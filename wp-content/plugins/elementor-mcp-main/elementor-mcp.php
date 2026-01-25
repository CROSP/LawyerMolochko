<?php
/**
 * Elementor MCP - Main logic (loaded by elementor-mcp-main.php)
 *
 * Do NOT add "Plugin Name" here - the main plugin file is elementor-mcp-main.php
 * so WordPress can find it when the folder is named elementor-mcp-main.
 *
 * @package Elementor_MCP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'ELEMENTOR_MCP_VERSION', '1.0.0' );
define( 'ELEMENTOR_MCP__FILE__', __FILE__ );
define( 'ELEMENTOR_MCP_PLUGIN_BASE', plugin_basename( ELEMENTOR_MCP__FILE__ ) );
define( 'ELEMENTOR_MCP_PATH', plugin_dir_path( ELEMENTOR_MCP__FILE__ ) );
define( 'ELEMENTOR_MCP_URL', plugins_url( '/', ELEMENTOR_MCP__FILE__ ) );
define( 'ELEMENTOR_MCP_INCLUDES_PATH', ELEMENTOR_MCP_PATH . 'includes/' );

// Check if running in CLI mode for MCP server
if ( php_sapi_name() === 'cli' ) {
	// MCP server entry point
	if ( defined( 'ELEMENTOR_MCP_SERVER_MODE' ) && ELEMENTOR_MCP_SERVER_MODE ) {
		require_once ELEMENTOR_MCP_PATH . 'includes/server/server.php';
		exit;
	}
}

// Load Composer autoloader if available
if ( file_exists( ELEMENTOR_MCP_PATH . 'vendor/autoload.php' ) ) {
	require_once ELEMENTOR_MCP_PATH . 'vendor/autoload.php';
}

/**
 * Check minimum PHP version requirement.
 *
 * @since 1.0.0
 * @return void
 */
function elementor_mcp_fail_php_version() {
	$html_message = sprintf(
		'<div class="error"><h3>%1$s</h3><p>%2$s</p></div>',
		esc_html__( 'Elementor MCP isn\'t running because PHP is outdated.', 'elementor-mcp' ),
		sprintf(
			/* translators: %s: PHP version. */
			esc_html__( 'Update to version %s and get back to using Elementor MCP!', 'elementor-mcp' ),
			'7.4'
		)
	);

	echo wp_kses_post( $html_message );
}

/**
 * Check minimum WordPress version requirement.
 *
 * @since 1.0.0
 * @return void
 */
function elementor_mcp_fail_wp_version() {
	$html_message = sprintf(
		'<div class="error"><h3>%1$s</h3><p>%2$s</p></div>',
		esc_html__( 'Elementor MCP isn\'t running because WordPress is outdated.', 'elementor-mcp' ),
		sprintf(
			/* translators: %s: WordPress version. */
			esc_html__( 'Update to version %s and get back to using Elementor MCP!', 'elementor-mcp' ),
			'6.6'
		)
	);

	echo wp_kses_post( $html_message );
}

/**
 * Check if Elementor is installed and activated.
 *
 * @since 1.0.0
 * @return void
 */
function elementor_mcp_fail_elementor_inactive() {
	$html_message = sprintf(
		'<div class="error"><h3>%1$s</h3><p>%2$s</p></div>',
		esc_html__( 'Elementor MCP requires Elementor.', 'elementor-mcp' ),
		sprintf(
			/* translators: %s: Plugin name. */
			esc_html__( 'Please install and activate %s first.', 'elementor-mcp' ),
			'<strong>Elementor</strong>'
		)
	);

	echo wp_kses_post( $html_message );
}

/**
 * Initialize the plugin.
 *
 * Load the plugin only after Elementor (and other plugins) are loaded.
 * Checks for basic plugin requirements, if one check fails don't continue.
 *
 * @since 1.0.0
 * @return void
 */
function elementor_mcp_init() {
	// Check if Elementor is installed and activated
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'elementor_mcp_fail_elementor_inactive' );
		return;
	}

	// Once Elementor is loaded, load the plugin
	require_once ELEMENTOR_MCP_PATH . 'includes/class-plugin.php';

	// Initialize the plugin
	ElementorMCP\Plugin::instance();
}

// Check PHP version
if ( ! version_compare( PHP_VERSION, '7.4', '>=' ) ) {
	add_action( 'admin_notices', 'elementor_mcp_fail_php_version' );
	return;
}

// Check WordPress version
if ( ! version_compare( get_bloginfo( 'version' ), '6.6', '>=' ) ) {
	add_action( 'admin_notices', 'elementor_mcp_fail_wp_version' );
	return;
}

// Wait for Elementor to load, then initialize our plugin
add_action( 'plugins_loaded', 'elementor_mcp_init', 11 );
