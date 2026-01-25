<?php
/**
 * MCP HTTP Server Entry Point
 *
 * This script initializes the MCP server with HTTP transport via WordPress REST API.
 * It integrates into WordPress as a plugin component.
 *
 * Usage:
 *   1. Ensure this plugin is activated in WordPress
 *   2. Access the endpoint at: http://your-site.com/wp-json/elementor-mcp/v1/rpc
 *   3. Send POST requests with JSON-RPC formatted messages
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

// This file is loaded by WordPress when the plugin is active
// The HTTP transport registers REST API endpoints automatically

// Load plugin if not already loaded
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_load = dirname(__DIR__, 3) . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        die('Error: WordPress not found. This script must be run from within WordPress.');
    }
}

// Initialize HTTP transport on WordPress init
add_action('init', function() {
    // Check if MCP server classes are loaded
    if (!class_exists('\ElementorMCP\Autoloader')) {
        require_once __DIR__ . '/includes/class-autoloader.php';
        \ElementorMCP\Autoloader::run();
    }

    // Load required classes
    require_once __DIR__ . '/includes/class-logger.php';
    require_once __DIR__ . '/includes/class-security.php';
    require_once __DIR__ . '/includes/transport/class-transport-interface.php';
    require_once __DIR__ . '/includes/transport/class-http-transport.php';
    require_once __DIR__ . '/includes/class-mcp-server.php';
    require_once __DIR__ . '/includes/tools/class-base-tool.php';
    require_once __DIR__ . '/includes/tools/class-schema-validator.php';
    require_once __DIR__ . '/includes/tools/class-tool-manager.php';

    // Initialize logger
    $logger = new \ElementorMCP\Logger([
        'min_level' => \ElementorMCP\Logger::LEVEL_INFO,
        'file_logging' => true,
        'wp_logging' => true,
        'log_file' => __DIR__ . '/logs/mcp-http.log',
    ]);

    // Initialize security
    $security = new \ElementorMCP\Security([
        'enforce_capabilities' => true,
        'rate_limit_window' => 60,
        'max_requests_per_window' => 100,
    ]);

    // Initialize HTTP transport
    $transport = new \ElementorMCP\Transport\HTTP_Transport([
        'cors_enabled' => true,
        'cors_origins' => ['*'], // Configure this for production!
        'auth_required' => false, // Set to true and configure API keys for production
        'log_requests' => true,
        'log_responses' => true,
    ]);

    // Initialize transport
    $transport->initialize();

    $logger->info('MCP HTTP Server initialized');

    // Note: The HTTP transport registers REST API endpoints automatically
    // The MCP server will be created and run on each REST API request
});

// Display info when accessed directly
if (php_sapi_name() === 'cli' || (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'http-server.php')) {
    echo "=== Elementor MCP HTTP Server ===\n\n";
    echo "The HTTP server is now active via WordPress REST API.\n\n";
    echo "Endpoint URL:\n";
    echo "  " . get_site_url() . "/wp-json/elementor-mcp/v1/rpc\n\n";
    echo "Test with cURL:\n";
    echo "  curl -X POST \\\n";
    echo "    -H 'Content-Type: application/json' \\\n";
    echo "    -d '{\"jsonrpc\":\"2.0\",\"id\":1,\"method\":\"initialize\",\"params\":{\"protocolVersion\":\"2024-11-05\",\"capabilities\":{},\"clientInfo\":{\"name\":\"test\",\"version\":\"1.0.0\"}}}' \\\n";
    echo "    " . get_site_url() . "/wp-json/elementor-mcp/v1/rpc\n\n";
}
