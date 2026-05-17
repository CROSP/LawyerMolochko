<?php
/**
 * Fetch latest Instagram Reels via Graph API for Law Talk carousel.
 * Requires Instagram Business/Creator account, Facebook App, and long-lived User Access Token.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch latest Reels permalinks for an Instagram user.
 * Results are cached in a transient for 1 hour.
 *
 * @param string $user_id   Instagram Graph API user ID (numeric).
 * @param string $token     Long-lived User Access Token with instagram_basic.
 * @param int    $limit     Max number of reels to return. Default 12.
 * @return array List of reel permalink URLs (strings), newest first.
 */
function molochko_get_latest_instagram_reels( $user_id, $token, $limit = 12 ) {
	$user_id = preg_replace( '/\D/', '', (string) $user_id );
	$token   = trim( (string) $token );
	if ( $user_id === '' || $token === '' ) {
		return array();
	}

	$cache_key = 'law_talk_ig_reels_' . $user_id . '_' . substr( md5( $token ), 0, 8 );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return array_slice( $cached, 0, $limit );
	}

	$url = add_query_arg(
		array(
			'fields'       => 'id,media_type,permalink',
			'limit'       => min( 50, (int) $limit * 2 ),
			'access_token' => $token,
		),
		'https://graph.instagram.com/v18.0/' . $user_id . '/media'
	);

	$response = wp_remote_get( $url, array(
		'timeout' => 15,
		'sslverify' => true,
	) );

	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
		set_transient( $cache_key, array(), 5 * MINUTE_IN_SECONDS );
		return array();
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$data = isset( $body['data'] ) && is_array( $body['data'] ) ? $body['data'] : array();
	$reels = array();

	foreach ( $data as $item ) {
		$type = isset( $item['media_type'] ) ? $item['media_type'] : '';
		if ( strtoupper( $type ) !== 'REELS' ) {
			continue;
		}
		$permalink = isset( $item['permalink'] ) ? trim( $item['permalink'] ) : '';
		if ( $permalink !== '' ) {
			$reels[] = $permalink;
		}
	}

	set_transient( $cache_key, $reels, HOUR_IN_SECONDS );
	return array_slice( $reels, 0, $limit );
}
