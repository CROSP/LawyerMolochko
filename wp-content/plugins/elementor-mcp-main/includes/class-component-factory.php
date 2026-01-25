<?php
/**
 * Component Factory for Singleton Pattern Implementation
 *
 * Creates and manages single instances of core components to reduce
 * overhead and improve performance, especially in HTTP mode.
 *
 * @package ElementorMCP
 * @since 1.1.0
 */

namespace ElementorMCP;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Component Factory class.
 *
 * Provides singleton instances of core components to avoid
 * recreating them on every request, significantly reducing
 * initialization overhead.
 *
 * @since 1.1.0
 */
class Component_Factory {

    /**
     * Component instances.
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Get logger instance.
     *
     * Returns a singleton logger instance configured with
     * the current settings.
     *
     * @since 1.1.0
     * @return Logger
     */
    public static function get_logger() {
        if (!isset(self::$instances['logger'])) {
            self::$instances['logger'] = new Logger([
                'log_file' => ELEMENTOR_MCP_PATH . 'logs/mcp.log',
                'log_level' => get_option('elementor_mcp_log_level', 'info'),
                'log_to_stderr' => false,
                'log_to_wordpress' => true
            ]);
        }
        return self::$instances['logger'];
    }

    /**
     * Get security instance.
     *
     * Returns a singleton security instance configured with
     * the current settings.
     *
     * @since 1.1.0
     * @return Security
     */
    public static function get_security() {
        if (!isset(self::$instances['security'])) {
            self::$instances['security'] = new Security([
                'enable_capability_check' => true,
                'enable_rate_limiting' => true,
                'rate_limit_max' => get_option('elementor_mcp_rate_limit_max', 100),
                'rate_limit_window' => get_option('elementor_mcp_rate_limit_window', 60),
                'enable_nonce_check' => false,
                'tool_whitelist' => get_option('elementor_mcp_tool_whitelist', []),
                'tool_blacklist' => get_option('elementor_mcp_tool_blacklist', [])
            ]);
        }
        return self::$instances['security'];
    }

    /**
     * Get tool manager instance.
     *
     * Returns a singleton tool manager instance with tools
     * cached for performance.
     *
     * @since 1.1.0
     * @return Tools\Tool_Manager
     */
    public static function get_tool_manager() {
        if (!isset(self::$instances['tool_manager'])) {
            // Check cache first
            $cache_key = 'elementor_mcp_tool_registry_v1';
            $cached_tools = wp_cache_get($cache_key, 'elementor_mcp');

            if ($cached_tools === false) {
                // No cache, create and discover tools
                $tool_manager = new Tools\Tool_Manager();
                $tool_manager->auto_discover_tools();

                // Cache for 1 hour
                wp_cache_set($cache_key, $tool_manager, 'elementor_mcp', HOUR_IN_SECONDS);
                self::$instances['tool_manager'] = $tool_manager;
            } else {
                self::$instances['tool_manager'] = $cached_tools;
            }
        }

        return self::$instances['tool_manager'];
    }

    /**
     * Get MCP server instance.
     *
     * Returns a singleton MCP server instance properly configured
     * and initialized.
     *
     * @since 1.1.0
     * @return MCP_Server
     */
    public static function get_mcp_server() {
        if (!isset(self::$instances['mcp_server'])) {
            $logger = self::get_logger();
            $security = self::get_security();

            $server = new MCP_Server($logger, $security);

            // Force initialize for HTTP mode
            if (defined('ELEMENTOR_MCP_HTTP_MODE') && ELEMENTOR_MCP_HTTP_MODE) {
                $server->force_initialize();
            }

            self::$instances['mcp_server'] = $server;
        }

        return self::$instances['mcp_server'];
    }

    /**
     * Clear all instances.
     *
     * Clears all cached singleton instances. Useful for testing
     * or when settings have changed.
     *
     * @since 1.1.0
     */
    public static function clear_instances() {
        self::$instances = [];

        // Also clear WordPress cache
        wp_cache_delete('elementor_mcp_tool_registry_v1', 'elementor_mcp');
    }

    /**
     * Clear specific instance.
     *
     * Clears a specific cached singleton instance.
     *
     * @since 1.1.0
     * @param string $component Component name to clear.
     */
    public static function clear_instance($component) {
        if (isset(self::$instances[$component])) {
            unset(self::$instances[$component]);
        }

        // Clear related cache if needed
        if ($component === 'tool_manager') {
            wp_cache_delete('elementor_mcp_tool_registry_v1', 'elementor_mcp');
        }
    }

    /**
     * Warm up instances.
     *
     * Pre-creates all singleton instances to avoid lazy loading
     * delays during request processing.
     *
     * @since 1.1.0
     */
    public static function warm_up() {
        self::get_logger();
        self::get_security();
        self::get_tool_manager();
        // Don't warm up MCP server as it needs specific context
    }
}