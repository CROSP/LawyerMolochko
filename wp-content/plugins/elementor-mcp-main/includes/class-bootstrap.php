<?php
/**
 * WordPress Bootstrap Helper
 *
 * Handles WordPress initialization in CLI context for the MCP server.
 * Provides WordPress path detection, minimal overhead loading, and plugin verification.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

/**
 * Class Bootstrap
 *
 * Bootstraps WordPress with minimal overhead for CLI/MCP server usage.
 */
class Bootstrap {
    /**
     * WordPress root path
     *
     * @var string
     */
    private $wp_root;

    /**
     * Required plugins
     *
     * @var array
     */
    private $required_plugins = [];

    /**
     * Whether WordPress is loaded
     *
     * @var bool
     */
    private $is_loaded = false;

    /**
     * Constructor
     *
     * @param array $config Bootstrap configuration
     */
    public function __construct($config = []) {
        $this->wp_root = $config['wp_root'] ?? $this->find_wp_root();
        $this->required_plugins = $config['required_plugins'] ?? [];
    }

    /**
     * Initialize WordPress
     *
     * @return void
     * @throws \Exception If WordPress cannot be loaded
     */
    public function init() {
        if ($this->is_loaded) {
            return;
        }

        // Validate WordPress root
        if (!$this->validate_wp_root($this->wp_root)) {
            throw new \Exception("Invalid WordPress root path: {$this->wp_root}");
        }

        // Set up WordPress environment
        $this->setup_environment();

        // Load WordPress
        $this->load_wordpress();

        // Verify required plugins
        $this->verify_plugins();

        // Set up CLI authentication
        $this->setup_cli_auth();

        $this->is_loaded = true;
    }

    /**
     * Find WordPress root directory
     *
     * Searches common locations for wp-load.php
     *
     * @return string|null WordPress root path or null if not found
     */
    private function find_wp_root() {
        $plugin_dir = dirname(__DIR__);

        // Common WordPress installation patterns
        $search_paths = [
            // Standard WordPress plugin directory
            dirname($plugin_dir, 2), // wp-content/plugins/elementor-mcp -> wp-content -> root

            // wp-content at same level as plugin
            dirname($plugin_dir), // wp-content/elementor-mcp -> wp-content -> need to go up

            // Custom plugins directory
            dirname($plugin_dir, 3), // custom/plugins/elementor-mcp

            // Direct parent
            dirname($plugin_dir),

            // Environment variable
            getenv('WORDPRESS_ROOT'),

            // Common Linux paths
            '/var/www/html',
            '/usr/share/wordpress',

            // Common Windows paths (XAMPP, WAMP, Local)
            'C:/xampp/htdocs',
            'C:/wamp/www',
            'C:/wamp64/www',
            'C:/Users/' . getenv('USERNAME') . '/Local Sites',
        ];

        foreach ($search_paths as $path) {
            if (!$path) {
                continue;
            }

            // Normalize path
            $path = str_replace('\\', '/', $path);

            // Check if this looks like WordPress root
            if ($this->validate_wp_root($path)) {
                return $path;
            }

            // Also check if we need to go up one more level from wp-content
            if (basename($path) === 'wp-content') {
                $parent = dirname($path);
                if ($this->validate_wp_root($parent)) {
                    return $parent;
                }
            }
        }

        return null;
    }

