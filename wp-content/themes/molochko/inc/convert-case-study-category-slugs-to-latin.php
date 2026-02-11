<?php
/**
 * One-time: convert case_study_category term slugs from Cyrillic to Latin.
 * Requires Cyr-To-Lat plugin to be active. Make a DB backup before running.
 *
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/convert-case-study-category-slugs-to-latin.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'cyr_to_lat' ) || ! is_object( cyr_to_lat() ) || ! method_exists( cyr_to_lat(), 'transliterate' ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::error( 'Cyr-To-Lat plugin must be active. Activate it and run again.' );
	}
	exit( 1 );
}

if ( ! taxonomy_exists( 'case_study_category' ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::warning( 'Taxonomy case_study_category does not exist.' );
	}
	exit( 0 );
}

$terms = get_terms( array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => false,
) );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( 'No case_study_category terms found.' );
	}
	exit( 0 );
}

$updated = 0;
foreach ( $terms as $term ) {
	$current_slug = $term->slug;
	$decoded      = rawurldecode( $current_slug );
	$latin        = cyr_to_lat()->transliterate( $decoded );
	$new_slug     = sanitize_title( $latin );

	// Skip if already Latin (only a-z, 0-9, dashes).
	if ( $new_slug === $current_slug || $new_slug === $decoded ) {
		continue;
	}
	if ( preg_match( '/^[a-z0-9\-]+$/', $current_slug ) ) {
		continue;
	}

	$result = wp_update_term( $term->term_id, 'case_study_category', array( 'slug' => $new_slug ) );
	if ( ! is_wp_error( $result ) ) {
		$updated++;
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( sprintf( 'Updated "%s": %s → %s', $term->name, $current_slug, $new_slug ) );
		}
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( sprintf( 'Converted %d case study category slug(s) to Latin.', $updated ) );
}
