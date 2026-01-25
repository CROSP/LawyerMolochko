<?php
/**
 * SSE (Server-Sent Events) Handler for OpenAI MCP Support
 *
 * Handles SSE requests for OpenAI's MCP implementation.
 * OpenAI requires streaming transport (SSE or Streamable HTTP).
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SSE_Handler
 *
 * Processes MCP requests via Server-Sent Events for OpenAI compatibility
 */
class SSE_Handler {
    /**
     * Register SSE routes
     *
     * @since 1.0.0
     */
    public static function register_routes() {
        // SSE endpoint for OpenAI MCP
        register_rest_route('elementor-mcp/v1', '/sse', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_sse_request'],
            'permission_callback' => '__return_true',
        ]);

        // Streamable HTTP endpoint (alternative to SSE)
        register_rest_route('elementor-mcp/v1', '/stream', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_stream_request'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle SSE request for OpenAI
     *
     * @param \WP_REST_Request $request
     * @return void
     */
    public static function handle_sse_request($request) {
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable Nginx buffering
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Set up admin user context
        self::setup_user_context();

        // Get request body
        $body = $request->get_body();
        $messages = [];

        // Parse the request body as line-delimited JSON (JSONL)
        if ($body) {
            $lines = explode("\n", trim($body));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line) {
                    $message = json_decode($line, true);
                    if ($message) {
                        $messages[] = $message;
                    }
                }
            }
        }

        // Process each message and send SSE responses
        foreach ($messages as $message) {
            $response = self::process_mcp_message($message);
            if ($response) {
                self::send_sse_message($response);
            }
        }

        // Send done event
        echo "event: done\n";
        echo "data: {\"type\":\"done\"}\n\n";
        flush();

        exit;
    }

    /**
     * Handle Streamable HTTP request
     *
     * @param \WP_REST_Request $request
     * @return void
     */
    public static function handle_stream_request($request) {
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set streaming headers
        header('Content-Type: application/x-ndjson');
        header('Cache-Control: no-cache');
        header('Transfer-Encoding: chunked');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Set up admin user context
        self::setup_user_context();

        // Get request body
        $body = $request->get_body();

        // Parse as JSONL (newline-delimited JSON)
        $lines = explode("\n", trim($body));

        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            $message = json_decode($line, true);
            if (!$message) continue;

            $response = self::process_mcp_message($message);
            if ($response) {
                // Send as NDJSON (newline-delimited JSON)
                echo json_encode($response) . "\n";
                flush();
            }
        }

        exit;
    }

    /**
     * Process MCP message
     *
     * @param array $message
     * @return array|null
     */
    private static function process_mcp_message($message) {
        try {
            // Load required classes
            if (!class_exists('\ElementorMCP\Component_Factory')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/class-component-factory.php';
            }

            // Create SSE transport
            $transport = new class implements \ElementorMCP\Transport\Transport_Interface {
                private $config = [];

                public function get_type() {
                    return 'sse';
                }

                public function initialize() {}
                public function is_connected() {
                    return true;
                }
                public function send_message($message) {
                    return true;
                }
                public function receive_message() {
                    return null;
                }
                public function read_message() {
                    return null;
                }
                public function shutdown() {}
                public function get_config($key, $default = null) {
                    return $this->config[$key] ?? $default;
                }
                public function set_config($key, $value) {
                    $this->config[$key] = $value;
                }
                public function log($msg, $level = 'info') {}
            };

            // Get singleton instances
            $logger = Component_Factory::get_logger();
            $security = Component_Factory::get_security();
            $server = Component_Factory::get_mcp_server($transport, $logger, $security);

            // Process the message
            $method = new \ReflectionMethod($server, 'handle_message');
            $method->setAccessible(true);
            return $method->invoke($server, $message);

        } catch (\Exception $e) {
            return [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage(),
                ],
                'id' => $message['id'] ?? null,
            ];
        }
    }

    /**
     * Send SSE message
     *
     * @param array $data
     * @return void
     */
    private static function send_sse_message($data) {
        $json = json_encode($data);
        echo "data: {$json}\n\n";
        flush();
    }

    /**
     * Set up user context
     *
     * @return void
     */
    private static function setup_user_context() {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        $admin_users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        if (!empty($admin_users)) {
            $admin_user = $admin_users[0];
            wp_set_current_user($admin_user->ID);
        }
    }
}