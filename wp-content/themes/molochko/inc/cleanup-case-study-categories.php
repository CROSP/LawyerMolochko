<?php
/**
 * Remove unused case_study_category terms (e.g. old English: Car Accident, Health Care Law, Family Law).
 * Keeps only terms that are in use by at least one case study post.
 * Run once: ddev exec wp eval-file wp-content/themes/molochko/inc/cleanup-case-study-categories.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! taxonomy_exists( 'case_study_category' ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::error( 'Taxonomy case_study_category does not exist.' );
	}
	exit( 1 );
}

$terms = get_terms( array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => false,
) );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::success( 'No case study categories found.' );
	}
	exit( 0 );
}

$deleted = 0;
foreach ( $terms as $term ) {
	if ( (int) $term->count === 0 ) {
		wp_delete_term( $term->term_id, 'case_study_category' );
		$deleted++;
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::log( "Deleted unused category: {$term->name}" );
		}
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( "Deleted {$deleted} unused case study categor(ies)." );
}
