<?php
/**
 * Import Photo Tool
 *
 * Download and import a Pexels photo to WordPress media library.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Pexels;

use ElementorMCP\Tools\Base_Tool;
use ElementorMCP\Integrations\Pexels_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Photo Tool
 *
 * @since 1.0.0
 */
class Import_Photo extends Base_Tool {

	/**
	 * Get tool name
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'pexels_import_photo';
	}

	/**
	 * Get tool description
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_description() {
		return 'Download a Pexels photo and import it directly to WordPress media library with proper attribution and metadata.';
	}

	/**
	 * Get input schema
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'photo_id' => array(
					'type'        => 'integer',
					'description' => 'Pexels photo ID to import',
					'minimum'     => 1,
				),
				'size'     => array(
					'type'        => 'string',
					'description' => 'Size to download (original, large2x, large, medium, small, portrait, landscape, tiny)',
					'enum'        => array( 'original', 'large2x', 'large', 'medium', 'small', 'portrait', 'landscape', 'tiny' ),
					'default'     => 'large',
				),
				'post_id'  => array(
					'type'        => 'integer',
					'description' => 'Optional. WordPress post ID to attach the image to',
					'minimum'     => 1,
				),
			),
			'required'   => array( 'photo_id' ),
		);
	}

	/**
	 * Execute tool
	 *
	 * @since 1.0.0
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args ) {
		// Check permissions
		if ( ! $this->check_permissions( 'upload_files' ) ) {
			return $this->format_error( 'Permission denied. User must have upload_files capability.', 'permission_denied' );
		}

		// Load required WordPress functions
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$client = new Pexels_Client();
		$photo_id = $args['photo_id'];
		$size = isset( $args['size'] ) ? $args['size'] : 'large';
		$post_id = isset( $args['post_id'] ) ? $args['post_id'] : 0;

		// First, get the photo details
		$photo = $client->get_photo( $photo_id );

		if ( is_wp_error( $photo ) ) {
			return $this->format_error(
				$photo->get_error_message(),
				$photo->get_error_code(),
				$photo->get_error_data()
			);
		}

		// Import to media library
		$attachment_id = $client->import_photo_to_media_library( $photo, $post_id, $size );

		if ( is_wp_error( $attachment_id ) ) {
			return $this->format_error(
				$attachment_id->get_error_message(),
				$attachment_id->get_error_code(),
				$attachment_id->get_error_data()
			);
		}

		// Get attachment details
		$attachment = get_post( $attachment_id );
		$attachment_url = wp_get_attachment_url( $attachment_id );
		$metadata = wp_get_attachment_metadata( $attachment_id );

		return $this->format_success(
			array(
				'attachment_id'  => $attachment_id,
				'url'            => $attachment_url,
				'title'          => $attachment->post_title,
				'alt'            => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'width'          => isset( $metadata['width'] ) ? $metadata['width'] : null,
				'height'         => isset( $metadata['height'] ) ? $metadata['height'] : null,
				'file'           => isset( $metadata['file'] ) ? $metadata['file'] : '',
				'pexels_id'      => get_post_meta( $attachment_id, '_pexels_photo_id', true ),
				'photographer'   => get_post_meta( $attachment_id, '_pexels_photographer', true ),
				'pexels_url'     => get_post_meta( $attachment_id, '_pexels_url', true ),
			),
			sprintf( 'Successfully imported photo #%d to media library (attachment ID: %d)', $photo_id, $attachment_id )
		);
	}
}
