<?php
/**
 * Transport Interface
 *
 * Defines the contract for all transport layer implementations.
 * Transports handle communication between the MCP server and clients
 * (e.g., Claude Desktop via STDIO, HTTP clients, WebSocket connections).
 *
 * @package ElementorMCP\Transport
 * @since 1.0.0
 */

namespace ElementorMCP\Transport;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface Transport_Interface
 *
 * All transport implementations must implement this interface to ensure
 * consistent behavior across different communication methods.
 */
interface Transport_Interface {
    /**
     * Read an incoming message from the transport layer
     *
     * This method should block until a complete message is available or
     * return null if the connection is closed/EOF reached.
     *
     * For STDIO: Read line-delimited JSON from STDIN
     * For HTTP: Parse incoming POST request body
     * For WebSocket: Read WebSocket frame
     *
     * @return array|null Decoded message as associative array, or null on EOF/disconnect
     * @throws \Exception If message parsing fails or transport error occurs
     */
    public function read_message();

    /**
     * Send an outgoing message through the transport layer
     *
     * The message should be encoded appropriately for the transport type
     * and sent immediately (flushed).
     *
     * For STDIO: Write line-delimited JSON to STDOUT
     * For HTTP: Send HTTP response with JSON body
     * For WebSocket: Send WebSocket frame
     *
     * @param array $message Message to send (will be JSON-encoded)
     * @return bool True on success, false on failure
     * @throws \Exception If encoding or sending fails
     */
    public function send_message($message);

    /**
     * Log a debugging or diagnostic message
     *
     * CRITICAL: Logs must NEVER be written to the main message channel.
     * For STDIO: Write to STDERR only (never STDOUT)
     * For HTTP: Write to error log or custom log file
     * For WebSocket: Send as separate log message type or to error log
     *
     * @param string $message Log message
     * @param string $level Log level: 'debug', 'info', 'warning', 'error'
     * @return void
     */
    public function log($message, $level = 'info');

    /**
     * Check if the transport connection is still active
     *
     * This allows the server to detect disconnections and shut down gracefully.
     *
     * For STDIO: Check if STDIN is still readable
     * For HTTP: Always true (stateless)
     * For WebSocket: Check connection state
     *
     * @return bool True if connected, false if disconnected
     */
    public function is_connected();

    /**
     * Initialize the transport layer
     *
     * Perform any necessary setup (e.g., configure streams, bind sockets).
     * This is called once when the MCP server starts.
     *
     * @return void
     * @throws \Exception If initialization fails
     */
    public function initialize();

    /**
     * Shutdown the transport layer gracefully
     *
     * Clean up resources, close connections, flush buffers.
     * This is called when the MCP server is shutting down.
     *
     * @return void
     */
    public function shutdown();

    /**
     * Get transport type identifier
     *
     * @return string Transport type: 'stdio', 'http', 'websocket', etc.
     */
    public function get_type();

    /**
     * Set a configuration option
     *
     * Allow runtime configuration of the transport layer.
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public function set_config($key, $value);

    /**
     * Get a configuration option
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function get_config($key, $default = null);
}
