<?php
/**
 * Get Widget Schema Tool
 *
 * Retrieves the complete schema for a specific widget including all controls,
 * defaults, and configuration.
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
 * Get complete widget schema
 */
class Get_Widget_Schema extends Base_Tool {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name        = 'get_widget_schema';
		$this->description = 'Get the complete schema for a specific widget including all controls, defaults, types, and validation rules.';
		$this->input_schema = [
			'type'       => 'object',
			'properties' => [
				'widget_name' => [
					'type'        => 'string',
					'description' => 'The widget name (e.g., "button", "heading", "image").',
				],
				'include_common_controls' => [
					'type'        => 'boolean',
					'description' => 'Whether to include common controls (advanced tab controls). Default: true.',
					'default'     => true,
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

		$widget_name = sanitize_text_field( $args['widget_name'] );
		$include_common = $args['include_common_controls'] ?? true;

		try {
			// Get widget instance.
			$widget = $this->get_widget_instance( $widget_name );
			if ( is_wp_error( $widget ) ) {
				return $this->error( $widget );
			}

			// Get widget stack (controls structure).
			$stack = $widget->get_stack( $include_common );

			// Build schema.
			$schema = [
				'name'       => $widget->get_name(),
				'title'      => $widget->get_title(),
				'icon'       => $widget->get_icon(),
				'categories' => $widget->get_categories(),
				'keywords'   => $widget->get_keywords(),
				'type'       => $widget->get_type(),
			];

			// Extract controls with full details.
			$controls = [];
			if ( isset( $stack['controls'] ) && is_array( $stack['controls'] ) ) {
				foreach ( $stack['controls'] as $control_name => $control ) {
					$controls[ $control_name ] = $this->extract_control_data( $control );
				}
			}
			$schema['controls'] = $controls;

			// Extract tabs structure.
			$tabs = [];
			if ( isset( $stack['tabs'] ) && is_array( $stack['tabs'] ) ) {
				$tabs = $stack['tabs'];
			}
			$schema['tabs'] = $tabs;

			// Get default data.
			$schema['defaults'] = $this->extract_default_values( $controls );

			// Get sections structure.
			$schema['sections'] = $this->extract_sections( $controls );

			// Additional widget info.
			if ( method_exists( $widget, 'get_custom_help_url' ) ) {
				$help_url = $widget->get_custom_help_url();
				if ( ! empty( $help_url ) ) {
					$schema['help_url'] = $help_url;
				}
			}

			if ( method_exists( $widget, 'has_widget_inner_wrapper' ) ) {
				$schema['has_inner_wrapper'] = $widget->has_widget_inner_wrapper();
			}

			// Note: is_dynamic_content() is a protected method and cannot be called directly
			// Skip this property as it requires reflection or internal access

			return $this->success(
				$schema,
				sprintf(
					/* translators: %s: widget name */
					__( 'Retrieved schema for widget: %s', 'elementor-mcp' ),
					$widget_name
				)
			);

		} catch ( \Exception $e ) {
			return $this->error(
				$e->getMessage(),
				'widget_schema_error'
			);
		}
	}

	/**
	 * Extract default values from controls
	 *
	 * @param array $controls Widget controls.
	 * @return array Default values.
	 */
	private function extract_default_values( $controls ) {
		$defaults = [];

		foreach ( $controls as $control_name => $control ) {
			if ( isset( $control['default'] ) ) {
				$defaults[ $control_name ] = $control['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Extract sections structure from controls
	 *
	 * @param array $controls Widget controls.
	 * @return array Sections structure.
	 */
	private function extract_sections( $controls ) {
		$sections = [];
		$current_section = null;

		foreach ( $controls as $control_name => $control ) {
			$control_type = $control['type'] ?? '';

			// Section control.
			if ( 'section' === $control_type ) {
				$current_section = $control_name;
				$sections[ $current_section ] = [
					'label'    => $control['label'] ?? '',
					'tab'      => $control['tab'] ?? 'content',
					'controls' => [],
				];
			} elseif ( $current_section && ! empty( $control_type ) ) {
				// Regular control - add to current section.
				if ( ! in_array( $control_type, [ 'section', 'tab' ], true ) ) {
					$sections[ $current_section ]['controls'][] = $control_name;
				}
			}
		}

		return $sections;
	}
}
