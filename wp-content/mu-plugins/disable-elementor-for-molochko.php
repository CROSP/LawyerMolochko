<?php
/**
 * Plugin Name: Disable Elementor for Molochko Theme
 * Description: Stops Elementor (and Elementor Pro) from loading when the Molochko theme is active. Use this when running the plain-code/ACF theme.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Elementor from active plugins when Molochko theme is active.
 * Must-use plugins load before regular plugins, so this runs in time.
 */
add_filter( 'option_active_plugins', function ( $plugins ) {
	if ( get_option( 'stylesheet' ) !== 'molochko' && get_option( 'template' ) !== 'molochko' ) {
		return $plugins;
	}
	if ( ! is_array( $plugins ) ) {
		return $plugins;
	}
	$block = array( 'elementor/elementor.php', 'elementor-pro/elementor-pro.php' );
	$plugins = array_values( array_filter( $plugins, function ( $p ) use ( $block ) {
		return ! in_array( $p, $block, true );
	} ) );
	return $plugins;
}, 1 );

/**
 * Multisite: remove from sitewide plugins list when Molochko is active.
 */
add_filter( 'option_active_sitewide_plugins', function ( $plugins ) {
	if ( get_option( 'stylesheet' ) !== 'molochko' && get_option( 'template' ) !== 'molochko' ) {
		return $plugins;
	}
	if ( ! is_array( $plugins ) ) {
		return $plugins;
	}
	unset( $plugins['elementor/elementor.php'], $plugins['elementor-pro/elementor-pro.php'] );
	return $plugins;
}, 1 );
