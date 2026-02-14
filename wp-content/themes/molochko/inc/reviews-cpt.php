<?php
/**
 * Reviews CPT: ensure archive at /reviews/.
 * CPT "reviews" is created in ACF. Case category is stored via ACF field (Case Category),
 * not via taxonomy — do not register case_study_category for reviews.
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
 * Case study category archive: show only case studies, never reviews.
 */
add_action( 'pre_get_posts', 'molochko_case_study_category_archive_only_case_studies' );
function molochko_case_study_category_archive_only_case_studies( $query ) {
	if ( ! $query->is_main_query() || ! $query->is_tax( 'case_study_category' ) ) {
		return;
	}
	$query->set( 'post_type', 'molochko-case-study' );
}

