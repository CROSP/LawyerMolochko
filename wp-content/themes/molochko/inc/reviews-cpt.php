<?php
/**
 * Reviews CPT: ensure archive at /reviews/ and register Case Category taxonomy.
 * CPT "reviews" is created in ACF; we only set has_archive and register taxonomy.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure Reviews CPT has archive at /reviews/ (runs when post type is registered).
 */
add_filter( 'register_post_type_args', 'molochko_reviews_cpt_archive_args', 10, 2 );
function molochko_reviews_cpt_archive_args( $args, $post_type ) {
	if ( $post_type === 'reviews' ) {
		$args['has_archive'] = true;
		$args['rewrite']     = array( 'slug' => 'reviews' );
	}
	return $args;
}

/**
 * Assign Case Category taxonomy (case_study_category) to Reviews CPT.
 * ACF field "case_category" uses case_study_category.
 */
add_action( 'init', 'molochko_reviews_use_case_category_taxonomy', 8 );
function molochko_reviews_use_case_category_taxonomy() {
	if ( ! post_type_exists( 'reviews' ) || ! taxonomy_exists( 'case_study_category' ) ) {
		return;
	}
	register_taxonomy_for_object_type( 'case_study_category', 'reviews' );
}
