<?php
/**
 * Logger Class
 *
 * Provides flexible logging functionality for the MCP server with support for:
 * - Multiple log levels (debug, info, warning, error)
 * - File-based logging
 * - STDERR logging for CLI operations
 * - WordPress integration (error_log when available)
 * - Log rotation and size management
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Logger
 *
 * Handles all logging operations for the MCP server.
 */
class Logger {
    /**
     * Log levels
     */
    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_WARNING = 3;
    const LEVEL_ERROR = 4;

    /**
     * Log level names
     */
    const LEVEL_NAMES = [
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
    ];

    /**
     * Current minimum log level
     *
     * @var int
     */
    private $min_level = self::LEVEL_INFO;

    /**
     * Log file path
     *
     * @var string|null
     */
    private $log_file = null;

    /**
     * Whether to log to file
     *
     * @var bool
     */
    private $file_logging_enabled = false;

    /**
     * Whether to log to STDERR (for CLI)
     *
     * @var bool
     */
    private $stderr_logging_enabled = false;

    /**
     * Whether to use WordPress error_log
     *
     * @var bool
     */
    private $wp_logging_enabled = false;

    /**
     * Maximum log file size in bytes (default 10MB)
     *
     * @var int
     */
    private $max_file_size = 10485760;

    /**
     * Number of rotated log files to keep
     *
     * @var int
     */
    private $max_files = 5;

    /**
     * Log entry format
     *
     * @var string
     */
    private $log_format = '[{timestamp}] {level}: {message}';

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct($config = []) {
        // Determine logging mode based on environment
        $is_cli = defined('STDIN') && php_sapi_name() === 'cli';
        $is_wordpress = defined('ABSPATH') && function_exists('wp_upload_dir');

        // Default configuration
        $defaults = [
            'min_level' => $is_cli ? self::LEVEL_DEBUG : self::LEVEL_INFO,
            'file_logging' => true,
            'stderr_logging' => $is_cli,
            'wp_logging' => $is_wordpress && !$is_cli,
            'log_file' => null,
            'max_file_size' => 10485760, // 10MB
            'max_files' => 5,
        ];

        $config = array_merge($defaults, $config);

        $this->min_level = $config['min_level'];
        $this->file_logging_enabled = $config['file_logging'];
        $this->stderr_logging_enabled = $config['stderr_logging'];
        $this->wp_logging_enabled = $config['wp_logging'];
        $this->max_file_size = $config['max_file_size'];
        $this->max_files = $config['max_files'];

        // Set up log file path
        if ($this->file_logging_enabled) {
            $this->log_file = $config['log_file'] ?? $this->get_default_log_file();
            $this->ensure_log_directory();
        }
    }

