<?php
/**
 * Elementor MCP Plugin Class
 *
 * Main plugin handler class responsible for initializing Elementor MCP.
 * Registers and initializes all components required to run the plugin.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if ( ! defined( "ABSPATH" ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor MCP plugin.
 *
 * The main plugin handler class is responsible for initializing Elementor MCP.
 * The class registers all the components required to run the plugin.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/**
	 * MCP Server Manager.
	 *
	 * Holds the MCP server manager instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var object
	 */
	public $server_manager;

	/**
	 * Tools Manager.
	 *
	 * Holds the tools manager instance for MCP tools.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var object
	 */
	public $tools_manager;

	/**
	 * Clone.
	 *
	 * Disable class cloning and throw an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( "Cloning instances of the singleton \"%s\" class is forbidden.", get_class( $this ) ),
			"1.0.0"
		);
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf( "Unserializing instances of the singleton \"%s\" class is forbidden.", get_class( $this ) ),
			"1.0.0"
		);
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			/**
			 * Elementor MCP loaded.
			 *
			 * Fires when Elementor MCP was fully loaded and instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( "elementor_mcp/loaded" );
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * Register actions and initialize components.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		$this->init_components();

		/**
		 * Elementor MCP init.
		 *
		 * Fires when Elementor MCP components are initialized.
		 *
		 * @since 1.0.0
		 */
		do_action( "elementor_mcp/init" );
	}

	/**
	 * Initialize plugin components.
	 *
	 * Register and initialize all plugin components.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_components() {
		// Initialize HTTP transport for REST API access
		$this->init_http_transport();

		// Initialize integrations (Pexels, etc.)
		$this->init_integrations();

		// Initialize tools manager
		$this->init_tools_manager();

		// Initialize course shortcodes
		$this->init_course_shortcodes();

		// Initialize cache system (v1.1.0)
		$this->init_cache_system();
	}

	/**
	 * Initialize HTTP transport.
	 *
	 * Sets up REST API endpoint for MCP communication.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_http_transport() {
		// Load REST handler
		require_once ELEMENTOR_MCP_PATH . "includes/class-rest-handler.php";

		// Load SSE handler for OpenAI support
		require_once ELEMENTOR_MCP_PATH . "includes/class-sse-handler.php";

		// Register REST routes on rest_api_init
		add_action("rest_api_init", ["\ElementorMCP\REST_Handler", "register_routes"]);
		add_action("rest_api_init", ["\ElementorMCP\SSE_Handler", "register_routes"]);
	}

	/**
	 * Initialize tools manager.
	 *
	 * Sets up the tools manager for MCP tools.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_tools_manager() {
		// Load base tool class first
		if (!class_exists("\ElementorMCP\Tools\Base_Tool")) {
			require_once ELEMENTOR_MCP_PATH . "includes/tools/class-base-tool.php";
		}

		// Load schema validator
		if (!class_exists("\ElementorMCP\Tools\Schema_Validator")) {
			require_once ELEMENTOR_MCP_PATH . "includes/tools/class-schema-validator.php";
		}

		// Load tool manager
		if (!class_exists("\ElementorMCP\Tools\Tool_Manager")) {
			require_once ELEMENTOR_MCP_PATH . "includes/tools/class-tool-manager.php";
		}

		$this->tools_manager = new Tools\Tool_Manager();
	}

	/**
	 * Initialize course shortcodes.
	 *
	 * Sets up custom shortcodes for displaying course products.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_course_shortcodes() {
		// Load course shortcodes class
		require_once ELEMENTOR_MCP_PATH . "includes/class-course-shortcodes.php";

		// The class initializes itself
	}

	/**
	 * Initialize cache system.
	 *
	 * Sets up the caching system and integrates with Elementor hooks
	 * for automatic cache invalidation.
	 *
	 * @since 1.1.0
	 * @access private
	 */
	private function init_cache_system() {
		// Load cache classes if they exist
		$cache_dir = ELEMENTOR_MCP_PATH . 'includes/cache/';
		if (file_exists("{$cache_dir}class-page-cache.php")) {
			require_once "{$cache_dir}class-page-cache.php";
		}
		if (file_exists("{$cache_dir}class-element-cache.php")) {
			require_once "{$cache_dir}class-element-cache.php";
		}

		// Hook into Elementor save events for cache invalidation
		add_action('elementor/editor/after_save', [$this, 'invalidate_post_cache'], 10, 2);
		add_action('elementor/document/after_save', [$this, 'invalidate_post_cache'], 10, 2);

		// Hook into post deletion
		add_action('before_delete_post', [$this, 'invalidate_post_cache']);

		// Hook into Elementor cache clear
		add_action('elementor/core/files/clear_cache', [$this, 'clear_all_cache']);

		// Add cache warming on init
		add_action('elementor_mcp/init', [$this, 'maybe_warm_cache']);
	}

	/**
	 * Invalidate cache for a post.
	 *
	 * @since 1.1.0
	 * @param mixed $document_or_post_id The Document object or post ID.
	 * @param mixed $data Optional data from Elementor (unused but required for hook signature).
	 */
	public function invalidate_post_cache($document_or_post_id, $data = null) {
		// Extract post_id from Document object if needed
		$post_id = $document_or_post_id;
		if (is_object($document_or_post_id) && method_exists($document_or_post_id, 'get_main_id')) {
			$post_id = $document_or_post_id->get_main_id();
		}

		// Validate post_id is numeric
		if (!is_numeric($post_id)) {
			error_log('ElementorMCP: Invalid post_id in invalidate_post_cache - ' . gettype($document_or_post_id));
			return;
		}

		$post_id = absint($post_id);

		if (class_exists('\ElementorMCP\Cache\Page_Cache')) {
			\ElementorMCP\Cache\Page_Cache::invalidate($post_id);
		}

		if (class_exists('\ElementorMCP\Cache\Element_Cache')) {
			\ElementorMCP\Cache\Element_Cache::invalidate_post_elements($post_id);
		}

		// Also clear Component Factory cache if tools changed
		if (class_exists('\ElementorMCP\Component_Factory')) {
			\ElementorMCP\Component_Factory::clear_instance('tool_manager');
		}
	}

	/**
	 * Clear all cache.
	 *
	 * @since 1.1.0
	 */
	public function clear_all_cache() {
		if (class_exists('\ElementorMCP\Cache\Page_Cache')) {
			\ElementorMCP\Cache\Page_Cache::invalidate_all();
		}

		// Clear Component Factory instances
		if (class_exists('\ElementorMCP\Component_Factory')) {
			\ElementorMCP\Component_Factory::clear_instances();
		}
	}

	/**
	 * Maybe warm cache on init.
	 *
	 * @since 1.1.0
	 */
	public function maybe_warm_cache() {
		// Only warm cache if enabled in settings
		if (\get_option('elementor_mcp_warm_cache', false)) {
			// Warm up Component Factory instances
			if (class_exists('\ElementorMCP\Component_Factory')) {
				\ElementorMCP\Component_Factory::warm_up();
			}
		}
	}

	/**
	 * Register plugin hooks.
	 *
	 * Register actions and filters.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_hooks() {
		// Register WordPress hooks
		add_action( "init", [ $this, "init" ], 0 );

		// Register CLI commands if in WP-CLI context
		if ( defined( "WP_CLI" ) && WP_CLI ) {
			$this->register_cli_commands();
		}
	}

	/**
	 * Register WP-CLI commands.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_cli_commands() {
		// Will register MCP server command
		// Example: wp elementor-mcp server
	}

	/**
	 * Register autoloader.
	 *
	 * Elementor MCP autoloader loads all the classes needed to run the plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_autoloader() {
		require_once ELEMENTOR_MCP_PATH . "includes/class-autoloader.php";

		Autoloader::run();
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->register_autoloader();
		$this->register_hooks();
		$this->init_integrations();
	}

	/**
	 * Initialize integrations.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_integrations() {
		// Load Pexels client
		if (!class_exists("\ElementorMCP\Integrations\Pexels_Client")) {
			$pexels_client_file = ELEMENTOR_MCP_PATH . "includes/integrations/class-pexels-client.php";
			if (file_exists($pexels_client_file)) {
				require_once $pexels_client_file;
			}
		}

		// Initialize integrations on elementor/init
		add_action( "elementor/init", [ $this, "init_elementor" ] );
	}

	/**
	 * Initialize Elementor integrations.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init_elementor() {
		// This will be called when Elementor is initialized
	}

	/**
	 * Checks if MCP server is running.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return bool
	 */
	public function is_server_running() {
		// Will check if MCP server is currently running
		return false;
	}

	/**
	 * Get plugin version.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_version() {
		return ELEMENTOR_MCP_VERSION;
	}

	/**
	 * Get plugin name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return "elementor-mcp";
	}

	/**
	 * Get plugin title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string
	 */
	public function get_title() {
		return __( "Elementor MCP", "elementor-mcp" );
	}
}
