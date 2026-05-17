<?php
/**
 * One-time script: set area_icon on existing pxl-practice-area posts by matching title to practice-areas-data.php.
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/update-practice-area-icons.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) && ! function_exists( 'get_posts' ) ) {
	fwrite( STDERR, "Run via: wp eval-file wp-content/themes/molochko/inc/update-practice-area-icons.php\n" );
	exit( 1 );
}

$data_file = get_template_directory() . '/inc/practice-areas-data.php';
if ( ! file_exists( $data_file ) ) {
	WP_CLI::error( 'practice-areas-data.php not found.' );
	exit( 1 );
}

$items = include $data_file;
if ( ! is_array( $items ) || empty( $items ) ) {
	WP_CLI::warning( 'No items in practice-areas-data.php.' );
	exit( 0 );
}

$title_to_icon = array();
foreach ( $items as $item ) {
	$t = isset( $item['title'] ) ? trim( $item['title'] ) : '';
	$icon = isset( $item['icon'] ) ? trim( $item['icon'] ) : '';
	if ( $t !== '' && $icon !== '' ) {
		$title_to_icon[ $t ] = $icon;
	}
}

$posts = get_posts( array(
	'post_type'      => 'pxl-practice-area',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

$updated = 0;
foreach ( $posts as $post ) {
	$title = $post->post_title;
	if ( ! isset( $title_to_icon[ $title ] ) ) {
		continue;
	}
	$icon = $title_to_icon[ $title ];
	$prev = get_post_meta( $post->ID, 'area_icon', true );
	if ( $prev === $icon ) {
		continue;
	}
	update_post_meta( $post->ID, 'area_icon', $icon );
	if ( function_exists( 'update_field' ) ) {
		update_field( 'area_icon', $icon, $post->ID );
	}
	$updated++;
	WP_CLI::log( "  {$title} => {$icon}" );
}

WP_CLI::success( "Updated {$updated} practice area icon(s)." );
