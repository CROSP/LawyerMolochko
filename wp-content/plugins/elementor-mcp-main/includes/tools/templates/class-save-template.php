<?php
/**
 * Save Template Tool
 *
 * MCP tool for creating new Elementor templates.
 * Creates elementor_library posts with proper taxonomy and metadata.
 *
 * @package ElementorMCP\Tools\Templates
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Templates;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Save Template Tool Class
 *
 * Creates new Elementor templates programmatically.
 * Handles post creation, taxonomy assignment, and element data storage.
 *
 * @since 1.0.0
 */
class Save_Template extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'save_template';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Create a new Elementor template with title, type, and content';
	}

	/**
	 * Get input schema.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array JSON schema for input validation.
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'title'         => array(
					'type'        => 'string',
					'description' => 'Template title',
				),
				'type'          => array(
					'type'        => 'string',
					'description' => 'Template type',
					'enum'        => array( 'page', 'section', 'widget', 'header', 'footer', 'single', 'archive' ),
				),
				'content'       => array(
					'type'        => 'array',
					'description' => 'Template content (Elementor elements array)',
					'items'       => array(
						'type' => 'object',
					),
					'default'     => array(),
				),
				'page_settings' => array(
					'type'        => 'object',
					'description' => 'Template page settings',
				),
			),
			'required'   => array( 'title', 'type' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Creates a new Elementor template.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args {
	 *     Input arguments.
	 *
	 *     @type string $title         Template title.
	 *     @type string $type          Template type.
	 *     @type array  $content       Template elements array. Default empty.
	 *     @type array  $page_settings Template page settings. Default empty.
	 * }
	 * @return array Execution result with new template data.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error(
				'Elementor is not active',
				'elementor_inactive'
			);
		}

		// Check permissions
		if ( ! $this->check_permissions( 'publish_posts' ) ) {
			return $this->format_error(
				'Insufficient permissions to create template',
				'permission_denied'
			);
		}

		$title = $args['title'];
		$type = $args['type'];
		$content = isset( $args['content'] ) ? $args['content'] : array();
		$page_settings = isset( $args['page_settings'] ) ? $args['page_settings'] : array();

		try {
			// Validate template type
			$document_types = \Elementor\Plugin::$instance->documents->get_document_types();
			$valid_type = false;

			foreach ( $document_types as $doc_type => $doc_class ) {
				if ( $doc_type === $type ) {
					// Check if this type can be saved to template library
					$cpt = $doc_class::get_property( 'cpt' );
					if ( is_array( $cpt ) && in_array( 'elementor_library', $cpt, true ) ) {
						$valid_type = true;
						break;
					}
				}
			}

			if ( ! $valid_type ) {
				return $this->format_error(
					'Invalid template type: ' . $type,
					'invalid_type'
				);
			}

			// Create the template using Elementor's document system
			$document = \Elementor\Plugin::$instance->documents->create(
				$type,
				array(
					'post_title'  => $title,
					'post_status' => 'publish',
					'post_type'   => 'elementor_library',
				)
			);

			if ( is_wp_error( $document ) ) {
				return $this->format_error(
					'Failed to create template: ' . $document->get_error_message(),
					'create_error'
				);
			}

			// Get the template ID
			$template_id = $document->get_main_id();

			// Save the template content and settings
			if ( ! empty( $content ) || ! empty( $page_settings ) ) {
				$document->save( array(
					'elements' => $content,
					'settings' => $page_settings,
				) );
			}

			// Set the taxonomy term
			wp_set_object_terms( $template_id, $type, 'elementor_library_type' );

			// Get the created template data
			$templates_manager = \Elementor\Plugin::$instance->templates_manager;
			$local_source = $templates_manager->get_source( 'local' );
			$template_data = $local_source->get_item( $template_id );

			return $this->format_success(
				array(
					'template_id' => $template_id,
					'title'       => $template_data['title'],
					'type'        => $template_data['type'],
					'url'         => $template_data['url'],
					'edit_url'    => admin_url( 'post.php?post=' . $template_id . '&action=elementor' ),
					'export_link' => isset( $template_data['export_link'] ) ? $template_data['export_link'] : '',
					'status'      => 'publish',
				),
				'Template created successfully'
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				'Failed to save template: ' . $e->getMessage(),
				'execution_error'
			);
		}
	}
}
