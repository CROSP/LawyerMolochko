<?php
/**
 * Export Template Tool
 *
 * MCP tool for exporting Elementor templates to JSON format.
 * Returns template data in Elementor's standard export format.
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
 * Export Template Tool Class
 *
 * Exports local Elementor templates to JSON format compatible with
 * Elementor's template import/export system.
 *
 * @since 1.0.0
 */
class Export_Template extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'export_template';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Export Elementor template to JSON format compatible with Elementor import';
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
					'type'        => 'integer',
					'description' => 'Local template ID to export',
				),
			),
			'required'   => array( 'template_id' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Exports template to Elementor JSON format.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args {
	 *     Input arguments.
	 *
	 *     @type int $template_id Local template ID to export.
	 * }
	 * @return array Execution result with exported template data.
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
				'Insufficient permissions to export template',
				'permission_denied'
			);
		}

		$template_id = $args['template_id'];

		try {
			// Verify template exists and is an Elementor template
			$post = get_post( $template_id );

			if ( ! $post ) {
				return $this->format_error(
					'Template not found',
					'template_not_found'
				);
			}

			if ( 'elementor_library' !== $post->post_type ) {
				return $this->format_error(
					'Post is not an Elementor template',
					'invalid_template_type'
				);
			}

			// Check permissions for this specific template
			if ( ! current_user_can( 'edit_post', $template_id ) ) {
				return $this->format_error(
					'Insufficient permissions to export this template',
					'permission_denied'
				);
			}

			// Get the document
			$document = \Elementor\Plugin::$instance->documents->get( $template_id );

			if ( ! $document ) {
				return $this->format_error(
					'Failed to load template document',
					'document_error'
				);
			}

			// Get export data
			$template_data = $document->get_export_data();

			if ( empty( $template_data['content'] ) ) {
				return $this->format_error(
					'Template is empty',
					'empty_template'
				);
			}

			// Get page settings
			$page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );
			$page = $page_settings_manager->get_model( $template_id );

			// Prepare export data in Elementor format
			$export_data = array(
				'content'       => $template_data['content'],
				'page_settings' => $template_data['settings'],
				'version'       => \Elementor\DB::DB_VERSION,
				'title'         => $document->get_main_post()->post_title,
				'type'          => \Elementor\TemplateLibrary\Source_Local::get_template_type( $template_id ),
			);

			// Generate filename
			$filename = 'elementor-' . $template_id . '-' . gmdate( 'Y-m-d' ) . '.json';

			return $this->format_success(
				array(
					'template_id' => $template_id,
					'title'       => $export_data['title'],
					'type'        => $export_data['type'],
					'filename'    => $filename,
					'export_data' => $export_data,
					'json'        => wp_json_encode( $export_data ),
					'version'     => $export_data['version'],
				),
				'Template exported successfully'
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				'Failed to export template: ' . $e->getMessage(),
				'execution_error'
			);
		}
	}
}
