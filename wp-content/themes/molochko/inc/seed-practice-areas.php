<?php
/**
 * One-time seed: create pxl-practice-area posts from practice-areas-data.php.
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/seed-practice-areas.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Allow run via: wp eval-file wp-content/themes/molochko/inc/seed-practice-areas.php
	if ( ! function_exists( 'wp_insert_post' ) ) {
		fwrite( STDERR, "Спочатку завантажте WordPress, напр.: wp eval-file " . basename( __FILE__ ) . "\n" );
		exit( 1 );
	}
}

$data_file = get_template_directory() . '/inc/practice-areas-data.php';
if ( ! file_exists( $data_file ) ) {
	WP_CLI::error( 'Файл practice-areas-data.php не знайдено.' );
	exit( 1 );
}

$items = include $data_file;
if ( ! is_array( $items ) || empty( $items ) ) {
	WP_CLI::warning( 'У practice-areas-data.php немає записів.' );
	exit( 0 );
}

$created = 0;
$skipped = 0;

foreach ( $items as $index => $item ) {
	$title = isset( $item['title'] ) ? trim( $item['title'] ) : '';
	if ( $title === '' ) {
		continue;
	}

	// Avoid duplicates: check by title
	$existing = get_posts( array(
		'post_type'      => 'pxl-practice-area',
		'title'          => $title,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		$skipped++;
		continue;
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'pxl-practice-area',
		'post_title'   => $title,
		'post_excerpt' => isset( $item['description'] ) ? trim( $item['description'] ) : '',
		'post_content' => '',
		'post_status'  => 'publish',
		'menu_order'   => $index,
	) );

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( "Не вдалося створити: {$title}" );
		continue;
	}

	// ACF fields: icon type empty = use CSS icon, area_icon = CSS classes
	update_post_meta( $post_id, 'area_icon_type', '' );
	update_post_meta( $post_id, 'area_icon', isset( $item['icon'] ) ? trim( $item['icon'] ) : 'flaticon flaticon-businessman' );

	if ( function_exists( 'update_field' ) ) {
		update_field( 'area_icon_type', '', $post_id );
		update_field( 'area_icon', isset( $item['icon'] ) ? trim( $item['icon'] ) : 'flaticon flaticon-businessman', $post_id );
	}

	$created++;
	WP_CLI::log( "Створено: {$title} (ID {$post_id})" );
}

WP_CLI::success( "Готово. Створено {$created} записів, пропущено {$skipped} (вже існують)." );
