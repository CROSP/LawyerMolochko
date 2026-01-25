<?php
/**
 * Save Document Tool
 *
 * Saves Elementor document data including elements and settings.
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
 * Save Document Tool Class
 *
 * Saves an Elementor document with elements and settings using Document::save() method.
 * Validates elements structure before saving.
 *
 * @since 1.0.0
 */
class Save_Document extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'save_document';
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
		return 'Saves Elementor document data including elements array and settings. Validates elements structure before saving.';
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
				'post_id'  => array(
					'type'        => 'integer',
					'description' => 'The WordPress post ID to save the document for',
					'minimum'     => 1,
				),
				'elements' => array(
					'type'        => 'array',
					'description' => 'Array of Elementor elements (sections, columns, widgets) to save',
					'items'       => array(
						'type' => 'object',
					),
				),
				'settings' => array(
					'type'        => 'object',
					'description' => 'Optional document settings (page settings, custom CSS, etc.)',
				),
			),
			'required'   => array( 'post_id', 'elements' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Saves the document data using Document::save() method.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments containing post_id, elements, and optional settings.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
		}

		$post_id = absint( $args['post_id'] );
		$elements = $args['elements'];
		$settings = isset( $args['settings'] ) ? $args['settings'] : array();

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
				'You do not have permission to edit this document',
				'permission_denied'
			);
		}

		// Validate elements structure
		$validation = $this->validate_elements_structure( $elements );
		if ( is_wp_error( $validation ) ) {
			return $this->format_error(
				$validation->get_error_message(),
				'invalid_elements',
				$validation->get_error_data()
			);
		}

		try {
			// Get document using Elementor's Documents Manager
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( ! $document ) {
				return $this->format_error(
					sprintf( 'Could not retrieve document for post ID %d', $post_id ),
					'document_not_found'
				);
			}

			// Check if document is editable by current user
			if ( ! $document->is_editable_by_current_user() ) {
				return $this->format_error(
					'You do not have permission to edit this document',
					'permission_denied'
				);
			}

			// Prepare data for saving
			$data = array(
				'elements' => $elements,
			);

			if ( ! empty( $settings ) ) {
				$data['settings'] = $settings;
			}

			// Save the document
			$saved = $document->save( $data );

			if ( ! $saved ) {
				return $this->format_error(
					'Failed to save document',
					'save_failed'
				);
			}

			// Get autosave information
			$autosave = $document->get_autosave();
			$autosave_info = null;
			if ( $autosave ) {
				$autosave_post = $autosave->get_post();
				$autosave_info = array(
					'id'            => $autosave_post->ID,
					'post_modified' => $autosave_post->post_modified,
				);
			}

			// Refresh post data
			$post = get_post( $post_id );

			$result = array(
				'post_id'        => $post_id,
				'status'         => $post->post_status,
				'post_modified'  => $post->post_modified,
				'autosave'       => $autosave_info,
				'elements_count' => count( $elements ),
			);

			return $this->format_success( $result, 'Document saved successfully' );

		} catch ( \Exception $e ) {
			return $this->format_error(
				sprintf( 'Error saving document: %s', $e->getMessage() ),
				'save_error',
				array( 'exception' => get_class( $e ) )
			);
		}
	}

	/**
	 * Validate elements structure.
	 *
	 * Ensures elements array has valid structure with required fields.
	 * Recursively validates nested elements.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $elements Elements array to validate.
	 * @param string $path Path to current element for error reporting.
	 * @return true|\WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_elements_structure( $elements, $path = '' ) {
		if ( ! is_array( $elements ) ) {
			return new \WP_Error(
				'invalid_elements',
				'Elements must be an array'
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

			// Each element must have elType
			if ( ! isset( $element['elType'] ) ) {
				return new \WP_Error(
					'missing_eltype',
					sprintf( 'Element at %s is missing elType property', $current_path ),
					array( 'element' => $element )
				);
			}

			// Widget elements must have widgetType
			if ( 'widget' === $element['elType'] && ! isset( $element['widgetType'] ) ) {
				return new \WP_Error(
					'missing_widget_type',
					sprintf( 'Widget element at %s is missing widgetType property', $current_path ),
					array( 'element' => $element )
				);
			}

			// RECURSIVE: Validate nested elements
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$nested_validation = $this->validate_elements_structure(
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
