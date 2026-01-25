<?php
/**
 * HTTP Transport Implementation
 *
 * Implements HTTP-based transport for MCP server communication.
 * This is an optional transport for web-based clients and REST API access.
 *
 * DESIGN PRINCIPLES:
 * - Accept JSON-RPC messages via HTTP POST requests
 * - Send JSON-RPC responses via HTTP responses
 * - Support CORS for browser-based clients
 * - Support authentication via headers (API keys, JWT tokens)
 * - Stateless operation (each request is independent)
 * - Proper HTTP status codes and error handling
 *
 * @package ElementorMCP\Transport
 * @since 1.0.0
 */

namespace ElementorMCP\Transport;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HTTP_Transport
 *
 * Handles communication via HTTP requests/responses.
 * Designed for REST API and web client integration.
 */
class HTTP_Transport implements Transport_Interface {
    /**
     * Configuration options
     *
     * @var array
     */
    private $config = [
        'endpoint' => '/wp-json/elementor-mcp/v1/rpc',
        'cors_enabled' => true,
        'cors_origins' => ['*'],
        'auth_required' => true,
        'auth_header' => 'X-MCP-API-Key',
        'rate_limit' => 100,           // Requests per minute
        'rate_limit_window' => 60,     // Seconds
        'log_requests' => true,
        'log_responses' => true,
    ];

    /**
     * Current request data
     *
     * @var array|null
     */
    private $current_request = null;

    /**
     * Connection state (always true for stateless HTTP)
     *
     * @var bool
     */
    private $connected = true;

    /**
     * Response sent flag
     *
     * @var bool
     */
    private $response_sent = false;

    /**
     * Rate limiting data
     *
     * @var array
     */
    private $rate_limits = [];

    /**
     * Constructor
     *
     * @param array $config Optional configuration overrides
     */
    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Initialize the HTTP transport
     *
     * Register WordPress REST API endpoints and hooks.
     *
     * @return void
     * @throws \Exception If initialization fails
     */
    public function initialize() {
        // Register REST API endpoint
        add_action('rest_api_init', [$this, 'register_rest_route']);

        // Add CORS headers if enabled
        if ($this->config['cors_enabled']) {
            add_action('rest_api_init', [$this, 'add_cors_headers']);
        }

        $this->log('HTTP transport initialized', 'debug');
    }

    /**
     * Register REST API route
     *
     * @return void
     */
    public function register_rest_route() {
        register_rest_route('elementor-mcp/v1', '/rpc', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_rest_request'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }

    /**
     * Add CORS headers
     *
     * @return void
     */
    public function add_cors_headers() {
        $allowed_origins = $this->config['cors_origins'];

        // Get request origin
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // Check if origin is allowed
        if (in_array('*', $allowed_origins) || in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, ' . $this->config['auth_header']);
            header('Access-Control-Max-Age: 86400');
        }

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit;
        }
    }

    /**
     * Handle REST API request
     *
     * @param \WP_REST_Request $request WordPress REST request
     * @return \WP_REST_Response REST response
     */
    public function handle_rest_request($request) {
        try {
            // Check rate limiting
            if (!$this->check_rate_limit()) {
                return new \WP_REST_Response([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32000,
                        'message' => 'Rate limit exceeded',
                    ],
                    'id' => null,
                ], 429);
            }

            // Get request body
            $body = $request->get_body();
            $message = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new \WP_REST_Response([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32700,
                        'message' => 'Parse error: ' . json_last_error_msg(),
                    ],
                    'id' => null,
                ], 400);
            }

            // Store current request for read_message()
            $this->current_request = $message;

            if ($this->config['log_requests']) {
                $this->log('HTTP request received: ' . substr($body, 0, 100) . '...', 'debug');
            }

