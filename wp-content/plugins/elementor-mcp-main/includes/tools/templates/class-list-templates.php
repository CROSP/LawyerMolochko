<?php
/**
 * List Templates Tool
 *
 * MCP tool for listing Elementor templates from local or remote sources.
 * Retrieves templates with filtering by type and source.
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
 * List Templates Tool Class
 *
 * Provides MCP interface to list Elementor templates from the template library.
 * Supports filtering by type (page, section, widget) and source (local, remote).
 *
 * @since 1.0.0
 */
class List_Templates extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'list_templates';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'List Elementor templates from local or remote library with filtering by type and source';
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
				'type'   => array(
					'type'        => 'string',
					'description' => 'Template type to filter by',
					'enum'        => array( 'page', 'section', 'widget', 'all' ),
					'default'     => 'all',
				),
				'source' => array(
					'type'        => 'string',
					'description' => 'Template source',
					'enum'        => array( 'local', 'remote', 'all' ),
					'default'     => 'all',
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Lists templates from the specified source with optional type filtering.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args {
	 *     Input arguments.
	 *
	 *     @type string $type   Template type filter. Default 'all'.
	 *     @type string $source Template source. Default 'all'.
	 * }
	 * @return array Execution result with templates list.
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
				'Insufficient permissions to list templates',
				'permission_denied'
			);
		}

		// Set defaults
		$type = isset( $args['type'] ) ? $args['type'] : 'all';
		$source = isset( $args['source'] ) ? $args['source'] : 'all';

		try {
			// Get template manager
			$templates_manager = \Elementor\Plugin::$instance->templates_manager;

			// Determine which sources to query
			$filter_sources = array();
			if ( 'all' !== $source ) {
				$filter_sources[] = $source;
			}

			// Get templates
			$templates = $templates_manager->get_templates( $filter_sources );

			// Filter by type if specified
			if ( 'all' !== $type ) {
				$templates = array_filter( $templates, function( $template ) use ( $type ) {
					return isset( $template['type'] ) && $template['type'] === $type;
				} );
			}

			// Format template data for output
			$formatted_templates = array();
			foreach ( $templates as $template ) {
				$formatted_templates[] = array(
					'template_id' => $template['template_id'],
					'title'       => $template['title'],
					'type'        => $template['type'],
					'source'      => $template['source'],
					'thumbnail'   => isset( $template['thumbnail'] ) ? $template['thumbnail'] : '',
					'date'        => isset( $template['date'] ) ? $template['date'] : '',
					'author'      => isset( $template['author'] ) ? $template['author'] : '',
					'url'         => isset( $template['url'] ) ? $template['url'] : '',
				);
			}

			return $this->format_success(
				array(
					'templates' => $formatted_templates,
					'count'     => count( $formatted_templates ),
					'filters'   => array(
						'type'   => $type,
						'source' => $source,
					),
				),
				sprintf( 'Found %d templates', count( $formatted_templates ) )
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				'Failed to list templates: ' . $e->getMessage(),
				'execution_error'
			);
		}
	}
}