    /**
     * Log a debug message
     *
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Main logging method
     *
     * @param int $level Log level
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public function log($level, $message, $context = []) {
        // Check if message should be logged
        if ($level < $this->min_level) {
            return;
        }

        // Format the log entry
        $log_entry = $this->format_log_entry($level, $message, $context);

        // Log to different destinations
        if ($this->file_logging_enabled && $this->log_file) {
            $this->log_to_file($log_entry);
        }

        if ($this->stderr_logging_enabled && defined('STDERR')) {
            $this->log_to_stderr($log_entry);
        }

        if ($this->wp_logging_enabled) {
            $this->log_to_wordpress($level, $message, $context);
        }
    }

    /**
     * Format a log entry
     *
     * @param int $level Log level
     * @param string $message Message
     * @param array $context Context data
     * @return string Formatted log entry
     */
    private function format_log_entry($level, $message, $context = []) {
        $level_name = self::LEVEL_NAMES[$level] ?? 'UNKNOWN';
        $timestamp = gmdate('Y-m-d H:i:s');

        // Replace placeholders in message with context values
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                $placeholder = '{' . $key . '}';
                if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                    $message = str_replace($placeholder, (string) $value, $message);
                }
            }
        }

        // Format the log entry
        $entry = str_replace(
            ['{timestamp}', '{level}', '{message}'],
            [$timestamp, $level_name, $message],
            $this->log_format
        );

        // Add context as JSON if present
        if (!empty($context) && strpos($message, json_encode($context)) === false) {
            $entry .= ' | Context: ' . wp_json_encode($context);
        }

        return $entry;
    }

    /**
     * Log to file
     *
     * @param string $message Formatted log entry
     * @return void
     */
    private function log_to_file($message) {
        if (!$this->log_file) {
            return;
        }

        try {
            // Check if rotation is needed
            if (file_exists($this->log_file) && filesize($this->log_file) >= $this->max_file_size) {
                $this->rotate_log_file();
            }

            // Append to log file
            $result = file_put_contents(
                $this->log_file,
                $message . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );

            if ($result === false) {
                // Fallback to STDERR if file write fails
                if (defined('STDERR')) {
                    fwrite(STDERR, "[LOGGER ERROR] Failed to write to log file: {$this->log_file}" . PHP_EOL);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't let logging break the application
            if (defined('STDERR')) {
                fwrite(STDERR, "[LOGGER ERROR] " . $e->getMessage() . PHP_EOL);
            }
        }
    }

    /**
     * Log to STDERR
     *
     * @param string $message Formatted log entry
     * @return void
     */
    private function log_to_stderr($message) {
        if (!defined('STDERR')) {
            return;
        }

        fwrite(STDERR, $message . PHP_EOL);
    }

    /**
     * Log to WordPress error log
     *
     * @param int $level Log level
     * @param string $message Message
     * @param array $context Context data
     * @return void
     */
    private function log_to_wordpress($level, $message, $context = []) {
        if (!function_exists('error_log')) {
            return;
        }

        $level_name = self::LEVEL_NAMES[$level] ?? 'UNKNOWN';
        $log_message = "[Elementor MCP] [{$level_name}] {$message}";

        if (!empty($context)) {
            $log_message .= ' | ' . wp_json_encode($context);
        }

        error_log($log_message);
    }

    /**
     * Rotate log file when it exceeds max size
     *
     * @return void
     */
    private function rotate_log_file() {
        if (!$this->log_file || !file_exists($this->log_file)) {
            return;
        }

        // Delete oldest file if we're at max
        $oldest_file = $this->log_file . '.' . $this->max_files;
        if (file_exists($oldest_file)) {
            unlink($oldest_file);
        }

        // Rotate existing files
        for ($i = $this->max_files - 1; $i >= 1; $i--) {
            $old_file = $this->log_file . '.' . $i;
            $new_file = $this->log_file . '.' . ($i + 1);

            if (file_exists($old_file)) {
                rename($old_file, $new_file);
            }
        }

        // Rename current file to .1
        rename($this->log_file, $this->log_file . '.1');
    }

    /**
     * Get default log file path
     *
     * @return string Log file path
     */
    private function get_default_log_file() {
        if (defined('WP_CONTENT_DIR')) {
            // WordPress environment - use uploads directory
            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/elementor-mcp-logs';
        } else {
            // Non-WordPress environment - use plugin directory
            $log_dir = dirname(__DIR__) . '/logs';
        }

        return $log_dir . '/mcp-server.log';
    }

    /**
     * Ensure log directory exists and is writable
     *
     * @return void
     */
    private function ensure_log_directory() {
        if (!$this->log_file) {
            return;
        }

        $log_dir = dirname($this->log_file);

        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        // Create .htaccess to protect log files
        $htaccess = $log_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "deny from all\n");
        }

        // Create index.php to prevent directory listing
        $index = $log_dir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, "<?php\n// Silence is golden.\n");
        }
    }

    /**
     * Set minimum log level
     *
     * @param int $level Log level constant
     * @return void
     */
    public function set_min_level($level) {
        $this->min_level = $level;
    }

    /**
     * Get minimum log level
     *
     * @return int
     */
    public function get_min_level() {
        return $this->min_level;
    }

    /**
     * Enable/disable file logging
     *
     * @param bool $enabled Whether to enable file logging
     * @return void
     */
    public function set_file_logging($enabled) {
        $this->file_logging_enabled = (bool) $enabled;

        if ($enabled && !$this->log_file) {
            $this->log_file = $this->get_default_log_file();
            $this->ensure_log_directory();
        }
    }

    /**
     * Enable/disable STDERR logging
     *
     * @param bool $enabled Whether to enable STDERR logging
     * @return void
     */
    public function set_stderr_logging($enabled) {
        $this->stderr_logging_enabled = (bool) $enabled;
    }

    /**
     * Enable/disable WordPress logging
     *
     * @param bool $enabled Whether to enable WordPress logging
     * @return void
     */
    public function set_wp_logging($enabled) {
        $this->wp_logging_enabled = (bool) $enabled;
    }

    /**
     * Set custom log file path
     *
     * @param string $path Log file path
     * @return void
     */
    public function set_log_file($path) {
        $this->log_file = $path;
        $this->ensure_log_directory();
    }

    /**
     * Get current log file path
     *
     * @return string|null
     */
    public function get_log_file() {
        return $this->log_file;
    }

    /**
     * Clear log file
     *
     * @return bool Success status
     */
    public function clear_log() {
        if (!$this->log_file || !file_exists($this->log_file)) {
            return false;
        }

        return (bool) file_put_contents($this->log_file, '');
    }

    /**
     * Get log file contents
     *
     * @param int $lines Number of lines to read from end (0 for all)
     * @return string Log contents
     */
    public function get_log_contents($lines = 0) {
        if (!$this->log_file || !file_exists($this->log_file)) {
            return '';
        }

        if ($lines <= 0) {
            return file_get_contents($this->log_file);
        }

        // Read last N lines
        $file = new \SplFileObject($this->log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key() + 1;
        $start_line = max(0, $total_lines - $lines);

        $result = [];
        $file->seek($start_line);
        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }

        return implode('', $result);
    }

    /**
     * Get log statistics
     *
     * @return array Log statistics
     */
    public function get_stats() {
        $stats = [
            'log_file' => $this->log_file,
            'file_logging_enabled' => $this->file_logging_enabled,
            'stderr_logging_enabled' => $this->stderr_logging_enabled,
            'wp_logging_enabled' => $this->wp_logging_enabled,
            'min_level' => self::LEVEL_NAMES[$this->min_level] ?? 'UNKNOWN',
        ];

        if ($this->log_file && file_exists($this->log_file)) {
            $stats['file_size'] = filesize($this->log_file);
            $stats['file_size_human'] = size_format($stats['file_size']);
            $stats['max_file_size'] = $this->max_file_size;
            $stats['max_file_size_human'] = size_format($this->max_file_size);
            $stats['file_writable'] = is_writable($this->log_file);
        }

        return $stats;
    }
}
