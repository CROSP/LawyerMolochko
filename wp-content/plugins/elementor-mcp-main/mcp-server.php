#!/usr/bin/env php
<?php
/**
 * MCP Server CLI Entry Point
 *
 * This is the main entry point for running the Elementor MCP server from command line.
 * It bootstraps WordPress, initializes all components, and starts the MCP server.
 *
 * Usage:
 *   php mcp-server.php
 *
 * Or make executable and run directly:
 *   chmod +x mcp-server.php
 *   ./mcp-server.php
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

// Ensure we're running in CLI mode
if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Error: This script must be run from the command line.\n");
    exit(1);
}

// Ensure we have required PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    fwrite(STDERR, "Error: PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n");
    exit(1);
}

// Ensure required extensions are loaded
$required_extensions = ['json'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        fwrite(STDERR, "Error: Required PHP extension '{$ext}' is not loaded.\n");
        exit(1);
    }
}

// Load configuration
$config_file = __DIR__ . '/config.php';
if (!file_exists($config_file)) {
    // Try to use example config
    $example_config = __DIR__ . '/config.example.php';
    if (file_exists($example_config)) {
        fwrite(STDERR, "Warning: config.php not found, using config.example.php\n");
        fwrite(STDERR, "Please copy config.example.php to config.php and customize it.\n\n");
        $config_file = $example_config;
    } else {
        fwrite(STDERR, "Error: Configuration file not found.\n");
        fwrite(STDERR, "Please create config.php based on config.example.php\n");
        exit(1);
    }
}

require_once $config_file;

// Set default configuration if not defined
if (!defined('ELEMENTOR_MCP_WP_ROOT')) {
    // Try to auto-detect WordPress root
    $possible_paths = [
        dirname(__DIR__, 3), // wp-content/plugins/elementor-mcp/
        dirname(__DIR__, 2), // custom-plugins/elementor-mcp/
        dirname(__DIR__),     // parent directory
        '/var/www/html',      // Common Linux path
        'C:/xampp/htdocs',    // Common Windows XAMPP path
    ];

    $wp_root = null;
    foreach ($possible_paths as $path) {
        if (file_exists($path . '/wp-load.php')) {
            $wp_root = $path;
            break;
        }
    }

    if (!$wp_root) {
        fwrite(STDERR, "Error: Could not auto-detect WordPress root directory.\n");
        fwrite(STDERR, "Please define ELEMENTOR_MCP_WP_ROOT in config.php\n");
        exit(1);
    }

    define('ELEMENTOR_MCP_WP_ROOT', $wp_root);
    fwrite(STDERR, "Info: Auto-detected WordPress root: {$wp_root}\n");
}

if (!defined('ELEMENTOR_MCP_LOG_LEVEL')) {
    define('ELEMENTOR_MCP_LOG_LEVEL', 'info');
}

if (!defined('ELEMENTOR_MCP_LOG_FILE')) {
    define('ELEMENTOR_MCP_LOG_FILE', __DIR__ . '/logs/mcp-server.log');
}

// Disable output buffering for real-time logging
if (ob_get_level()) {
    ob_end_clean();
}

// Load Bootstrap helper
require_once __DIR__ . '/includes/class-bootstrap.php';

// Create bootstrap instance
$bootstrap = new \ElementorMCP\Bootstrap([
    'wp_root' => ELEMENTOR_MCP_WP_ROOT,
    'required_plugins' => ['elementor/elementor.php'],
]);

// Initialize WordPress
try {
    fwrite(STDERR, "[MCP] Bootstrapping WordPress...\n");
    $bootstrap->init();
    fwrite(STDERR, "[MCP] WordPress loaded successfully\n");
} catch (Exception $e) {
    fwrite(STDERR, "Error: Failed to bootstrap WordPress: {$e->getMessage()}\n");
    exit(1);
}

// Verify Elementor is loaded
if (!defined('ELEMENTOR_VERSION')) {
    fwrite(STDERR, "Error: Elementor plugin is not active or not found.\n");
    fwrite(STDERR, "Please ensure Elementor is installed and activated.\n");
    exit(1);
}

fwrite(STDERR, "[MCP] Elementor version " . ELEMENTOR_VERSION . " loaded\n");

// Load MCP Server components
try {
    // Ensure plugin path is defined (plugin may not have defined it in CLI context)
    if (!defined('ELEMENTOR_MCP_PATH')) {
        define('ELEMENTOR_MCP_PATH', __DIR__ . '/');
    }

    // Initialize autoloader (if not already loaded by WordPress plugin)
    if (!class_exists('\ElementorMCP\Autoloader')) {
        require_once __DIR__ . '/includes/class-autoloader.php';
        \ElementorMCP\Autoloader::run();
    }

    // Manually load core classes (needed before autoloader is fully active)
    require_once __DIR__ . '/includes/class-logger.php';
    require_once __DIR__ . '/includes/class-security.php';
    require_once __DIR__ . '/includes/transport/class-transport-interface.php';
    require_once __DIR__ . '/includes/transport/class-stdio-transport.php';
    require_once __DIR__ . '/includes/class-mcp-server.php';
    require_once __DIR__ . '/includes/tools/class-base-tool.php';
    require_once __DIR__ . '/includes/tools/class-schema-validator.php';
    require_once __DIR__ . '/includes/tools/class-tool-manager.php';

    // Initialize logger
    $log_level_map = [
        'debug' => \ElementorMCP\Logger::LEVEL_DEBUG,
        'info' => \ElementorMCP\Logger::LEVEL_INFO,
        'warning' => \ElementorMCP\Logger::LEVEL_WARNING,
        'error' => \ElementorMCP\Logger::LEVEL_ERROR,
    ];

    $min_log_level = $log_level_map[ELEMENTOR_MCP_LOG_LEVEL] ?? \ElementorMCP\Logger::LEVEL_INFO;

    $logger = new \ElementorMCP\Logger([
        'min_level' => $min_log_level,
        'file_logging' => true,
        'stderr_logging' => true,
        'wp_logging' => false,
        'log_file' => ELEMENTOR_MCP_LOG_FILE,
    ]);

    $logger->info('MCP Server starting...');
    $logger->info('PHP Version: ' . PHP_VERSION);
    $logger->info('WordPress Version: ' . get_bloginfo('version'));
    $logger->info('Elementor Version: ' . ELEMENTOR_VERSION);

    // Initialize security
    $security = new \ElementorMCP\Security([
        'enforce_capabilities' => false, // Disabled for CLI context
        'rate_limit_window' => 60,
        'max_requests_per_window' => 1000, // Higher limit for CLI
    ]);

    // Initialize STDIO transport
    $transport = new \ElementorMCP\Transport\STDIO_Transport([
        'log_level' => ELEMENTOR_MCP_LOG_LEVEL,
        'log_prefix' => '[MCP]',
    ]);

    // Initialize MCP server
    $server = new \ElementorMCP\MCP_Server($transport, $logger, $security);

    $logger->info('MCP Server components initialized');

    // Initialize tool manager and register tools
    $tool_manager = new \ElementorMCP\Tools\Tool_Manager();

    // Wait for WordPress to fully load before discovering tools
    // This ensures all Elementor classes are available
    if (!did_action('init')) {
        // Trigger init action manually in CLI context
        do_action('init');
    }

    // Auto-discover and register all tools
    $tool_manager->auto_discover_tools();

    $tools = $tool_manager->get_all_tools();
    $logger->info('Discovered ' . count($tools) . ' tools');
    fwrite(STDERR, '[MCP] Discovered ' . count($tools) . " tools\n");

    // Register tools with the MCP server
    foreach ($tools as $tool_name => $tool_instance) {
        $server->register_tool($tool_name, $tool_instance);
        $logger->debug("Registered tool: {$tool_name}");
    }

    $logger->info('All tools registered with MCP server');

    // Set up signal handlers for graceful shutdown
    if (function_exists('pcntl_signal')) {
        pcntl_signal(SIGTERM, function () use ($server, $logger) {
            $logger->info('Received SIGTERM, shutting down...');
            $server->stop();
        });

        pcntl_signal(SIGINT, function () use ($server, $logger) {
            $logger->info('Received SIGINT, shutting down...');
            $server->stop();
        });
    }

    // Start the server
    fwrite(STDERR, "[MCP] Server ready, waiting for connections...\n");
    $logger->info('Starting MCP server run loop');

    $server->run();

    // Server stopped
    $logger->info('MCP Server stopped');
    fwrite(STDERR, "[MCP] Server stopped\n");

} catch (Exception $e) {
    fwrite(STDERR, "Fatal Error: {$e->getMessage()}\n");
    fwrite(STDERR, "Stack trace:\n{$e->getTraceAsString()}\n");

    if (isset($logger)) {
        $logger->error('Fatal error: ' . $e->getMessage());
        $logger->error('Stack trace: ' . $e->getTraceAsString());
    }

    exit(1);
}

// Clean exit
exit(0);
