<?php
/**
 * Reviews section: data from Reviews CPT (person_name, post_content, ACF Case Category).
 * Filter: molochko_reviews_list. Case category comes from ACF field only, not taxonomy.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get case type label for a review from ACF field (Case Category / Categorie caz).
 * Expects ACF field name "case_category" — text/select (label) or legacy term ID if taxonomy exists.
 *
 * @param int $post_id Review post ID.
 * @return string Category name in current language.
 */
function molochko_review_case_type_label( $post_id ) {
	$val = get_field( 'case_category', $post_id );
	if ( $val === null || $val === false || $val === '' ) {
		return '';
	}
	// If taxonomy was removed, ACF may store a string (label) or old term ID — return string only.
	if ( ! taxonomy_exists( 'case_study_category' ) ) {
		return is_string( $val ) ? $val : '';
	}
	if ( is_object( $val ) && isset( $val->term_id ) ) {
		$val = $val->term_id;
	}
	$term_id = (int) $val;
	if ( ! $term_id ) {
		return '';
	}
	if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_get_term' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) {
			$translated = (int) pll_get_term( $term_id, $lang );
			if ( $translated ) {
				$term_id = $translated;
			}
		}
	}
	$term = get_term( $term_id, 'case_study_category' );
	return ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
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
		$case_type = function_exists( 'molochko_review_case_type_label' ) ? molochko_review_case_type_label( $post->ID ) : '';
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
