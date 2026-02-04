<?php
/**
 * Remove duplicate Molochko ACF field groups from DB.
 * Deletes ALL Molochko groups (by title); run import-acf-groups.php after for exactly one of each.
 *
 * Run:  ddev exec php cleanup-acf-duplicates.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( __FILE__ ) . '/wp-load.php';
	if ( ! is_file( $wp_load ) ) {
		fwrite( STDERR, "Error: wp-load.php not found. Run from project root.\n" );
		exit( 1 );
	}
	require_once $wp_load;
}

$titles = array(
	'Головна сторінка (Molochko)',
	'Напрямок практики (поля картки)',
);

global $wpdb;
$placeholders = implode( ',', array_fill( 0, count( $titles ), '%s' ) );
$to_delete = $wpdb->get_col( $wpdb->prepare(
	"SELECT ID FROM $wpdb->posts WHERE post_type = 'acf-field-group' AND post_title IN ($placeholders)",
	...$titles
) );
$to_delete = array_map( 'intval', (array) $to_delete );

if ( empty( $to_delete ) ) {
	echo "No Molochko ACF field groups found. Nothing to clean.\n";
	exit( 0 );
}

if ( ! function_exists( 'acf_delete_field_group' ) ) {
	fwrite( STDERR, "ACF not active.\n" );
	exit( 1 );
}

foreach ( $to_delete as $id ) {
	acf_delete_field_group( $id );
	echo "Deleted field group ID: $id\n";
}

echo "Done. Deleted " . count( $to_delete ) . " group(s). Run: ddev exec php import-acf-groups.php\n";
