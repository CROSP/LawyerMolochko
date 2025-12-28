<?php
/**
 * Modified WordPress settings file for DDEV.
 * Custom domain configuration added.
 *
 * @package ddevapp
 */

if ( getenv( 'IS_DDEV_PROJECT' ) == 'true' ) {
	/** The name of the database for WordPress */
	defined( 'DB_NAME' ) || define( 'DB_NAME', 'db' );

	/** MySQL database username */
	defined( 'DB_USER' ) || define( 'DB_USER', 'db' );

	/** MySQL database password */
	defined( 'DB_PASSWORD' ) || define( 'DB_PASSWORD', 'db' );

	/** MySQL hostname */
	defined( 'DB_HOST' ) || define( 'DB_HOST', 'ddev-lawyermolochko-db' );

	/** WP_HOME URL - Dynamic based on HTTP_HOST */
	$server_port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 8443;
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $server_port == 443 || $server_port == 8443 ? "https" : "http";
	$host = $_SERVER['HTTP_HOST'] ?? 'lawyermolochko.ddev.site';
	// Remove port from host if it's already there to avoid double ports
	$host = preg_replace('/:\d+$/', '', $host);
	$port = in_array($server_port, [80, 443]) ? '' : ':' . $server_port;
	// Only define if not already set (allows database values to take precedence)
	if (!defined('WP_HOME')) {
		define( 'WP_HOME', $protocol . '://' . $host . $port );
	}
	if (!defined('WP_SITEURL')) {
		define( 'WP_SITEURL', WP_HOME . '/' );
	}

	/** Enable debug */
	defined( 'WP_DEBUG' ) || define( 'WP_DEBUG', true );

	/**
	 * Set WordPress Database Table prefix if not already set.
	 *
	 * @global string $table_prefix
	 */
	if ( ! isset( $table_prefix ) || empty( $table_prefix ) ) {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$table_prefix = 'wp_';
		// phpcs:enable
	}
}
