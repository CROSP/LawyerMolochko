<?php
/**
 * Update Elementor Data Tool
 *
 * Updates the raw _elementor_data meta value for a post.
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
 * Update Elementor Data Tool Class
 *
 * Updates the _elementor_data meta value directly in the database.
 * Includes security checks and validates that the post has Elementor enabled.
 *
 * @since 1.0.0
 */
class Update_Elementor_Data extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'update_elementor_data';
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
		return 'Updates the raw _elementor_data meta value for a post. Verifies post exists and has Elementor enabled.';
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
					'description' => 'The WordPress post ID to update Elementor data for',
					'minimum'     => 1,
				),
				'data'    => array(
					'type'        => 'array',
					'description' => 'The Elementor data to save as an array of elements',
					'items'       => array(
						'type' => 'object',
					),
				),
			),
			'required'   => array( 'post_id', 'data' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Updates the _elementor_data meta value with security checks.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments containing post_id and data.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		$post_id = absint( $args['post_id'] );
		$data = $args['data'];

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
				'You do not have permission to edit this post',
				'permission_denied'
			);
		}

		// Verify post has Elementor enabled
		$edit_mode = get_post_meta( $post_id, '_elementor_edit_mode', true );
		if ( 'builder' !== $edit_mode ) {
			return $this->format_error(
				'This post does not have Elementor enabled',
				'elementor_not_enabled',
				array(
					'edit_mode' => $edit_mode,
					'hint'      => 'Use create_elementor_page tool to enable Elementor on this post',
				)
			);
		}

		// Process the data
		$json_data = $data;

		// If data is an array, convert to JSON
		if ( is_array( $data ) ) {
			// Validate array structure
			$validation = $this->validate_elements_array( $data );
			if ( is_wp_error( $validation ) ) {
				return $this->format_error(
					$validation->get_error_message(),
					'invalid_data',
					$validation->get_error_data()
				);
			}

			// Use JSON_UNESCAPED_UNICODE to preserve Unicode characters
			$json_data = wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( false === $json_data ) {
				return $this->format_error(
					'Failed to encode data as JSON',
					'json_encode_failed'
				);
			}
		}

		// If data is a string, validate it's valid JSON
		if ( is_string( $json_data ) ) {
			json_decode( $json_data );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return $this->format_error(
					sprintf( 'Invalid JSON data: %s', json_last_error_msg() ),
					'invalid_json',
					array( 'json_error' => json_last_error() )
				);
			}
		}

		// Update the post meta
		// Use wp_slash to prevent unslashing during update_post_meta
		$updated = update_post_meta( $post_id, '_elementor_data', wp_slash( $json_data ) );

		if ( false === $updated ) {
			return $this->format_error(
				'Failed to update _elementor_data meta',
				'update_failed'
			);
		}

		// Clear Elementor cache
		if ( class_exists( '\Elementor\Plugin' ) ) {
			// Delete post CSS
			$post_css = \Elementor\Core\Files\CSS\Post::create( $post_id );
			$post_css->delete();

			// If we can get the document, delete its cache
			if ( did_action( 'elementor/loaded' ) ) {
				$document = \Elementor\Plugin::$instance->documents->get( $post_id );
				if ( $document ) {
					$document->delete_meta( '_elementor_element_cache' );
				}
			}
		}

		$result = array(
			'post_id'  => $post_id,
			'updated'  => true,
			'data_length' => strlen( $json_data ),
			'elements_count' => is_array( $data ) ? count( $data ) : count( json_decode( $json_data, true ) ),
		);

		return $this->format_success( $result, 'Elementor data updated successfully' );
	}

	/**
	 * Validate elements array structure.
	 *
	 * Ensures elements array has basic valid structure.
	 * Recursively validates nested elements.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $elements Elements array to validate.
	 * @param string $path Path to current element for error reporting.
	 * @return true|\WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_elements_array( $elements, $path = '' ) {
		if ( ! is_array( $elements ) ) {
			return new \WP_Error(
				'invalid_elements',
				'Data must be an array of elements'
			);
		}

		foreach ( $elements as $index => $element ) {
			$current_path = $path ? "{$path}[{$index}]" : "index {$index}";

			if ( ! is_array( $element ) ) {
				return new \WP_Error(
					'invalid_element',
					sprintf( 'Element at %s is not an array', $current_path )
				);
			}

			// Each element should have an elType
			if ( ! isset( $element['elType'] ) ) {
				return new \WP_Error(
					'missing_eltype',
					sprintf( 'Element at %s is missing elType property', $current_path )
				);
			}

			// Widget elements must have widgetType
			if ( 'widget' === $element['elType'] && ! isset( $element['widgetType'] ) ) {
				return new \WP_Error(
					'missing_widget_type',
					sprintf( 'Widget element at %s is missing widgetType property', $current_path )
				);
			}

			// Recursively validate nested elements
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$nested_validation = $this->validate_elements_array(
					$element['elements'],
					$current_path
				);
				if ( is_wp_error( $nested_validation ) ) {
					return $nested_validation;
				}
			}
		}

		return true;
	}
}
