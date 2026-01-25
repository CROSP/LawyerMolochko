<?php
/**
 * STDIO Transport Implementation
 *
 * Implements standard input/output transport for MCP server communication.
 * This is the primary transport for Claude Desktop integration.
 *
 * CRITICAL DESIGN PRINCIPLES:
 * - Messages (JSON-RPC) are read from STDIN and written to STDOUT
 * - Logs and debugging info ONLY go to STDERR (never STDOUT)
 * - Each message is line-delimited JSON (one message per line)
 * - Streams are flushed immediately after writes
 * - EOF on STDIN signals graceful shutdown
 * - Non-blocking reads to allow for timeouts and health checks
 *
 * @package ElementorMCP\Transport
 * @since 1.0.0
 */

namespace ElementorMCP\Transport;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class STDIO_Transport
 *
 * Handles communication via standard input/output streams.
 * Designed specifically for Claude Desktop MCP protocol.
 */
class STDIO_Transport implements Transport_Interface {
    /**
     * Configuration options
     *
     * @var array
     */
    private $config = [
        'read_timeout' => 0,        // Seconds to wait for input (0 = blocking)
        'write_buffer_size' => 8192, // Write buffer size
        'log_prefix' => '[MCP]',     // Prefix for log messages
        'log_timestamp' => true,     // Include timestamp in logs
        'log_level' => 'info',       // Minimum log level to output
    ];

    /**
     * Stream handles
     *
     * @var resource[]
     */
    private $streams = [
        'stdin' => null,
        'stdout' => null,
        'stderr' => null,
    ];

    /**
     * Connection state
     *
     * @var bool
     */
    private $connected = false;

    /**
     * Message buffer for incomplete reads
     *
     * @var string
     */
    private $read_buffer = '';

