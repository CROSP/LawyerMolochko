<?php
/**
 * Reviews section: data from Reviews CPT (person_name, post_content, case_study_category).
 * Filter: molochko_reviews_list.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get reviews for the front page section from the Reviews CPT.
 *
 * @return array List of { name, text, case_type }.
 */
function molochko_get_reviews() {
	if ( ! post_type_exists( 'reviews' ) ) {
		return apply_filters( 'molochko_reviews_list', array() );
	}
	$query_args = array(
		'post_type'      => 'reviews',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) {
			$query_args['lang'] = $lang;
		}
	}
	$posts = get_posts( $query_args );
	$reviews = array();
	foreach ( $posts as $post ) {
		$name = get_field( 'person_name', $post->ID );
		if ( $name === null || $name === false || $name === '' ) {
			$name = $post->post_title;
		}
		$text = $post->post_content;
		if ( $text === '' ) {
			continue;
		}
		$case_type = '';
		$terms     = get_the_terms( $post->ID, 'case_study_category' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$case_type = $terms[0]->name;
		}
		$case_study_archive_url = get_post_type_archive_link( 'molochko-case-study' );
		$reviews[] = array(
			'name'                   => is_string( $name ) ? $name : '',
			'text'                   => $text,
			'case_type'              => $case_type,
			'case_study_archive_url' => $case_study_archive_url,
		);
	}
	return apply_filters( 'molochko_reviews_list', $reviews );
}
