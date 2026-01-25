<?php
/**
 * Get Elementor Data Tool
 *
 * Retrieves raw _elementor_data meta value (JSON) for a post.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Documents;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Elementor Data Tool Class
 *
 * Returns the raw _elementor_data meta value (JSON string) for a given post ID.
 * This is the low-level storage format used by Elementor.
 *
 * @since 1.0.0
 */
class Get_Elementor_Data extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_elementor_data';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Retrieves the raw _elementor_data meta value (JSON) stored in the database for a given post ID.';
	}

	/**
	 * Get input schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JSON schema for input validation.
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'The WordPress post ID to retrieve Elementor data for',
					'minimum'     => 1,
				),
			),
			'required'   => array( 'post_id' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves the raw _elementor_data meta value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments containing post_id.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		$post_id = absint( $args['post_id'] );

		// Check if post exists
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $this->format_error(
				sprintf( 'Post with ID %d does not exist', $post_id ),
				'post_not_found'
			);
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $this->format_error(
				'You do not have permission to access this post',
				'permission_denied'
			);
		}

		// Get the raw _elementor_data meta value
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

		// Check if this post has Elementor data
		$has_elementor_data = ! empty( $elementor_data );
		$is_built_with_elementor = get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder';

		// If the data is a JSON string, decode it for validation
		$decoded_data = null;
		if ( $has_elementor_data && is_string( $elementor_data ) ) {
			$decoded_data = json_decode( $elementor_data, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return $this->format_error(
					sprintf( 'Invalid JSON data: %s', json_last_error_msg() ),
					'invalid_json',
					array( 'json_error' => json_last_error() )
				);
			}
		}

		$result = array(
			'post_id'                 => $post_id,
			'has_elementor_data'      => $has_elementor_data,
			'is_built_with_elementor' => $is_built_with_elementor,
			'raw_data'                => $elementor_data,
			'decoded_data'            => $decoded_data,
			'elements_count'          => is_array( $decoded_data ) ? count( $decoded_data ) : 0,
		);

		return $this->format_success( $result, 'Elementor data retrieved successfully' );
	}
}
