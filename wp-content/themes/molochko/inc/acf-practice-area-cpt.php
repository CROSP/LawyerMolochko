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
 * Practice area archive: show all items, no pagination.
 */
add_action( 'pre_get_posts', 'molochko_practice_area_archive_no_pagination' );
function molochko_practice_area_archive_no_pagination( $query ) {
	if ( ! $query->is_main_query() || ! $query->is_post_type_archive( 'pxl-practice-area' ) ) {
		return;
	}
	$query->set( 'posts_per_page', -1 );
}

// ACF field group for pxl-practice-area lives in DB only. Create via: php import-acf-groups.php
