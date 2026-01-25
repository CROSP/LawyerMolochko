<?php
/**
 * Elementor MCP Server Configuration
 *
 * Copy this file to config.php and customize the settings for your environment.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

// ============================================================================
// WORDPRESS CONFIGURATION
// ============================================================================

/**
 * WordPress Root Directory
 *
 * Absolute path to your WordPress installation root directory.
 * This is the directory containing wp-load.php, wp-config.php, etc.
 *
 * If not defined, the bootstrap will attempt to auto-detect the path.
 * Auto-detection checks common locations but may not work in all setups.
 *
 * Examples:
 * - Linux/Mac: '/var/www/html' or '/Users/username/Sites/mysite'
 * - Windows: 'C:/xampp/htdocs' or 'C:/wamp64/www'
 * - Relative: dirname(__DIR__, 3) for standard plugin location
 */
// DDEV: /var/www/html | Local: adjust as needed
define('ELEMENTOR_MCP_WP_ROOT', '/var/www/html');

/**
 * Auto-detect WordPress root (recommended for standard installations)
 *
 * For standard WordPress plugin installation (wp-content/plugins/elementor-mcp),
 * you can leave this commented out and let auto-detection work.
 *
 * Uncomment and set if:
 * - You have a non-standard WordPress installation
 * - Auto-detection fails
 * - You want to specify the exact path for performance
 */
// define('ELEMENTOR_MCP_WP_ROOT', dirname(__DIR__, 3));

// ============================================================================
// LOGGING CONFIGURATION
// ============================================================================

/**
 * Log Level
 *
 * Minimum log level to output. Options:
 * - 'debug'   : All messages including debug information
 * - 'info'    : Informational messages and above (recommended)
 * - 'warning' : Warnings and errors only
 * - 'error'   : Errors only
 *
 * Default: 'info'
 */
define('ELEMENTOR_MCP_LOG_LEVEL', 'error');

/**
 * Log File Path
 *
 * Absolute path to the log file.
 * Logs are written here in addition to STDERR.
 *
 * The directory will be created automatically if it doesn't exist.
 * Make sure PHP has write permissions to this location.
 *
 * Default: {plugin-dir}/logs/mcp-server.log
 */
define('ELEMENTOR_MCP_LOG_FILE', __DIR__ . '/logs/mcp-server.log');

/**
 * Maximum Log File Size (bytes)
 *
 * When the log file exceeds this size, it will be rotated.
 * Default: 10485760 (10MB)
 */
// define('ELEMENTOR_MCP_MAX_LOG_SIZE', 10485760);

/**
 * Number of Log Files to Keep
 *
 * When log rotation occurs, this many old log files will be kept.
 * Default: 5
 */
// define('ELEMENTOR_MCP_MAX_LOG_FILES', 5);

// ============================================================================
// SECURITY CONFIGURATION
// ============================================================================

/**
 * Rate Limiting
 *
 * Maximum number of requests allowed per time window.
 * Helps prevent abuse and excessive resource usage.
 *
 * Default: 1000 requests per 60 seconds (CLI context)
 */
// define('ELEMENTOR_MCP_RATE_LIMIT', 1000);
// define('ELEMENTOR_MCP_RATE_LIMIT_WINDOW', 60);

/**
 * Allowed Tools
 *
 * Whitelist of tools that can be executed.
 * Leave empty to allow all tools (default).
 *
 * Example:
 * define('ELEMENTOR_MCP_ALLOWED_TOOLS', [
 *     'get_elementor_info',
 *     'list_widgets',
 *     'get_widget_schema',
 * ]);
 */
// define('ELEMENTOR_MCP_ALLOWED_TOOLS', []);

/**
 * Blocked Tools
 *
 * Blacklist of tools that should never be executed.
 * Useful for disabling specific dangerous operations.
 *
 * Example:
 * define('ELEMENTOR_MCP_BLOCKED_TOOLS', [
 *     'delete_elementor_data',
 *     'clear_elementor_cache',
 * ]);
 */
// define('ELEMENTOR_MCP_BLOCKED_TOOLS', []);

// ============================================================================
// PERFORMANCE CONFIGURATION
// ============================================================================

/**
 * Memory Limit
 *
 * PHP memory limit for the MCP server process.
 * Increase if you're working with large Elementor templates or data.
 *
 * Default: WordPress default (usually 256M or higher)
 */
// ini_set('memory_limit', '512M');

/**
 * Maximum Execution Time
 *
 * Maximum time (in seconds) a script is allowed to run.
 * Set to 0 for unlimited (useful for long-running MCP server).
 *
 * Default: 0 (unlimited)
 */
// set_time_limit(0);

// ============================================================================
// DEVELOPMENT CONFIGURATION
// ============================================================================

/**
 * Debug Mode
 *
 * Enable additional debugging output.
 * DO NOT enable in production!
 *
 * Default: false
 */
// define('ELEMENTOR_MCP_DEBUG', false);

/**
 * Development Mode
 *
 * Enables additional checks and verbose logging.
 * May impact performance.
 *
 * Default: false
 */
// define('ELEMENTOR_MCP_DEV_MODE', false);

// ============================================================================
// CLAUDE DESKTOP CONFIGURATION
// ============================================================================

/**
 * This configuration file only affects the PHP/WordPress side.
 * To configure Claude Desktop to use this MCP server, you need to edit
 * your Claude Desktop configuration file:
 *
 * Location:
 * - macOS: ~/Library/Application Support/Claude/claude_desktop_config.json
 * - Windows: %APPDATA%\Claude\claude_desktop_config.json
 * - Linux: ~/.config/Claude/claude_desktop_config.json
 *
 * Add this server configuration:
 * {
 *   "mcpServers": {
 *     "elementor": {
 *       "command": "php",
 *       "args": [
 *         "/absolute/path/to/elementor-mcp/mcp-server.php"
 *       ]
 *     }
 *   }
 * }
 *
 * Replace /absolute/path/to/elementor-mcp/ with the actual path to this plugin.
 *
 * After editing, restart Claude Desktop.
 */

// ============================================================================
// ENVIRONMENT-SPECIFIC OVERRIDES
// ============================================================================

/**
 * You can use environment variables to override these settings.
 * This is useful for different environments (dev, staging, production).
 *
 * Example:
 * if (getenv('ELEMENTOR_MCP_ENV') === 'production') {
 *     define('ELEMENTOR_MCP_LOG_LEVEL', 'warning');
 *     define('ELEMENTOR_MCP_DEBUG', false);
 * }
 */

// Load environment-specific configuration if it exists
$env_config = __DIR__ . '/config.' . (getenv('ELEMENTOR_MCP_ENV') ?: 'local') . '.php';
if (file_exists($env_config)) {
    require_once $env_config;
}
