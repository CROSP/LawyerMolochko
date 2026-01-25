<?php
/**
 * Get Template Tool
 *
 * MCP tool for retrieving complete Elementor template data.
 * Returns template elements, settings, and metadata.
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
 * Get Template Tool Class
 *
 * Retrieves complete template data including elements structure,
 * page settings, and metadata from local or remote sources.
 *
 * @since 1.0.0
 */
class Get_Template extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_template';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Get complete Elementor template data including elements, settings, and metadata';
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
				'template_id' => array(
					'type'        => 'string',
					'description' => 'Template ID to retrieve (as string)',
				),
				'source'      => array(
					'type'        => 'string',
					'description' => 'Template source',
					'enum'        => array( 'local', 'remote' ),
					'default'     => 'local',
				),
			),
			'required'   => array( 'template_id' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves complete template data from the specified source.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args {
	 *     Input arguments.
	 *
	 *     @type int|string $template_id Template ID.
	 *     @type string     $source      Template source. Default 'local'.
	 * }
	 * @return array Execution result with template data.
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
				'Insufficient permissions to get template',
				'permission_denied'
			);
		}

		$template_id = $args['template_id'];
		$source = isset( $args['source'] ) ? $args['source'] : 'local';

		try {
			// Get template manager
			$templates_manager = \Elementor\Plugin::$instance->templates_manager;

			// Get the source
			$source_instance = $templates_manager->get_source( $source );

			if ( ! $source_instance ) {
				return $this->format_error(
					'Invalid template source: ' . $source,
					'invalid_source'
				);
			}

			// Get template item (metadata)
			$template_item = $source_instance->get_item( $template_id );

			if ( is_wp_error( $template_item ) ) {
				return $this->format_error(
					'Failed to get template: ' . $template_item->get_error_message(),
					'template_error'
				);
			}

			// Get template data (content and settings)
			$template_data = $source_instance->get_data( array(
				'template_id'        => $template_id,
				'with_page_settings' => true,
			) );

			if ( is_wp_error( $template_data ) ) {
				return $this->format_error(
					'Failed to get template data: ' . $template_data->get_error_message(),
					'template_data_error'
				);
			}

			// Combine metadata and data
			$result = array(
				'template_id'   => $template_item['template_id'],
				'title'         => $template_item['title'],
				'type'          => $template_item['type'],
				'source'        => $template_item['source'],
				'content'       => isset( $template_data['content'] ) ? $template_data['content'] : array(),
				'page_settings' => isset( $template_data['page_settings'] ) ? $template_data['page_settings'] : array(),
				'metadata'      => array(
					'thumbnail' => isset( $template_item['thumbnail'] ) ? $template_item['thumbnail'] : '',
					'date'      => isset( $template_item['date'] ) ? $template_item['date'] : '',
					'author'    => isset( $template_item['author'] ) ? $template_item['author'] : '',
					'url'       => isset( $template_item['url'] ) ? $template_item['url'] : '',
				),
			);

			return $this->format_success(
				$result,
				'Template retrieved successfully'
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				'Failed to get template: ' . $e->getMessage(),
				'execution_error'
			);
		}
	}
}
