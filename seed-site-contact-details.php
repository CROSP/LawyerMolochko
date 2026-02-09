<?php
/**
 * One-time script: fill ACF Options page "Site Contact Details" with default values.
 * Fields: phone_number, working_hours, email, address (match your ACF group).
 *
 * Run from project root:
 *   ddev exec php seed-site-contact-details.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( __FILE__ ) . '/wp-load.php';
	if ( ! is_file( $wp_load ) ) {
		fwrite( STDERR, "Error: wp-load.php not found. Run from project root.\n" );
		exit( 1 );
	}
	require_once $wp_load;
}

if ( ! function_exists( 'update_field' ) ) {
	fwrite( STDERR, "Error: ACF not active.\n" );
	exit( 1 );
}

// ACF stores ALL options page data under 'option' (see ACF docs: Get values from an options page).
$post_id = 'option';

$defaults = array(
	'phone_number'   => '+38 (050) 606-00-79',
	'working_hours'  => 'Пн–Пт: 9:00 – 18:00',
	'email'          => 'info@lawyermolochko.com.ua',
	'address'        => '', // Fill in Settings → Site Contact Details
);

foreach ( $defaults as $name => $value ) {
	$updated = update_field( $name, $value, $post_id );
	echo "  {$name}: " . ( $updated !== false ? "set" : "unchanged or field missing" ) . "\n";
}

echo "Done. Edit Settings → Site Contact Details in WP Admin to fill email and adjust values.\n";
