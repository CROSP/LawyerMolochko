<?php
/**
 * Practice Area CPT and ACF field group (Напрямки юридичної практики).
 * CPT registered via acf/init; field group attached to pxl-practice-area.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register pxl-practice-area post type (via ACF init).
 */
add_action( 'acf/init', 'molochko_register_practice_area_cpt_acf', 5 );
function molochko_register_practice_area_cpt_acf() {
	if ( post_type_exists( 'pxl-practice-area' ) ) {
		return;
	}
	register_post_type( 'pxl-practice-area', array(
		'labels'             => array(
			'name'               => __( 'Напрямки практики', 'molochko' ),
			'singular_name'      => __( 'Напрямок практики', 'molochko' ),
			'add_new'            => __( 'Додати', 'molochko' ),
			'add_new_item'       => __( 'Додати напрямок практики', 'molochko' ),
			'edit_item'          => __( 'Редагувати', 'molochko' ),
			'view_item'          => __( 'Переглянути', 'molochko' ),
			'menu_name'          => __( 'Напрямки практики', 'molochko' ),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'practice-area' ),
		'supports'           => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
		'menu_icon'          => 'dashicons-image-filter',
	) );
}

/**
 * Register ACF field group for pxl-practice-area.
 */
add_action( 'acf/init', 'molochko_acf_practice_area_field_group', 10 );
function molochko_acf_practice_area_field_group() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'                   => 'group_molochko_practice_area',
		'title'                 => __( 'Напрямок практики (поля картки)', 'molochko' ),
		'fields'                => array(
			array(
				'key'           => 'field_pa_area_icon_type',
				'label'         => __( 'Тип іконки', 'molochko' ),
				'name'          => 'area_icon_type',
				'type'          => 'select',
				'choices'       => array(
					''       => __( 'Іконка (CSS клас)', 'molochko' ),
					'image'  => __( 'Зображення', 'molochko' ),
				),
				'default_value' => '',
				'instructions'  => __( 'Як показувати іконку в картці на головній.', 'molochko' ),
			),
			array(
				'key'           => 'field_pa_area_icon',
				'label'         => __( 'Іконка (CSS класи)', 'molochko' ),
				'name'          => 'area_icon',
				'type'          => 'text',
				'placeholder'   => 'flaticon flaticon-businessman',
				'instructions'  => __( 'Наприклад: flaticon flaticon-medal, flaticon flaticon-idea. Використовується, якщо тип іконки не «Зображення».', 'molochko' ),
			),
			array(
				'key'           => 'field_pa_area_img',
				'label'         => __( 'Іконка (зображення)', 'molochko' ),
				'name'          => 'area_img',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'thumbnail',
				'instructions'  => __( 'Опціонально. Якщо тип іконки «Зображення» або потрібна своя картинка замість CSS-іконки.', 'molochko' ),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'pxl-practice-area',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'show_in_rest'          => 1,
		'description'           => __( 'Поля для карток у секції «Напрямки юридичної практики» на головній.', 'molochko' ),
	) );
}