    /**
     * Log level priorities (lower = more important)
     *
     * @var array
     */
    private $log_levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
    ];

    /**
     * Constructor
     *
     * @param array $config Optional configuration overrides
     */
    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Initialize the STDIO transport
     *
     * Opens and configures STDIN, STDOUT, STDERR streams.
     *
     * @return void
     * @throws \Exception If streams cannot be opened or configured
     */
    public function initialize() {
        try {
            // Open standard streams
            $this->streams['stdin'] = defined('STDIN') ? STDIN : fopen('php://stdin', 'r');
            $this->streams['stdout'] = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');
            $this->streams['stderr'] = defined('STDERR') ? STDERR : fopen('php://stderr', 'w');

            // Verify streams are valid
            if (!$this->streams['stdin'] || !$this->streams['stdout'] || !$this->streams['stderr']) {
                throw new \Exception('Failed to open standard streams');
            }

            // Configure non-blocking mode for STDIN if timeout is set
            if ($this->config['read_timeout'] > 0) {
                stream_set_blocking($this->streams['stdin'], false);
            }

            // Disable output buffering for immediate writes
            stream_set_write_buffer($this->streams['stdout'], 0);
            stream_set_write_buffer($this->streams['stderr'], 0);

            $this->connected = true;
            $this->log('STDIO transport initialized', 'debug');

        } catch (\Exception $e) {
            $this->connected = false;
            throw new \Exception('Failed to initialize STDIO transport: ' . $e->getMessage());
        }
    }

    /**
     * Read an incoming message from STDIN
     *
     * Reads line-delimited JSON from STDIN. Blocks until a complete line
     * is available (unless configured with read_timeout).
     *
     * @return array|null Decoded message, or null on EOF
     * @throws \Exception If JSON parsing fails
     */
    public function read_message() {
        if (!$this->is_connected()) {
            return null;
        }

        try {
            // Read a line from STDIN
            $line = $this->read_line();

            // EOF reached
            if ($line === false || $line === null) {
                $this->log('EOF detected on STDIN', 'info');
                $this->connected = false;
                return null;
            }

            // Empty line - skip
            if (trim($line) === '') {
                return $this->read_message(); // Recursively read next line
            }

            // Parse JSON
            $message = json_decode($line, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON received: ' . json_last_error_msg());
            }

            $this->log('Received message: ' . substr($line, 0, 100) . '...', 'debug');

            return $message;

        } catch (\Exception $e) {
            $this->log('Error reading message: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Send an outgoing message to STDOUT
     *
     * Encodes message as JSON and writes to STDOUT with newline delimiter.
     * CRITICAL: This is the ONLY method that should write to STDOUT.
     *
     * @param array $message Message to send
     * @return bool True on success
     * @throws \Exception If encoding or writing fails
     */
    public function send_message($message) {
        if (!$this->is_connected()) {
            throw new \Exception('Cannot send message: transport not connected');
        }

        try {
            // Encode to JSON
            $json = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to encode message: ' . json_last_error_msg());
            }

            // Write to STDOUT with newline
            $bytes_written = fwrite($this->streams['stdout'], $json . "\n");

            if ($bytes_written === false) {
                throw new \Exception('Failed to write message to STDOUT');
            }

            // Flush immediately
            fflush($this->streams['stdout']);

            $this->log('Sent message: ' . substr($json, 0, 100) . '...', 'debug');

            return true;

        } catch (\Exception $e) {
            $this->log('Error sending message: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Log a message to STDERR
     *
     * CRITICAL: Logs MUST go to STDERR, never STDOUT.
     * STDOUT is reserved exclusively for JSON-RPC messages.
     *
     * @param string $message Log message
     * @param string $level Log level: 'debug', 'info', 'warning', 'error'
     * @return void
     */
    public function log($message, $level = 'info') {
        // Check if we should log this level
        if (!$this->should_log($level)) {
            return;
        }

        // Format log message
        $formatted = $this->format_log_message($message, $level);

        // Write to STDERR only
        if ($this->streams['stderr']) {
            fwrite($this->streams['stderr'], $formatted . "\n");
            fflush($this->streams['stderr']);
        }
    }

    /**
     * Check if transport is still connected
     *
     * @return bool True if connected
     */
    public function is_connected() {
        if (!$this->connected) {
            return false;
        }

        // Check if STDIN is still readable
        if ($this->streams['stdin'] && feof($this->streams['stdin'])) {
            $this->connected = false;
            return false;
        }

        return true;
    }

    /**
     * Shutdown the transport gracefully
     *
     * Close streams and clean up resources.
     *
     * @return void
     */
    public function shutdown() {
        $this->log('Shutting down STDIO transport', 'info');

        // Flush output streams
        if ($this->streams['stdout']) {
            fflush($this->streams['stdout']);
        }
        if ($this->streams['stderr']) {
            fflush($this->streams['stderr']);
        }

        // Note: We don't close STDIN/STDOUT/STDERR as they are managed by PHP
        // Only close if we opened them ourselves
        // This prevents issues with other code using these streams

        $this->connected = false;
    }

    /**
     * Get transport type
     *
     * @return string
     */
    public function get_type() {
        return 'stdio';
    }

    /**
     * Set configuration option
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public function set_config($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * Get configuration option
     *
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_config($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * Read a line from STDIN
     *
     * Handles both blocking and non-blocking reads based on configuration.
     *
     * @return string|false|null Line read, false on error, null on timeout
     */
    private function read_line() {
        $stdin = $this->streams['stdin'];

        if (!$stdin) {
            return false;
        }

        // Blocking read
        if ($this->config['read_timeout'] === 0) {
            $line = fgets($stdin);
            return $line === false ? null : rtrim($line, "\r\n");
        }

        // Non-blocking read with timeout
        $start_time = microtime(true);
        $timeout = $this->config['read_timeout'];
        $buffer = '';

        while (true) {
            // Check for timeout
            if (microtime(true) - $start_time > $timeout) {
                return null;
            }

            // Check if data is available
            $read = [$stdin];
            $write = null;
            $except = null;
            $changed = stream_select($read, $write, $except, 0, 100000); // 100ms

            if ($changed === false) {
                return false; // Error
            }

            if ($changed === 0) {
                continue; // No data yet
            }

            // Read available data
            $chunk = fgets($stdin);

            if ($chunk === false) {
                return feof($stdin) ? null : false;
            }

            $buffer .= $chunk;

            // Check if we have a complete line
            if (substr($chunk, -1) === "\n") {
                return rtrim($buffer, "\r\n");
            }
        }
    }

    /**
     * Format a log message
     *
     * @param string $message Message to format
     * @param string $level Log level
     * @return string Formatted message
     */
    private function format_log_message($message, $level) {
        $parts = [];

        // Add timestamp
        if ($this->config['log_timestamp']) {
            $parts[] = date('Y-m-d H:i:s');
        }

        // Add prefix
        if (!empty($this->config['log_prefix'])) {
            $parts[] = $this->config['log_prefix'];
        }

        // Add level
        $parts[] = '[' . strtoupper($level) . ']';

        // Add message
        $parts[] = $message;

        return implode(' ', $parts);
    }

    /**
     * Check if a log level should be output
     *
     * @param string $level Level to check
     * @return bool True if should log
     */
    private function should_log($level) {
        $min_level = $this->config['log_level'];

        if (!isset($this->log_levels[$level]) || !isset($this->log_levels[$min_level])) {
            return true; // Log unknown levels
        }

        return $this->log_levels[$level] >= $this->log_levels[$min_level];
    }
}
