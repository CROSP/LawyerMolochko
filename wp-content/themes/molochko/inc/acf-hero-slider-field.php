<?php
/**
 * ACF: Custom hero slider (replaces RevSlider). Use when you want to avoid RevSlider DB/cache issues.
 * Front page only: "Use custom hero slider" + repeater "Hero slides" (image, title, caption, link).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'molochko_acf_hero_slider_field' );
function molochko_acf_hero_slider_field() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'                   => 'group_molochko_hero_slider',
		'title'                 => __( 'Hero – Custom slider (no RevSlider)', 'molochko' ),
		'fields'                => array(
			array(
				'key'          => 'field_molochko_use_custom_hero_slider',
				'label'        => __( 'Use custom hero slider', 'molochko' ),
				'name'         => 'use_custom_hero_slider',
				'type'         => 'true_false',
				'message'      => __( 'Show hero from "Hero slides" below instead of RevSlider. Images are stored in WordPress media; no DB URL issues on deploy.', 'molochko' ),
				'default_value'=> 0,
			),
			array(
				'key'        => 'field_molochko_hero_slides',
				'label'      => __( 'Hero slides', 'molochko' ),
				'name'       => 'hero_slides',
				'type'       => 'repeater',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_molochko_use_custom_hero_slider',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
				'min'        => 1,
				'layout'     => 'block',
				'sub_fields' => array(
					array(
						'key'   => 'field_molochko_hero_slide_image',
						'label' => __( 'Image', 'molochko' ),
						'name'  => 'image',
						'type'  => 'image',
						'required' => 1,
						'return_format' => 'array',
						'preview_size' => 'medium',
					),
					array(
						'key'   => 'field_molochko_hero_slide_title',
						'label' => __( 'Title', 'molochko' ),
						'name'  => 'title',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_molochko_hero_slide_caption',
						'label' => __( 'Caption', 'molochko' ),
						'name'  => 'caption',
						'type'  => 'textarea',
						'rows'  => 2,
					),
					array(
						'key'   => 'field_molochko_hero_slide_link',
						'label' => __( 'Link URL', 'molochko' ),
						'name'  => 'link',
						'type'  => 'url',
						'instructions' => __( 'Optional. Clicking the slide goes to this URL.', 'molochko' ),
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'page_type',
					'operator' => '==',
					'value'    => 'front_page',
				),
			),
		),
	) );
}
