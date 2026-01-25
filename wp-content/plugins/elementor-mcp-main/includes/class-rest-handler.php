<?php
/**
 * REST API Handler
 *
 * Handles HTTP requests via WordPress REST API and processes them through the MCP server.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class REST_Handler
 *
 * Processes MCP requests via WordPress REST API
 */
class REST_Handler {
    /**
     * Register REST routes
     *
     * @since 1.0.0
     */
    public static function register_routes() {
        register_rest_route('elementor-mcp/v1', '/rpc', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_request'],
            'permission_callback' => [__CLASS__, 'check_permissions'],
        ]);

        register_rest_route('elementor-mcp/v1', '/status', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_status'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle MCP request
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function handle_request($request) {
        // Set CORS headers for MCP Inspector
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: false');

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        try {
            // Set up admin user context for HTTP requests
            self::setup_user_context();

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

            // Load required classes
            if (!class_exists('\ElementorMCP\Logger')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/class-logger.php';
            }
            if (!class_exists('\ElementorMCP\Security')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/class-security.php';
            }
            if (!class_exists('\ElementorMCP\MCP_Server')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/class-mcp-server.php';
            }
            if (!class_exists('\ElementorMCP\Component_Factory')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/class-component-factory.php';
            }
            if (!class_exists('\ElementorMCP\Tools\Tool_Manager')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/abstract-class-base-tool.php';
                require_once ELEMENTOR_MCP_PATH . 'includes/tools/class-base-tool.php';
                require_once ELEMENTOR_MCP_PATH . 'includes/tools/class-schema-validator.php';
                require_once ELEMENTOR_MCP_PATH . 'includes/tools/class-tool-manager.php';
            }

            // Load Transport Interface
            if (!interface_exists('\ElementorMCP\Transport\Transport_Interface')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/transport/class-transport-interface.php';
            }

            // Define HTTP mode constant
            if (!\defined('ELEMENTOR_MCP_HTTP_MODE')) {
                \define('ELEMENTOR_MCP_HTTP_MODE', true);
            }

            // Use Component Factory for singleton instances (massive performance improvement)
            $logger = Component_Factory::get_logger();
            $security = Component_Factory::get_security();

            // Create a mock transport for HTTP (implements Transport_Interface)
            $transport = new class implements \ElementorMCP\Transport\Transport_Interface {
                private $config = [];

                public function get_type() {
                    return 'http';
                }

                public function initialize() {
                    // HTTP transport is always initialized
                }

                public function is_connected() {
                    // HTTP is always connected in context of a request
                    return true;
                }

                public function send_message($message) {
                    // HTTP transport doesn't send messages, returns are handled differently
                    return true;
                }

                public function receive_message() {
                    // HTTP receives via REST request
                    return null;
                }

                public function read_message() {
                    // HTTP reads from request body
                    return null;
                }

                public function shutdown() {
                    // Nothing to shutdown for HTTP
                }

                public function get_config($key, $default = null) {
                    return $this->config[$key] ?? $default;
                }

                public function set_config($key, $value) {
                    $this->config[$key] = $value;
                }

                public function log($msg, $level = 'info') {
                    // Optional logging
                }
            };

            $server = new MCP_Server($transport, $logger, $security);

            // Use cached tool manager from Component Factory (avoids expensive auto-discovery)
            $tool_manager = Component_Factory::get_tool_manager();

            $tools = $tool_manager->get_all_tools();
            foreach ($tools as $tool_name => $tool_instance) {
                $server->register_tool($tool_name, $tool_instance);
            }

            // Process the message
            $response = self::process_message($server, $message);

            return new \WP_REST_Response($response, 200);

        } catch (\Exception $e) {
            return new \WP_REST_Response([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error: ' . $e->getMessage(),
                ],
                'id' => isset($message['id']) ? $message['id'] : null,
            ], 500);
        }
    }

    /**
     * Process MCP message
     *
     * @param MCP_Server $server
     * @param array $message
     * @return array
     */
    private static function process_message($server, $message) {
        // For HTTP transport, automatically initialize the server
        // since it's stateless and each request is independent
        if (!$server->is_initialized() && isset($message['method']) && $message['method'] !== 'initialize') {
            // Use the new force_initialize method instead of reflection
            $server->force_initialize();
        }

        // Use reflection to call private handle_message method
        $reflection = new \ReflectionClass($server);
        $method = $reflection->getMethod('handle_message');
        $method->setAccessible(true);

        return $method->invoke($server, $message);
    }

    /**
     * Get server status
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function get_status($request) {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        return new \WP_REST_Response([
            'status' => 'active',
            'version' => ELEMENTOR_MCP_VERSION,
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'not_installed',
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'endpoint' => rest_url('elementor-mcp/v1/rpc'),
        ], 200);
    }

    /**
     * Check permissions
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public static function check_permissions($request) {
        // For now, allow all requests
        // TODO: Implement API key authentication
        return true;
    }

    /**
     * Set up user context for HTTP requests
     *
     * Automatically authenticates as an admin user for MCP requests
     *
     * @return void
     */
    private static function setup_user_context() {
        // Skip if user is already logged in
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        // Get first admin user
        $admin_users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        if (!empty($admin_users)) {
            $admin_user = $admin_users[0];
            wp_set_current_user($admin_user->ID);

            // Set up authentication cookie
            if (function_exists('wp_set_auth_cookie')) {
                wp_set_auth_cookie($admin_user->ID, false);
            }
        }
    }
}
