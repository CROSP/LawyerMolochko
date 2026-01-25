<?php
/**
 * Create Section Tool
 *
 * Creates a complete Elementor section structure with columns and widgets.
 * This tool handles the hierarchical structure automatically.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Widgets;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Section Tool Class
 *
 * Creates a properly structured Elementor section with columns and widgets.
 * Handles ID generation and validates the nested structure.
 *
 * @since 1.0.0
 */
class Create_Section extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'create_section';
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
		return 'Creates a complete Elementor section structure with columns and widgets. Handles the proper hierarchy (Section > Column > Widget) automatically with generated IDs.';
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
				'section_settings' => array(
					'type'        => 'object',
					'description' => 'Optional settings for the section (padding, background_color, etc.)',
					'default'     => new \stdClass(),
				),
				'columns' => array(
					'type'        => 'array',
					'description' => 'Array of column definitions. Each column has column_size (percentage) and widgets array.',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'column_size' => array(
								'type'        => 'integer',
								'description' => 'Column width percentage (columns should add up to 100)',
								'minimum'     => 1,
								'maximum'     => 100,
								'default'     => 100,
							),
							'column_settings' => array(
								'type'        => 'object',
								'description' => 'Optional settings for the column',
								'default'     => new \stdClass(),
							),
							'widgets' => array(
								'type'        => 'array',
								'description' => 'Array of widgets to place in this column',
								'items'       => array(
									'type'       => 'object',
									'properties' => array(
										'widget_type' => array(
											'type'        => 'string',
											'description' => 'The widget type (heading, text-editor, button, image, etc.)',
										),
										'settings' => array(
											'type'        => 'object',
											'description' => 'Widget settings',
											'default'     => new \stdClass(),
										),
									),
									'required' => array( 'widget_type' ),
								),
							),
						),
						'required' => array( 'widgets' ),
					),
				),
				'use_container' => array(
					'type'        => 'boolean',
					'description' => 'Use modern container layout instead of section/column. Default: false',
					'default'     => false,
				),
			),
			'required'   => array( 'columns' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Creates a complete section structure.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
		}

		$section_settings = isset( $args['section_settings'] ) ? $args['section_settings'] : array();
		$columns = isset( $args['columns'] ) ? $args['columns'] : array();
		$use_container = isset( $args['use_container'] ) ? (bool) $args['use_container'] : false;

		if ( empty( $columns ) ) {
			return $this->format_error(
				'At least one column is required',
				'invalid_columns'
			);
		}

		try {
			if ( $use_container ) {
				$section = $this->create_container_structure( $section_settings, $columns );
			} else {
				$section = $this->create_section_structure( $section_settings, $columns );
			}

			// Count widgets for the message
			$widget_count = 0;
			foreach ( $columns as $column ) {
				if ( isset( $column['widgets'] ) ) {
					$widget_count += count( $column['widgets'] );
				}
			}

			return $this->format_success(
				array(
					'section'      => $section,
					'column_count' => count( $columns ),
					'widget_count' => $widget_count,
					'layout_type'  => $use_container ? 'container' : 'section',
				),
				sprintf(
					'Created %s with %d column(s) and %d widget(s)',
					$use_container ? 'container' : 'section',
					count( $columns ),
					$widget_count
				)
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				sprintf( 'Error creating section: %s', $e->getMessage() ),
				'section_creation_error'
			);
		}
	}

	/**
	 * Create classic section/column structure.
	 *
	 * @param array $section_settings Section settings.
	 * @param array $columns Column definitions.
	 * @return array Section element.
	 */
	private function create_section_structure( $section_settings, $columns ) {
		$section_id = $this->generate_element_id();

		$column_elements = array();
		foreach ( $columns as $column_def ) {
			$column_elements[] = $this->create_column( $column_def );
		}

		return array(
			'id'       => $section_id,
			'elType'   => 'section',
			'settings' => ! empty( $section_settings ) ? $section_settings : new \stdClass(),
			'elements' => $column_elements,
		);
	}

	/**
	 * Create modern container structure.
	 *
	 * @param array $container_settings Container settings.
	 * @param array $columns Column definitions (widgets go directly in container).
	 * @return array Container element.
	 */
	private function create_container_structure( $container_settings, $columns ) {
		$container_id = $this->generate_element_id();

		// For containers, we flatten widgets from all "columns" into direct children
		// or create nested containers for multi-column layouts
		$elements = array();

		if ( count( $columns ) === 1 ) {
			// Single column - put widgets directly in container
			foreach ( $columns[0]['widgets'] as $widget_def ) {
				$elements[] = $this->create_widget( $widget_def );
			}
		} else {
			// Multiple columns - create nested containers
			foreach ( $columns as $column_def ) {
				$nested_container = array(
					'id'       => $this->generate_element_id(),
					'elType'   => 'container',
					'settings' => array(
						'flex_grow' => isset( $column_def['column_size'] ) ? (string) $column_def['column_size'] : '1',
					),
					'elements' => array(),
				);

				if ( isset( $column_def['column_settings'] ) && ! empty( $column_def['column_settings'] ) ) {
					$nested_container['settings'] = array_merge(
						$nested_container['settings'],
						$column_def['column_settings']
					);
				}

				foreach ( $column_def['widgets'] as $widget_def ) {
					$nested_container['elements'][] = $this->create_widget( $widget_def );
				}

				$elements[] = $nested_container;
			}

			// Set parent container to row layout
			if ( ! isset( $container_settings['flex_direction'] ) ) {
				$container_settings['flex_direction'] = 'row';
			}
		}

		return array(
			'id'       => $container_id,
			'elType'   => 'container',
			'settings' => ! empty( $container_settings ) ? $container_settings : new \stdClass(),
			'elements' => $elements,
		);
	}

	/**
	 * Create a column element.
	 *
	 * @param array $column_def Column definition with column_size, column_settings, widgets.
	 * @return array Column element.
	 */
	private function create_column( $column_def ) {
		$column_id = $this->generate_element_id();
		$column_size = isset( $column_def['column_size'] ) ? $column_def['column_size'] : 100;
		$column_settings = isset( $column_def['column_settings'] ) ? $column_def['column_settings'] : array();

		// Add column size to settings
		$column_settings['_column_size'] = $column_size;

		$widget_elements = array();
		if ( isset( $column_def['widgets'] ) && is_array( $column_def['widgets'] ) ) {
			foreach ( $column_def['widgets'] as $widget_def ) {
				$widget_elements[] = $this->create_widget( $widget_def );
			}
		}

		return array(
			'id'       => $column_id,
			'elType'   => 'column',
			'settings' => $column_settings,
			'elements' => $widget_elements,
		);
	}

	/**
	 * Create a widget element.
	 *
	 * @param array $widget_def Widget definition with widget_type and settings.
	 * @return array Widget element.
	 */
	private function create_widget( $widget_def ) {
		$widget_id = $this->generate_element_id();
		$widget_type = $widget_def['widget_type'];
		$settings = isset( $widget_def['settings'] ) ? $widget_def['settings'] : array();

		return array(
			'id'         => $widget_id,
			'elType'     => 'widget',
			'widgetType' => $widget_type,
			'settings'   => ! empty( $settings ) ? $settings : new \stdClass(),
			'elements'   => array(),
		);
	}

	/**
	 * Generate a unique element ID.
	 *
	 * @return string 8-character hexadecimal ID.
	 */
	private function generate_element_id() {
		return substr( md5( uniqid( mt_rand(), true ) ), 0, 8 );
	}
}
