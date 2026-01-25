<?php
/**
 * Get Widget Controls Tool
 *
 * Retrieves detailed control information for a specific widget.
 * More focused than get_widget_schema - returns just the controls
 * with all their properties.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Widgets;

use ElementorMCP\Base_Tool;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get widget controls with detailed information
 */
class Get_Widget_Controls extends Base_Tool {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name        = 'get_widget_controls';
		$this->description = 'Get detailed control (field) information for a widget including type, label, default, options, conditions, and selectors.';
		$this->input_schema = [
			'type'       => 'object',
			'properties' => [
				'widget_name' => [
					'type'        => 'string',
					'description' => 'The widget name (e.g., "button", "heading", "image").',
				],
				'control_name' => [
					'type'        => 'string',
					'description' => 'Optional: Get a specific control by name. If omitted, returns all controls.',
				],
				'include_common' => [
					'type'        => 'boolean',
					'description' => 'Whether to include common controls (advanced tab). Default: true.',
					'default'     => true,
				],
				'tab' => [
					'type'        => 'string',
					'description' => 'Filter controls by tab (content, style, advanced). If omitted, returns all tabs.',
				],
			],
			'required' => [ 'widget_name' ],
		];
	}

	/**
	 * Execute the tool
	 *
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args = [] ) {
		$verify = $this->verify_elementor();
		if ( is_wp_error( $verify ) ) {
			return $this->error( $verify );
		}

		// Validate required arguments.
		$validation = $this->validate_required_args( $args, [ 'widget_name' ] );
		if ( is_wp_error( $validation ) ) {
			return $this->error( $validation );
		}

		$widget_name   = sanitize_text_field( $args['widget_name'] );
		$control_name  = isset( $args['control_name'] ) ? sanitize_text_field( $args['control_name'] ) : '';
		$include_common = $args['include_common'] ?? true;
		$tab_filter    = isset( $args['tab'] ) ? sanitize_text_field( $args['tab'] ) : '';

		try {
			// Get widget instance.
			$widget = $this->get_widget_instance( $widget_name );
			if ( is_wp_error( $widget ) ) {
				return $this->error( $widget );
			}

			// Get widget stack.
			$stack = $widget->get_stack( $include_common );
			$controls = $stack['controls'] ?? [];

			if ( empty( $controls ) ) {
				return $this->success(
					[],
					__( 'No controls found for this widget.', 'elementor-mcp' )
				);
			}

			// If specific control requested.
			if ( ! empty( $control_name ) ) {
				if ( ! isset( $controls[ $control_name ] ) ) {
					return $this->error(
						sprintf(
							/* translators: 1: control name, 2: widget name */
							__( 'Control "%1$s" not found in widget "%2$s".', 'elementor-mcp' ),
							$control_name,
							$widget_name
						),
						'control_not_found'
					);
				}

				$control_data = $this->get_detailed_control_info( $controls[ $control_name ], $control_name );

				return $this->success(
					$control_data,
					sprintf(
						/* translators: %s: control name */
						__( 'Retrieved control: %s', 'elementor-mcp' ),
						$control_name
					)
				);
			}

			// Return all controls (with optional tab filter).
			$controls_data = [];

			foreach ( $controls as $key => $control ) {
				// Apply tab filter if specified.
				if ( ! empty( $tab_filter ) ) {
					$control_tab = $control['tab'] ?? '';
					if ( $control_tab !== $tab_filter ) {
						continue;
					}
				}

				$controls_data[ $key ] = $this->get_detailed_control_info( $control, $key );
			}

			// Organize by sections.
			$organized = $this->organize_controls_by_section( $controls_data );

			return $this->success(
				[
					'controls'          => $controls_data,
					'organized_sections' => $organized,
					'total_controls'    => count( $controls_data ),
				],
				sprintf(
					/* translators: 1: widget name, 2: number of controls */
					__( 'Retrieved %2$d controls for widget "%1$s".', 'elementor-mcp' ),
					$widget_name,
					count( $controls_data )
				)
			);

		} catch ( \Exception $e ) {
			return $this->error(
				$e->getMessage(),
				'widget_controls_error'
			);
		}
	}

	/**
	 * Get detailed control information
	 *
	 * @param array  $control Control array.
	 * @param string $control_key Control key.
	 * @return array Detailed control info.
	 */
	private function get_detailed_control_info( $control, $control_key ) {
		$info = [
			'name'  => $control_key,
			'type'  => $control['type'] ?? '',
			'label' => $control['label'] ?? '',
		];

		// Add all relevant properties.
		$properties = [
			'default',
			'placeholder',
			'description',
			'separator',
			'show_label',
			'label_block',
			'dynamic',
			'ai',
			'responsive',
			'selectors',
			'condition',
			'conditions',
			'tab',
			'section',
			'options',
			'min',
			'max',
			'step',
			'rows',
			'show_external',
			'size_units',
			'default_unit',
			'range',
			'toggle',
			'return_value',
			'frontend_available',
			'render_type',
			'prefix_class',
			'title',
			'object_type',
			'query',
			'multiple',
			'label_on',
			'label_off',
		];

		foreach ( $properties as $property ) {
			if ( isset( $control[ $property ] ) ) {
				$info[ $property ] = $control[ $property ];
			}
		}

		// Add control type metadata.
		$info['metadata'] = $this->get_control_type_metadata( $info['type'] );

		return $info;
	}

	/**
	 * Get metadata about control type
	 *
	 * @param string $control_type Control type.
	 * @return array Control type metadata.
	 */
	private function get_control_type_metadata( $control_type ) {
		$metadata = [
			'type'        => $control_type,
			'is_input'    => false,
			'is_group'    => false,
			'is_section'  => false,
			'value_type'  => 'string',
		];

		// Define control categories and characteristics.
		$input_controls = [ 'text', 'textarea', 'number', 'wysiwyg', 'code', 'hidden', 'date_time' ];
		$choice_controls = [ 'select', 'select2', 'choose', 'switcher' ];
		$media_controls = [ 'media', 'gallery', 'icon', 'icons' ];
		$dimension_controls = [ 'slider', 'dimensions', 'color' ];
		$group_controls = [ 'typography', 'text_shadow', 'box_shadow', 'border', 'background', 'image_size' ];
		$section_controls = [ 'section', 'tab', 'divider', 'heading', 'raw_html' ];

		if ( in_array( $control_type, $input_controls, true ) ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'text' === $control_type || 'textarea' === $control_type ? 'string' : 'mixed';
		} elseif ( in_array( $control_type, $choice_controls, true ) ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'string';
		} elseif ( in_array( $control_type, $media_controls, true ) ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'object';
		} elseif ( in_array( $control_type, $dimension_controls, true ) ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'slider' === $control_type ? 'object' : 'string';
		} elseif ( in_array( $control_type, $group_controls, true ) ) {
			$metadata['is_group'] = true;
			$metadata['value_type'] = 'object';
		} elseif ( in_array( $control_type, $section_controls, true ) ) {
			$metadata['is_section'] = true;
			$metadata['value_type'] = 'none';
		}

		// Special cases.
		if ( 'url' === $control_type ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'object';
		} elseif ( 'repeater' === $control_type ) {
			$metadata['is_input'] = true;
			$metadata['value_type'] = 'array';
		}

		return $metadata;
	}

	/**
	 * Organize controls by section
	 *
	 * @param array $controls Controls array.
	 * @return array Organized by sections.
	 */
	private function organize_controls_by_section( $controls ) {
		$sections = [];
		$current_section = 'default';
		$current_tab = 'content';

		foreach ( $controls as $control_name => $control ) {
			$control_type = $control['type'] ?? '';

			if ( 'section' === $control_type ) {
				$current_section = $control_name;
				$current_tab = $control['tab'] ?? 'content';

				$sections[ $current_section ] = [
					'label'    => $control['label'] ?? '',
					'tab'      => $current_tab,
					'controls' => [],
				];
			} elseif ( isset( $sections[ $current_section ] ) && ! in_array( $control_type, [ 'tab', 'section' ], true ) ) {
				$sections[ $current_section ]['controls'][ $control_name ] = $control;
			}
		}

		return $sections;
	}
}
