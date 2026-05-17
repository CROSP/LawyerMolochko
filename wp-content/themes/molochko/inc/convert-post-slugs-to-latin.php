<?php
/**
 * One-time: convert post slugs (post_name) from Cyrillic to Latin.
 * Covers post types: post, page, molochko-case-study, pxl-practice-area. Requires Cyr-To-Lat plugin.
 * Makes _wp_old_slug meta so WordPress can redirect old URLs. Make a DB backup before running.
 *
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/convert-post-slugs-to-latin.php
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

// Post types to convert (same idea as Cyr-To-Lat converter).
$post_types   = array( 'post', 'page', 'molochko-case-study', 'pxl-practice-area' );
$post_types   = array_filter( (array) apply_filters( 'ctl_post_types', $post_types ) );
$post_statuses = array( 'publish', 'private' );

global $wpdb;

$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
$status_placeholders = implode( ',', array_fill( 0, count( $post_statuses ), '%s' ) );
$params = array_merge( array( '^([a-z0-9\'-._]|%[2-7][0-9a-f])+$' ), $post_types, $post_statuses );

// Find posts whose slug is not already Latin (MySQL REGEXP: slug does not match Latin pattern).
$sql = $wpdb->prepare(
	"SELECT ID, post_name, post_type, post_status, post_parent FROM $wpdb->posts
	WHERE LOWER(post_name) NOT REGEXP %s
	AND post_type IN ($placeholders)
	AND post_status IN ($status_placeholders)
	AND post_name != ''",
	$params
);

// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery
$posts = $wpdb->get_results( $sql );

if ( empty( $posts ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::success( 'No posts with Cyrillic (or non-Latin) slugs found.' );
	}
	exit( 0 );
}

$updated = 0;
foreach ( $posts as $post ) {
	$decoded   = rawurldecode( $post->post_name );
	$latin     = cyr_to_lat()->transliterate( $decoded );
	$new_slug  = sanitize_title( $latin );

	// Skip if already Latin-only.
	if ( preg_match( '/^[a-z0-9\-._]+$/', $post->post_name ) ) {
		continue;
	}
	if ( $new_slug === $post->post_name || $new_slug === $decoded ) {
		continue;
	}

	$new_slug = wp_unique_post_slug(
		$new_slug,
		(int) $post->ID,
		$post->post_status,
		$post->post_type,
		(int) $post->post_parent
	);

	$old_slug = $post->post_name;
	$result   = wp_update_post( array(
		'ID'        => (int) $post->ID,
		'post_name' => $new_slug,
	), true );

	if ( ! is_wp_error( $result ) ) {
		update_post_meta( (int) $post->ID, '_wp_old_slug', $old_slug );
		$updated++;
		if ( class_exists( 'WP_CLI' ) ) {
			$title = get_the_title( $post->ID );
			WP_CLI::log( sprintf( 'Updated #%d "%s": %s → %s', $post->ID, $title, $old_slug, $new_slug ) );
		}
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( sprintf( 'Converted %d post slug(s) to Latin.', $updated ) );
}
