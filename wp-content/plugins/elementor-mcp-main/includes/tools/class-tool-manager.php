<?php
/**
 * Tool Manager
 *
 * Manages registration and retrieval of MCP tools.
 * Similar to Elementor's widgets and controls managers.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Tool Manager Class
 *
 * Central registry for all MCP tools. Handles tool registration,
 * retrieval, and auto-discovery.
 *
 * @since 1.0.0
 */
class Tool_Manager {

	/**
	 * Registered tools.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $tools = array();

	/**
	 * Tool instances cache.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $tool_instances = array();

	/**
	 * Constructor.
	 *
	 * Initialize the tool manager.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the manager.
	 *
	 * Set up hooks and auto-discover tools.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init() {
		// Auto-discover tools on init
		add_action( 'init', array( $this, 'auto_discover_tools' ) );

		/**
		 * Tool manager initialized.
		 *
		 * Fires after the tool manager is initialized.
		 * Use this to register custom tools.
		 *
		 * @since 1.0.0
		 *
		 * @param Tool_Manager $this The tool manager instance.
		 */
		do_action( 'elementor_mcp/tools/manager_init', $this );
	}

	/**
	 * Register a tool.
	 *
	 * Add a tool instance to the registry.
	 * Similar to Elementor's register_widget_type().
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $tool_instance Tool instance to register (must extend a Base_Tool class).
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function register_tool( $tool_instance ) {
		$tool_name = $tool_instance->get_name();

		// Check if tool already registered
		if ( isset( $this->tools[ $tool_name ] ) ) {
			return new \WP_Error(
				'tool_already_registered',
				sprintf( 'Tool "%s" is already registered.', $tool_name )
			);
		}

		// Validate tool has required methods
		if ( ! method_exists( $tool_instance, 'execute' ) ) {
			return new \WP_Error(
				'invalid_tool',
				sprintf( 'Tool "%s" must implement execute() method.', $tool_name )
			);
		}

		// Register the tool
		$this->tools[ $tool_name ] = get_class( $tool_instance );
		$this->tool_instances[ $tool_name ] = $tool_instance;

		/**
		 * Tool registered.
		 *
		 * Fires after a tool is registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string    $tool_name      Tool name.
		 * @param Base_Tool $tool_instance  Tool instance.
		 */
		do_action( 'elementor_mcp/tools/tool_registered', $tool_name, $tool_instance );

		return true;
	}

	/**
	 * Unregister a tool.
	 *
	 * Remove a tool from the registry.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $tool_name Tool name to unregister.
	 * @return bool True on success, false if tool not found.
	 */
	public function unregister_tool( $tool_name ) {
		if ( ! isset( $this->tools[ $tool_name ] ) ) {
			return false;
		}

		unset( $this->tools[ $tool_name ] );
		unset( $this->tool_instances[ $tool_name ] );

		/**
		 * Tool unregistered.
		 *
		 * Fires after a tool is unregistered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tool_name Tool name.
		 */
		do_action( 'elementor_mcp/tools/tool_unregistered', $tool_name );

		return true;
	}

	/**
	 * Get a tool by name.
	 *
	 * Retrieve a registered tool instance.
	 * Creates instance if not already created.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $tool_name Tool name.
	 * @return Base_Tool|null Tool instance or null if not found.
	 */
	public function get_tool( $tool_name ) {
		if ( ! isset( $this->tools[ $tool_name ] ) ) {
			return null;
		}

		// Return cached instance if available
		if ( isset( $this->tool_instances[ $tool_name ] ) ) {
			return $this->tool_instances[ $tool_name ];
		}

		// Create new instance
		$tool_class = $this->tools[ $tool_name ];
		if ( ! class_exists( $tool_class ) ) {
			return null;
		}

		$tool_instance = new $tool_class();
		$this->tool_instances[ $tool_name ] = $tool_instance;

		return $tool_instance;
	}

	/**
	 * Get all registered tools.
	 *
	 * Retrieve array of all registered tool instances.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of tool instances keyed by tool name.
	 */
	public function get_all_tools() {
		$all_tools = array();

		foreach ( array_keys( $this->tools ) as $tool_name ) {
			$tool = $this->get_tool( $tool_name );
			if ( $tool ) {
				$all_tools[ $tool_name ] = $tool;
			}
		}

		return $all_tools;
	}

	/**
	 * Get tools list for MCP.
	 *
	 * Format all tools for MCP tools/list response.
	 * Returns array formatted according to MCP protocol.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Tools list formatted for MCP.
	 */
	public function get_tools_list() {
		$tools_list = array();
		$all_tools = $this->get_all_tools();

		foreach ( $all_tools as $tool ) {
			$tools_list[] = $tool->get_config();
		}

		/**
		 * Filter tools list.
		 *
		 * Allows modification of the tools list before sending to MCP client.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tools_list Array of tool configurations.
		 */
		return apply_filters( 'elementor_mcp/tools/list', $tools_list );
	}

	/**
	 * Check if a tool is registered.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $tool_name Tool name to check.
	 * @return bool True if registered, false otherwise.
	 */
	public function is_tool_registered( $tool_name ) {
		return isset( $this->tools[ $tool_name ] );
	}

	/**
	 * Auto-discover tools.
	 *
	 * Automatically discover and register tools from subdirectories.
	 * Scans the tools directory for tool class files.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function auto_discover_tools() {
		$tools_dir = dirname( __FILE__ );

		// Load traits first (they are dependencies for tools)
		$traits_dir = $tools_dir . '/traits';
		if ( is_dir( $traits_dir ) ) {
			$trait_files = glob( $traits_dir . '/trait-*.php' );
			foreach ( $trait_files as $trait_file ) {
				require_once $trait_file;
			}
		}

		$subdirs = array( 'elementor', 'widgets', 'documents', 'templates', 'controls', 'settings', 'pexels', 'rag' );

		foreach ( $subdirs as $subdir ) {
			$subdir_path = $tools_dir . '/' . $subdir;

			if ( ! is_dir( $subdir_path ) ) {
				continue;
			}

			$this->discover_tools_in_directory( $subdir_path, $subdir );
		}

		/**
		 * After auto-discover tools.
		 *
		 * Fires after automatic tool discovery completes.
		 * Use this to register additional tools or modify discovered tools.
		 *
		 * @since 1.0.0
		 *
		 * @param Tool_Manager $this The tool manager instance.
		 */
		do_action( 'elementor_mcp/tools/auto_discover_complete', $this );
	}

	/**
	 * Discover tools in a directory.
	 *
	 * Scan a directory for tool files and register them.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $directory Directory path to scan.
	 * @param string $subdir    Subdirectory name for namespace resolution.
	 */
	private function discover_tools_in_directory( $directory, $subdir = '' ) {
		if ( ! is_dir( $directory ) ) {
			return;
		}

		$files = glob( $directory . '/class-*.php' );

		foreach ( $files as $file ) {
			// Skip base classes and non-tool files
			$basename = basename( $file );
			if ( in_array( $basename, array( 'class-base-tool.php', 'class-tool-manager.php', 'class-schema-validator.php' ) ) ) {
				continue;
			}

			$this->load_and_register_tool( $file, $subdir );
		}
	}

	/**
	 * Load and register a tool from file.
	 *
	 * Require the tool file and attempt to register it.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $file_path Path to the tool file.
	 * @param string $subdir    Subdirectory name for namespace resolution.
	 */
	private function load_and_register_tool( $file_path, $subdir = '' ) {
		// Require the file
		require_once $file_path;

		// Extract class name from file name
		$file_name = basename( $file_path, '.php' );

		// Convert class-example-tool.php or class-example.php to Example_Tool or Example
		$class_name_parts = explode( '-', $file_name );
		array_shift( $class_name_parts ); // Remove 'class'

		$class_name = implode( '_', array_map( 'ucfirst', $class_name_parts ) );

		// Build full class name with sub-namespace if needed
		$base_namespace = __NAMESPACE__;
		if ( ! empty( $subdir ) ) {
			// Convert subdirectory name to proper case for namespace
			$namespace_part = ucfirst( $subdir );
			$full_class_name = $base_namespace . '\\' . $namespace_part . '\\' . $class_name;
		} else {
			$full_class_name = $base_namespace . '\\' . $class_name;
		}

		// Check if class exists
		if ( ! class_exists( $full_class_name ) ) {
			// Try without subdirectory namespace as fallback
			$full_class_name = $base_namespace . '\\' . $class_name;
			if ( ! class_exists( $full_class_name ) ) {
				error_log( "Tool class not found: {$full_class_name} in {$file_path}" );
				return;
			}
		}

		// Check if it extends Base_Tool (support both base tool classes)
		$valid_base_classes = array(
			$base_namespace . '\\Base_Tool',
			'ElementorMCP\\Base_Tool',
		);

		$extends_base_tool = false;
		foreach ( $valid_base_classes as $base_class ) {
			if ( is_subclass_of( $full_class_name, $base_class ) ) {
				$extends_base_tool = true;
				break;
			}
		}

		if ( ! $extends_base_tool ) {
			return;
		}

		// Create instance and register
		try {
			$tool_instance = new $full_class_name();
			$result = $this->register_tool( $tool_instance );
			if ( is_wp_error( $result ) ) {
				error_log( "Failed to register tool from {$file_path}: " . $result->get_error_message() );
			}
		} catch ( \Exception $e ) {
			// Log error but don't break execution
			error_log( "Failed to instantiate tool from {$file_path}: " . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() );
		}
	}

	/**
	 * Execute a tool.
	 *
	 * Execute a registered tool with the provided arguments.
	 * This is the main entry point for tool execution.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $tool_name Tool name to execute.
	 * @param array  $args      Arguments for the tool.
	 * @return array Execution result.
	 */
	public function execute_tool( $tool_name, $args = array() ) {
		$tool = $this->get_tool( $tool_name );

		if ( ! $tool ) {
			return array(
				'success' => false,
				'error'   => array(
					'code'    => 'tool_not_found',
					'message' => sprintf( 'Tool "%s" not found.', $tool_name ),
				),
			);
		}

		// Use the tool's run() method which includes validation and hooks
		return $tool->run( $args );
	}

	/**
	 * Get tool count.
	 *
	 * Get the number of registered tools.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int Number of registered tools.
	 */
	public function get_tool_count() {
		return count( $this->tools );
	}

	/**
	 * Get tool names.
	 *
	 * Get array of all registered tool names.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of tool names.
	 */
	public function get_tool_names() {
		return array_keys( $this->tools );
	}
}
