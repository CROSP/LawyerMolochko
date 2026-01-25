<?php
/**
 * Import Template Tool
 *
 * MCP tool for importing Elementor templates from JSON data or remote sources.
 * Handles image imports and creates new local templates.
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
 * Import Template Tool Class
 *
 * Imports templates from JSON data or remote template IDs.
 * Processes template data, handles image imports, and creates local templates.
 *
 * @since 1.0.0
 */
class Import_Template extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'import_template';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Import Elementor template from JSON data or remote template ID';
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
				'template_data' => array(
					'type'        => 'object',
					'description' => 'Template data in Elementor JSON format (content, page_settings, title, type). Either template_data or template_id is required.',
					'properties'  => array(
						'content'       => array(
							'type'        => 'array',
							'description' => 'Template elements array',
							'items'       => array(
								'type' => 'object',
							),
						),
						'page_settings' => array(
							'type'        => 'object',
							'description' => 'Template page settings',
						),
						'title'         => array(
							'type'        => 'string',
							'description' => 'Template title',
						),
						'type'          => array(
							'type'        => 'string',
							'description' => 'Template type',
						),
					),
				),
				'template_id'   => array(
					'type'        => 'string',
					'description' => 'Remote template ID to import. Either template_data or template_id is required.',
				),
				'import_images' => array(
					'type'        => 'boolean',
					'description' => 'Whether to import images from remote URLs',
					'default'     => true,
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Imports template from JSON data or remote template ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args {
	 *     Input arguments.
	 *
	 *     @type array      $template_data Template JSON data (alternative to template_id).
	 *     @type int|string $template_id   Remote template ID (alternative to template_data).
	 *     @type bool       $import_images Whether to import images. Default true.
	 * }
	 * @return array Execution result with imported template data.
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
		if ( ! $this->check_permissions( 'edit_posts' ) ) {
			return $this->format_error(
				'Insufficient permissions to import template',
				'permission_denied'
			);
		}

		$import_images = isset( $args['import_images'] ) ? (bool) $args['import_images'] : true;

		try {
			$templates_manager = \Elementor\Plugin::$instance->templates_manager;
			$local_source = $templates_manager->get_source( 'local' );

			if ( ! $local_source ) {
				return $this->format_error(
					'Local template source not available',
					'source_error'
				);
			}

			// Determine import method: from JSON data or remote template ID
			if ( isset( $args['template_data'] ) ) {
				// Import from JSON data
				$template_data = $args['template_data'];

				// Validate required fields
				if ( empty( $template_data['content'] ) || empty( $template_data['title'] ) || empty( $template_data['type'] ) ) {
					return $this->format_error(
						'Template data must include content, title, and type',
						'invalid_template_data'
					);
				}

				// Process content for import (handle images if needed)
				if ( $import_images ) {
					$template_data['content'] = $this->process_import_images( $template_data['content'] );
				}

				// Prepare data for save
				$save_data = array(
					'content'       => $template_data['content'],
					'title'         => $template_data['title'],
					'type'          => $template_data['type'],
					'page_settings' => isset( $template_data['page_settings'] ) ? $template_data['page_settings'] : array(),
				);

			} elseif ( isset( $args['template_id'] ) ) {
				// Import from remote template
				$remote_template_id = $args['template_id'];
				$remote_source = $templates_manager->get_source( 'remote' );

				if ( ! $remote_source ) {
					return $this->format_error(
						'Remote template source not available',
						'source_error'
					);
				}

				// Get remote template data
				$remote_data = $remote_source->get_data( array(
					'template_id'        => $remote_template_id,
					'with_page_settings' => true,
				) );

				if ( is_wp_error( $remote_data ) ) {
					return $this->format_error(
						'Failed to get remote template: ' . $remote_data->get_error_message(),
						'remote_error'
					);
				}

				// Get remote template metadata
				$remote_item = $remote_source->get_item( $remote_template_id );

				// Prepare data for save
				$save_data = array(
					'content'       => $remote_data['content'],
					'title'         => $remote_item['title'],
					'type'          => $remote_item['type'],
					'page_settings' => isset( $remote_data['page_settings'] ) ? $remote_data['page_settings'] : array(),
				);

			} else {
				return $this->format_error(
					'Either template_data or template_id must be provided',
					'missing_input'
				);
			}

			// Save the template
			$template_id = $local_source->save_item( $save_data );

			if ( is_wp_error( $template_id ) ) {
				return $this->format_error(
					'Failed to save template: ' . $template_id->get_error_message(),
					'save_error'
				);
			}

			// Get the saved template data
			$imported_template = $local_source->get_item( $template_id );

			return $this->format_success(
				array(
					'template_id'   => $template_id,
					'title'         => $imported_template['title'],
					'type'          => $imported_template['type'],
					'url'           => $imported_template['url'],
					'export_link'   => isset( $imported_template['export_link'] ) ? $imported_template['export_link'] : '',
					'import_method' => isset( $args['template_data'] ) ? 'json_data' : 'remote_id',
				),
				'Template imported successfully'
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				'Failed to import template: ' . $e->getMessage(),
				'execution_error'
			);
		}
	}

	/**
	 * Process template content to import images.
	 *
	 * Iterates through template content and imports remote images.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param array $content Template content array.
	 * @return array Processed content with local image URLs.
	 */
	private function process_import_images( $content ) {
		// This uses Elementor's Import_Images class
		$import_images = \Elementor\Plugin::$instance->templates_manager->get_import_images_instance();

		return \Elementor\Plugin::$instance->db->iterate_data( $content, function( $element ) use ( $import_images ) {
			// Process image controls
			foreach ( array( 'image', 'background_image', 'background_video' ) as $control_name ) {
				if ( isset( $element['settings'][ $control_name ] ) && is_array( $element['settings'][ $control_name ] ) ) {
					$attachment = $element['settings'][ $control_name ];
					if ( ! empty( $attachment['url'] ) ) {
						$imported = $import_images->import( $attachment );
						if ( $imported ) {
							$element['settings'][ $control_name ] = $imported;
						}
					}
				}
			}

			return $element;
		} );
	}
}