    /**
     * Validate WordPress root directory
     *
     * @param string $path Path to validate
     * @return bool True if valid WordPress root
     */
    private function validate_wp_root($path) {
        if (!$path || !is_dir($path)) {
            return false;
        }

        // Check for essential WordPress files
        $required_files = [
            'wp-load.php',
            'wp-config.php',
            'wp-includes/version.php',
        ];

        foreach ($required_files as $file) {
            if (!file_exists($path . '/' . $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set up WordPress environment
     *
     * Configure environment variables and constants before loading WordPress
     *
     * @return void
     */
    private function setup_environment() {
        // Prevent WordPress from redirecting
        if (!defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', false);
        }

        // Set script filename for proper WordPress initialization
        if (!isset($_SERVER['SCRIPT_FILENAME'])) {
            $_SERVER['SCRIPT_FILENAME'] = $this->wp_root . '/index.php';
        }

        // Set up request URI
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        // Set HTTP host for URL generation
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }

        // Set server protocol
        if (!isset($_SERVER['SERVER_PROTOCOL'])) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        }

        // Set request method
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }

        // Disable cron in CLI context
        if (!defined('DISABLE_WP_CRON')) {
            define('DISABLE_WP_CRON', true);
        }

        // Set admin context for capabilities
        if (!defined('WP_ADMIN')) {
            define('WP_ADMIN', true);
        }

        // Disable plugin/theme editor
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }

    /**
     * Load WordPress core
     *
     * @return void
     * @throws \Exception If WordPress fails to load
     */
    private function load_wordpress() {
        $wp_load_path = $this->wp_root . '/wp-load.php';

        if (!file_exists($wp_load_path)) {
            throw new \Exception("wp-load.php not found at: {$wp_load_path}");
        }

        // Capture output during WordPress load
        ob_start();

        try {
            // Load WordPress
            require_once $wp_load_path;

            // Discard any output from WordPress loading
            ob_end_clean();

        } catch (\Exception $e) {
            ob_end_clean();
            throw new \Exception("Failed to load WordPress: " . $e->getMessage());
        }

        // Verify WordPress constants are defined
        if (!defined('ABSPATH')) {
            throw new \Exception("WordPress did not load correctly (ABSPATH not defined)");
        }

        // Trigger WordPress loaded action
        if (!did_action('wp_loaded')) {
            do_action('wp_loaded');
        }
    }

    /**
     * Verify required plugins are loaded
     *
     * @return void
     * @throws \Exception If required plugins are not active
     */
    private function verify_plugins() {
        if (empty($this->required_plugins)) {
            return;
        }

        // Check if plugins are active
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $missing_plugins = [];

        foreach ($this->required_plugins as $plugin) {
            if (!is_plugin_active($plugin)) {
                $missing_plugins[] = $plugin;
            }
        }

        if (!empty($missing_plugins)) {
            throw new \Exception(
                "Required plugins are not active: " . implode(', ', $missing_plugins)
            );
        }
    }

    /**
     * Set up CLI authentication context
     *
     * Creates a temporary admin user context for CLI operations
     *
     * @return void
     */
    private function setup_cli_auth() {
        // In CLI context, we need to set up a user
        // Try to get first admin user
        $admin_users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        if (!empty($admin_users)) {
            $admin_user = $admin_users[0];
            wp_set_current_user($admin_user->ID);

            if (function_exists('wp_set_auth_cookie')) {
                wp_set_auth_cookie($admin_user->ID);
            }

            // Log which user we're running as
            if (defined('STDERR')) {
                fwrite(STDERR, "[MCP] Running as user: {$admin_user->user_login} (ID: {$admin_user->ID})\n");
            }
        } else {
            // No admin user found - this is unusual but we'll continue
            // Some operations might fail without a user context
            if (defined('STDERR')) {
                fwrite(STDERR, "[MCP] Warning: No administrator user found, some operations may fail\n");
            }
        }
    }

    /**
     * Get WordPress root path
     *
     * @return string WordPress root path
     */
    public function get_wp_root() {
        return $this->wp_root;
    }

    /**
     * Check if WordPress is loaded
     *
     * @return bool True if loaded
     */
    public function is_loaded() {
        return $this->is_loaded;
    }

    /**
     * Get WordPress version
     *
     * @return string|null WordPress version or null if not loaded
     */
    public function get_wp_version() {
        if (!$this->is_loaded || !function_exists('get_bloginfo')) {
            return null;
        }

        return get_bloginfo('version');
    }

    /**
     * Get site URL
     *
     * @return string|null Site URL or null if not loaded
     */
    public function get_site_url() {
        if (!$this->is_loaded || !function_exists('get_site_url')) {
            return null;
        }

        return get_site_url();
    }

    /**
     * Get current user
     *
     * @return \WP_User|null Current user or null
     */
    public function get_current_user() {
        if (!$this->is_loaded || !function_exists('wp_get_current_user')) {
            return null;
        }

        $user = wp_get_current_user();
        return $user->exists() ? $user : null;
    }

    /**
     * Get bootstrap information
     *
     * @return array Bootstrap information
     */
    public function get_info() {
        return [
            'wp_root' => $this->wp_root,
            'is_loaded' => $this->is_loaded,
            'wp_version' => $this->get_wp_version(),
            'site_url' => $this->get_site_url(),
            'current_user' => $this->get_current_user() ? $this->get_current_user()->user_login : null,
            'required_plugins' => $this->required_plugins,
            'php_version' => PHP_VERSION,
            'php_sapi' => php_sapi_name(),
        ];
    }
}
