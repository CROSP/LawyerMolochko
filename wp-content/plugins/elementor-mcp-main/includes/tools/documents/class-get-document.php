<?php
/**
 * Get Document Tool
 *
 * Retrieves full document data including elements array, settings, and metadata.
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
 * Get Document Tool Class
 *
 * Retrieves a complete Elementor document with all its data, settings, and metadata.
 * Uses Plugin::$instance->documents->get($post_id) to fetch the document.
 *
 * @since 1.0.0
 */
class Get_Document extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_document';
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
		return 'Retrieves full Elementor document data including elements array, settings, metadata, and post information for a given post ID.';
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
					'description' => 'The WordPress post ID to retrieve the document for',
					'minimum'     => 1,
				),
			),
			'required'   => array( 'post_id' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves the full document data using Elementor's Documents Manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments containing post_id.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
		}

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
				'You do not have permission to access this document',
				'permission_denied'
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

			// Get elements data using Document::get_elements_data()
			$elements_data = $document->get_elements_data();

			// Get document settings
			$settings = $document->get_settings();

			// Get post data
			$post_data = array(
				'ID'            => $post->ID,
				'post_title'    => $post->post_title,
				'post_status'   => $post->post_status,
				'post_type'     => $post->post_type,
				'post_modified' => $post->post_modified,
				'post_author'   => $post->post_author,
			);

			// Get metadata
			$metadata = array(
				'template_type'       => $document->get_template_type(),
				'edit_mode'           => get_post_meta( $post_id, '_elementor_edit_mode', true ),
				'version'             => get_post_meta( $post_id, '_elementor_version', true ),
				'css_status'          => get_post_meta( $post_id, '_elementor_css', true ),
				'is_built_with_elementor' => $document->is_built_with_elementor(),
			);

			// Get autosave information
			$autosave = $document->get_autosave();
			$autosave_info = null;
			if ( $autosave ) {
				$autosave_post = $autosave->get_post();
				$autosave_info = array(
					'id'            => $autosave_post->ID,
					'post_modified' => $autosave_post->post_modified,
					'post_author'   => $autosave_post->post_author,
				);
			}

			$result = array(
				'post_id'      => $post_id,
				'post'         => $post_data,
				'elements'     => $elements_data,
				'settings'     => $settings,
				'metadata'     => $metadata,
				'document_type' => $document->get_name(),
				'autosave'     => $autosave_info,
				'edit_url'     => $document->get_edit_url(),
				'preview_url'  => $document->get_preview_url(),
			);

			return $this->format_success( $result, 'Document retrieved successfully' );

		} catch ( \Exception $e ) {
			return $this->format_error(
				sprintf( 'Error retrieving document: %s', $e->getMessage() ),
				'retrieval_error',
				array( 'exception' => get_class( $e ) )
			);
		}
	}
}
