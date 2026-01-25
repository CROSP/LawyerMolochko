<?php
/**
 * Elementor MCP Autoloader
 *
 * Autoloader handler class responsible for loading the different
 * classes needed to run the plugin.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor MCP autoloader.
 *
 * Elementor MCP autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 *
 * @since 1.0.0
 */
class Autoloader {

	/**
	 * Classes map.
	 *
	 * Maps Elementor MCP classes to file names.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var array Classes used by elementor MCP.
	 */
	private static $classes_map;

	/**
	 * Default path for autoloader.
	 *
	 * @var string
	 */
	private static $default_path;

	/**
	 * Default namespace for autoloader.
	 *
	 * @var string
	 */
	private static $default_namespace;

	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @param string $default_path
	 * @param string $default_namespace
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function run( $default_path = '', $default_namespace = '' ) {
		if ( '' === $default_path ) {
			$default_path = ELEMENTOR_MCP_PATH;
		}

		if ( '' === $default_namespace ) {
			$default_namespace = __NAMESPACE__;
		}

		self::$default_path = $default_path;
		self::$default_namespace = $default_namespace;

		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	/**
	 * Get classes map.
	 *
	 * Retrieve the classes map.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array Classes map.
	 */
	public static function get_classes_map() {
		if ( ! self::$classes_map ) {
			self::init_classes_map();
		}

		return self::$classes_map;
	}

	/**
	 * Initialize classes map.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 */
	private static function init_classes_map() {
		self::$classes_map = [
			// Core classes
			'Plugin' => 'includes/class-plugin.php',
			'Autoloader' => 'includes/class-autoloader.php',
			'Base_Tool' => 'includes/abstract-class-base-tool.php',

			// Server classes (to be implemented by other agents)
			// 'Server\Server' => 'includes/server/class-server.php',
			// 'Server\Transport\Stdio' => 'includes/server/transport/class-stdio.php',
			// 'Server\Transport\SSE' => 'includes/server/transport/class-sse.php',

			// Manager classes (to be implemented by other agents)
			// 'Managers\Tools_Manager' => 'includes/managers/class-tools-manager.php',
			// 'Managers\Schema_Manager' => 'includes/managers/class-schema-manager.php',

			// Tool classes (to be implemented by other agents)
			// 'Tools\Base_Tool' => 'includes/tools/class-base-tool.php',
			// 'Tools\Get_Widgets' => 'includes/tools/class-get-widgets.php',
		];
	}

	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @param string $relative_class_name Class name.
	 */
	private static function load_class( $relative_class_name ) {
		$classes_map = self::get_classes_map();

		if ( isset( $classes_map[ $relative_class_name ] ) ) {
			$filename = self::$default_path . $classes_map[ $relative_class_name ];
		} else {
			// Convert class name to file path
			// Example: Server\Transport\Stdio => server/transport/class-stdio.php
			$filename = strtolower(
				preg_replace(
					[ '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
					[ '$1-$2', '-', DIRECTORY_SEPARATOR ],
					$relative_class_name
				)
			);

			$filename = self::$default_path . 'includes/' . $filename . '.php';
		}

		if ( is_readable( $filename ) ) {
			require $filename;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exists and load it.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @param string $class_name Class name.
	 */
	private static function autoload( $class_name ) {
		// Only autoload classes from our namespace
		if ( 0 !== strpos( $class_name, self::$default_namespace . '\\' ) ) {
			return;
		}

		$relative_class_name = preg_replace( '/^' . self::$default_namespace . '\\\/', '', $class_name );

		$final_class_name = self::$default_namespace . '\\' . $relative_class_name;

		if ( ! class_exists( $final_class_name, false ) ) {
			self::load_class( $relative_class_name );
		}
	}
}
