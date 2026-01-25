<?php
/**
 * Pexels API Client
 *
 * Handles all communication with the Pexels API.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pexels Client Class
 *
 * @since 1.0.0
 */
class Pexels_Client {

	/**
	 * API Base URL
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const API_BASE = 'https://api.pexels.com/v1/';

	/**
	 * Video API Base URL
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const VIDEO_API_BASE = 'https://api.pexels.com/videos/';

	/**
	 * API Key
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @param string $api_key Pexels API key.
	 */
	public function __construct( $api_key = '' ) {
		$this->api_key = $api_key ? $api_key : $this->get_api_key();
	}

	/**
	 * Get API key from configuration
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_api_key() {
		return get_option( 'elementor_mcp_pexels_api_key', '' );
	}

	/**
	 * Make API request
	 *
	 * @since 1.0.0
	 * @param string $endpoint API endpoint.
	 * @param array  $params   Query parameters.
	 * @param string $base_url Base URL to use.
	 * @return array|\WP_Error Response data or WP_Error.
	 */
	private function request( $endpoint, $params = array(), $base_url = self::API_BASE ) {
		if ( empty( $this->api_key ) ) {
			return new \WP_Error( 'no_api_key', 'Pexels API key is not configured.' );
		}

		$url = $base_url . $endpoint;

		if ( ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => $this->api_key,
				),
				'timeout'  => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$message = isset( $data['error'] ) ? $data['error'] : 'Unknown error';
			return new \WP_Error( 'api_error', $message, array( 'status' => $status_code ) );
		}

		return $data;
	}

	/**
	 * Search photos
	 *
	 * @since 1.0.0
	 * @param string $query   Search query.
	 * @param array  $options Additional options (per_page, page, orientation, size, color, locale).
	 * @return array|\WP_Error
	 */
	public function search_photos( $query, $options = array() ) {
		$params = array_merge(
			array(
				'query'    => $query,
				'per_page' => 15,
				'page'     => 1,
			),
			$options
		);

		return $this->request( 'search', $params );
	}

	/**
	 * Get curated photos
	 *
	 * @since 1.0.0
	 * @param array $options Options (per_page, page).
	 * @return array|\WP_Error
	 */
	public function get_curated_photos( $options = array() ) {
		$params = array_merge(
			array(
				'per_page' => 15,
				'page'     => 1,
			),
			$options
		);

		return $this->request( 'curated', $params );
	}

	/**
	 * Get photo by ID
	 *
	 * @since 1.0.0
	 * @param int $id Photo ID.
	 * @return array|\WP_Error
	 */
	public function get_photo( $id ) {
		return $this->request( "photos/{$id}" );
	}

	/**
	 * Search videos
	 *
	 * @since 1.0.0
	 * @param string $query   Search query.
	 * @param array  $options Additional options (per_page, page, orientation, size).
	 * @return array|\WP_Error
	 */
	public function search_videos( $query, $options = array() ) {
		$params = array_merge(
			array(
				'query'    => $query,
				'per_page' => 15,
				'page'     => 1,
			),
			$options
		);

		return $this->request( 'search', $params, self::VIDEO_API_BASE );
	}

	/**
	 * Get popular videos
	 *
	 * @since 1.0.0
	 * @param array $options Options (per_page, page, min_width, min_height, min_duration, max_duration).
	 * @return array|\WP_Error
	 */
	public function get_popular_videos( $options = array() ) {
		$params = array_merge(
			array(
				'per_page' => 15,
				'page'     => 1,
			),
			$options
		);

		return $this->request( 'popular', $params, self::VIDEO_API_BASE );
	}

	/**
	 * Get video by ID
	 *
	 * @since 1.0.0
	 * @param int $id Video ID.
	 * @return array|\WP_Error
	 */
	public function get_video( $id ) {
		return $this->request( "videos/{$id}", array(), self::VIDEO_API_BASE );
	}

	/**
	 * Download and import image to WordPress media library
	 *
	 * @since 1.0.0
	 * @param array $photo     Photo data from Pexels API.
	 * @param int   $post_id   Optional. Post ID to attach the image to.
	 * @param string $size     Size to download (original, large2x, large, medium, small, portrait, landscape, tiny).
	 * @return int|\WP_Error Attachment ID or WP_Error on failure.
	 */
	public function import_photo_to_media_library( $photo, $post_id = 0, $size = 'large' ) {
		if ( ! isset( $photo['src'][ $size ] ) ) {
			return new \WP_Error( 'invalid_size', "Size '{$size}' not available for this photo." );
		}

		$image_url = $photo['src'][ $size ];
		$photographer = isset( $photo['photographer'] ) ? $photo['photographer'] : 'Unknown';
		$photo_id = isset( $photo['id'] ) ? $photo['id'] : '';

		// Download image
		$temp_file = download_url( $image_url );

		if ( is_wp_error( $temp_file ) ) {
			return $temp_file;
		}

		// Prepare file array
		$file_array = array(
			'name'     => "pexels-{$photographer}-{$photo_id}.jpg",
			'tmp_name' => $temp_file,
		);

		// Import to media library
		$attachment_id = media_handle_sideload( $file_array, $post_id );

		// Clean up temp file
		if ( file_exists( $temp_file ) ) {
			@unlink( $temp_file );
		}

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Add metadata
		$alt_text = isset( $photo['alt'] ) ? $photo['alt'] : '';
		$description = sprintf(
			'Photo by %s on Pexels: %s',
			$photographer,
			isset( $photo['url'] ) ? $photo['url'] : ''
		);

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		wp_update_post(
			array(
				'ID'           => $attachment_id,
				'post_excerpt' => $description,
				'post_content' => $description,
			)
		);

		// Store Pexels metadata
		update_post_meta( $attachment_id, '_pexels_photo_id', $photo_id );
		update_post_meta( $attachment_id, '_pexels_photographer', $photographer );
		update_post_meta( $attachment_id, '_pexels_url', isset( $photo['url'] ) ? $photo['url'] : '' );

		return $attachment_id;
	}
}
