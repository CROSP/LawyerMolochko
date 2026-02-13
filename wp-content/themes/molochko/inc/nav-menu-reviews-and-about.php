<?php
/**
 * Primary menu: point Reviews to CPT archive, remove "Про нас" (About us).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter primary menu: remove "Про нас", fix Reviews link to CPT archive.
 *
 * @param array $sorted_menu_items Menu items.
 * @param object $args              wp_nav_menu() args.
 * @return array
 */
add_filter( 'wp_nav_menu_objects', 'molochko_nav_menu_primary_reviews_and_about', 10, 2 );
function molochko_nav_menu_primary_reviews_and_about( $sorted_menu_items, $args ) {
	if ( isset( $args->theme_location ) && $args->theme_location !== 'primary' ) {
		return $sorted_menu_items;
	}
	$reviews_archive_url = get_post_type_archive_link( 'reviews' );
	$filtered = array();
	foreach ( $sorted_menu_items as $item ) {
		// Remove "Про нас" (About us) – anchor #about or title "Про нас".
		$is_about = ( strpos( (string) $item->url, '#about' ) !== false )
			|| ( trim( $item->title ) === 'Про нас' );
		if ( $is_about ) {
			continue;
		}
		// Point Reviews to CPT archive: item linked to legacy page (4666) or URL contains reviews-legacy.
		if ( (int) $item->object_id === 4666 && $item->object === 'page' ) {
			$item->url = $reviews_archive_url;
		} elseif ( $reviews_archive_url && strpos( (string) $item->url, 'reviews-legacy' ) !== false ) {
			$item->url = $reviews_archive_url;
		}
		$filtered[] = $item;
	}
	return $filtered;
}