            // Return success response (actual MCP response will be sent via send_message())
            return new \WP_REST_Response([
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            $this->log('Error handling HTTP request: ' . $e->getMessage(), 'error');

            return new \WP_REST_Response([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error: ' . $e->getMessage(),
                ],
                'id' => null,
            ], 500);
        }
    }

    /**
     * Check permissions for REST API access
     *
     * @return bool|\WP_Error True if allowed, WP_Error otherwise
     */
    public function check_permissions() {
        // If authentication not required, allow all
        if (!$this->config['auth_required']) {
            return true;
        }

        // Check for API key in header
        $api_key = isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($this->config['auth_header']))])
            ? $_SERVER['HTTP_' . str_replace('-', '_', strtoupper($this->config['auth_header']))]
            : null;

        if (!$api_key) {
            return new \WP_Error(
                'unauthorized',
                'API key required',
                ['status' => 401]
            );
        }

        // Validate API key (implement your validation logic here)
        $valid = $this->validate_api_key($api_key);

        if (!$valid) {
            return new \WP_Error(
                'forbidden',
                'Invalid API key',
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * Validate API key
     *
     * @param string $api_key API key to validate
     * @return bool True if valid
     */
    private function validate_api_key($api_key) {
        // TODO: Implement proper API key validation
        // For now, check against stored keys in WordPress options
        $valid_keys = get_option('elementor_mcp_api_keys', []);
        return in_array($api_key, $valid_keys);
    }

    /**
     * Check rate limiting
     *
     * @return bool True if request allowed
     */
    private function check_rate_limit() {
        $client_id = $this->get_client_identifier();
        $now = time();
        $window = $this->config['rate_limit_window'];
        $limit = $this->config['rate_limit'];

        // Initialize rate limit data for client
        if (!isset($this->rate_limits[$client_id])) {
            $this->rate_limits[$client_id] = [
                'count' => 0,
                'reset_time' => $now + $window,
            ];
        }

        $client_data = &$this->rate_limits[$client_id];

        // Reset if window expired
        if ($now >= $client_data['reset_time']) {
            $client_data['count'] = 0;
            $client_data['reset_time'] = $now + $window;
        }

        // Check limit
        if ($client_data['count'] >= $limit) {
            return false;
        }

        // Increment counter
        $client_data['count']++;

        return true;
    }

    /**
     * Get client identifier for rate limiting
     *
     * @return string Client identifier
     */
    private function get_client_identifier() {
        // Use API key if available
        $api_key = isset($_SERVER['HTTP_' . str_replace('-', '_', strtoupper($this->config['auth_header']))])
            ? $_SERVER['HTTP_' . str_replace('-', '_', strtoupper($this->config['auth_header']))]
            : null;

        if ($api_key) {
            return 'key_' . md5($api_key);
        }

        // Fall back to IP address
        return 'ip_' . $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Read an incoming message from HTTP request
     *
     * For HTTP, this returns the current request that was stored
     * by handle_rest_request(). Since HTTP is stateless, this is
     * called only once per request.
     *
     * @return array|null Decoded message
     */
    public function read_message() {
        $message = $this->current_request;
        $this->current_request = null; // Consume the message
        return $message;
    }

    /**
     * Send an outgoing message via HTTP response
     *
     * For HTTP transport, this outputs the response directly.
     *
     * @param array $message Message to send
     * @return bool True on success
     * @throws \Exception If encoding fails
     */
    public function send_message($message) {
        if ($this->response_sent) {
            $this->log('Warning: Attempting to send multiple responses', 'warning');
            return false;
        }

        try {
            // Encode to JSON
            $json = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to encode message: ' . json_last_error_msg());
            }

            if ($this->config['log_responses']) {
                $this->log('HTTP response sent: ' . substr($json, 0, 100) . '...', 'debug');
            }

            // Set headers and output
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }

            echo $json;

            $this->response_sent = true;

            return true;

        } catch (\Exception $e) {
            $this->log('Error sending HTTP response: ' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Log a message
     *
     * For HTTP, logs go to WordPress error log or custom log file.
     *
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    public function log($message, $level = 'info') {
        $formatted = sprintf(
            '[%s] [HTTP Transport] [%s] %s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );

        // Use WordPress error logging
        if (function_exists('error_log')) {
            error_log($formatted);
        }
    }

    /**
     * Check if transport is connected
     *
     * For HTTP, always returns true (stateless)
     *
     * @return bool
     */
    public function is_connected() {
        return $this->connected;
    }

    /**
     * Shutdown the transport
     *
     * For HTTP, nothing to clean up (stateless)
     *
     * @return void
     */
    public function shutdown() {
        $this->log('HTTP transport shutting down', 'debug');
        $this->connected = false;
    }

    /**
     * Get transport type
     *
     * @return string
     */
    public function get_type() {
        return 'http';
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
}
