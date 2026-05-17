<?php
/**
 * Case Study CPT and taxonomy case_study_category (Кейси).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Case Study post type and taxonomy.
 */
add_action( 'init', 'molochko_register_case_study_cpt', 5 );
function molochko_register_case_study_cpt() {
	$slug_cpt = 'case-study';
	$slug_tax = 'case-study-category';

	if ( ! post_type_exists( 'molochko-case-study' ) ) {
		register_post_type( 'molochko-case-study', array(
			'labels'             => array(
				'name'               => __( 'Кейси', 'molochko' ),
				'singular_name'      => __( 'Кейс', 'molochko' ),
				'add_new'            => __( 'Додати кейс', 'molochko' ),
				'add_new_item'       => __( 'Додати кейс', 'molochko' ),
				'edit_item'          => __( 'Редагувати кейс', 'molochko' ),
				'view_item'          => __( 'Переглянути кейс', 'molochko' ),
				'menu_name'          => __( 'Кейси', 'molochko' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'has_archive'        => true,
			'rewrite'            => array( 'slug' => $slug_cpt ),
			'supports'           => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
			'menu_icon'          => 'dashicons-portfolio',
		) );
	}

	if ( ! taxonomy_exists( 'case_study_category' ) ) {
		register_taxonomy( 'case_study_category', array( 'molochko-case-study' ), array(
			'labels'            => array(
				'name'          => __( 'Категорії кейсів', 'molochko' ),
				'singular_name'  => __( 'Категорія кейсу', 'molochko' ),
				'menu_name'      => __( 'Категорії', 'molochko' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => $slug_tax ),
			'show_admin_column' => true,
		) );
	}
}
