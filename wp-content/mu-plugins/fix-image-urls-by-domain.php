<?php
/**
 * Plugin Name: Fix image URLs by current domain
 * Description: Replaces hardcoded site URLs in content and media with the current request URL so images load on both lawyermolochko.ddev.site and lawyer-molochko.com.ua.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'the_content', 'lawyermolochko_fix_content_image_urls', 20 );
add_filter( 'elementor/frontend/the_content', 'lawyermolochko_fix_content_image_urls', 20 );
add_filter( 'post_thumbnail_url', 'lawyermolochko_fix_attachment_url', 10, 3 );
add_filter( 'wp_get_attachment_url', 'lawyermolochko_fix_attachment_url_single', 10, 2 );
add_filter( 'wp_calculate_image_srcset', 'lawyermolochko_fix_srcset_urls', 10, 5 );

// Fix double slash in URLs on final HTML (catches Elementor/other output that bypasses content filters)
add_action( 'template_redirect', 'lawyermolochko_ob_start_fix_urls', 0 );
function lawyermolochko_ob_start_fix_urls() {
	ob_start( 'lawyermolochko_fix_final_html_urls' );
}
function lawyermolochko_fix_final_html_urls( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	// Fix //wp-content and :8443// (double slash in path)
	$html = str_replace( '//wp-content', '/wp-content', $html );
	$html = preg_replace( '#(https?://[^/]+)/+#', '$1/', $html );
	return $html;
}

function lawyermolochko_fix_content_image_urls( $content ) {
	if ( ! is_string( $content ) || '' === $content ) {
		return $content;
	}
	$current = home_url( '/' );
	$bases   = array(
		'https://lawyer-molochko.com.ua:8443/',
		'http://lawyer-molochko.com.ua:8443/',
		'https://lawyermolochko.ddev.site:8443/',
		'http://lawyermolochko.ddev.site:8443/',
		'https://lawyermolochko.ddev.site:8080/',
		'http://lawyermolochko.ddev.site:8080/',
	);
	foreach ( $bases as $base ) {
		if ( $base !== $current && strpos( $content, $base ) !== false ) {
			$content = str_replace( $base, $current, $content );
		}
	}
	// Fix triple/double slash after host (e.g. 8443///wp-content -> 8443/wp-content)
	$content = preg_replace( '#(https?://[^/]+)/+#', '$1/', $content );
	return $content;
}

function lawyermolochko_fix_attachment_url( $url, $post, $size ) {
	return lawyermolochko_normalize_media_url( $url );
}

function lawyermolochko_fix_attachment_url_single( $url, $attachment_id ) {
	return lawyermolochko_normalize_media_url( $url );
}

function lawyermolochko_normalize_media_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}
	$current = home_url( '/' );
	$bases   = array(
		'https://lawyer-molochko.com.ua:8443',
		'http://lawyer-molochko.com.ua:8443',
		'https://lawyermolochko.ddev.site:8443',
		'http://lawyermolochko.ddev.site:8443',
		'https://lawyermolochko.ddev.site:8080',
		'http://lawyermolochko.ddev.site:8080',
	);
	foreach ( $bases as $base ) {
		if ( strpos( $url, $base ) === 0 ) {
			$path = substr( $url, strlen( $base ) );
			$path = '/' . ltrim( $path, '/' );
			return rtrim( $current, '/' ) . $path;
		}
	}
	return $url;
}

function lawyermolochko_fix_srcset_urls( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( ! is_array( $sources ) ) {
		return $sources;
	}
	foreach ( $sources as $width => $data ) {
		if ( ! empty( $data['url'] ) ) {
			$sources[ $width ]['url'] = lawyermolochko_normalize_media_url( $data['url'] );
		}
	}
	return $sources;
}
