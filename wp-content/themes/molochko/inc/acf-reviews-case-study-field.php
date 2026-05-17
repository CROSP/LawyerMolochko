<?php
/**
 * ACF: Related Case Study field on Reviews CPT.
 * Links to Case Study archive (or selected case study single).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'molochko_acf_reviews_related_case_study_field' );
function molochko_acf_reviews_related_case_study_field() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'                   => 'group_molochko_reviews_case_study',
		'title'                 => __( 'Reviews – Related Case Study', 'molochko' ),
		'fields'                => array(
			array(
				'key'               => 'field_molochko_reviews_related_case_study',
				'label'             => __( 'Related Case Study', 'molochko' ),
				'name'              => 'related_case_study',
				'type'              => 'post_object',
				'post_type'         => array( 'molochko-case-study' ),
				'return_format'     => 'object',
				'multiple'          => 0,
				'allow_null'        => 1,
				'ui'                => 1,
				'instructions'      => __( 'Optional. Link from this review points to the Case Study archive.', 'molochko' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'reviews',
				),
			),
		),
	) );
}
