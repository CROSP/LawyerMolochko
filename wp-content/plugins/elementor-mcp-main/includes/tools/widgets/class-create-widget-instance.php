<?php
/**
 * Create Widget Instance Tool
 *
 * Creates a proper Elementor widget instance JSON structure that can be
 * inserted into a document's elements array.
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
 * Create widget instance with proper JSON structure
 */
class Create_Widget_Instance extends Base_Tool {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name        = 'create_widget_instance';
		$this->description = 'Create a properly formatted Elementor widget instance that can be inserted into a page or section.';
		$this->input_schema = [
			'type'       => 'object',
			'properties' => [
				'widget_type' => [
					'type'        => 'string',
					'description' => 'The widget type (e.g., "button", "heading", "image").',
				],
				'settings' => [
					'type'        => 'object',
					'description' => 'Widget settings as key-value pairs. Use get_widget_schema to see available settings.',
				],
				'validate_settings' => [
					'type'        => 'boolean',
					'description' => 'Whether to validate settings against widget schema. Default: true.',
					'default'     => true,
				],
			],
			'required' => [ 'widget_type' ],
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
		$validation = $this->validate_required_args( $args, [ 'widget_type' ] );
		if ( is_wp_error( $validation ) ) {
			return $this->error( $validation );
		}

		$widget_type = sanitize_text_field( $args['widget_type'] );
		$settings    = $args['settings'] ?? [];
		$validate    = $args['validate_settings'] ?? true;

		try {
			// Get widget instance to verify it exists.
			$widget = $this->get_widget_instance( $widget_type );
			if ( is_wp_error( $widget ) ) {
				return $this->error( $widget );
			}

			// Validate settings if requested.
			if ( $validate ) {
				$validation_result = $this->validate_widget_settings( $widget, $settings );
				if ( is_wp_error( $validation_result ) ) {
					return $this->error( $validation_result );
				}
			}

			// Sanitize settings.
			$sanitized_settings = $this->sanitize_settings( $settings );

			// Generate unique ID for this widget instance.
			$element_id = $this->generate_element_id();

			// Create the widget instance structure.
			// This follows the exact format Elementor uses in _elementor_data.
			$widget_instance = [
				'id'         => $element_id,
				'elType'     => 'widget',
				'widgetType' => $widget_type,
				'settings'   => $sanitized_settings,
			];

			// Add metadata about the widget.
			$metadata = [
				'widget_name'  => $widget->get_name(),
				'widget_title' => $widget->get_title(),
				'categories'   => $widget->get_categories(),
			];

			return $this->success(
				[
					'element'  => $widget_instance,
					'metadata' => $metadata,
				],
				sprintf(
					/* translators: 1: widget type, 2: element ID */
					__( 'Created widget instance of type "%1$s" with ID: %2$s', 'elementor-mcp' ),
					$widget_type,
					$element_id
				)
			);

		} catch ( \Exception $e ) {
			return $this->error(
				$e->getMessage(),
				'widget_instance_error'
			);
		}
	}

	/**
	 * Validate widget settings against schema
	 *
	 * @param \Elementor\Widget_Base $widget Widget instance.
	 * @param array                  $settings Settings to validate.
	 * @return bool|\WP_Error True if valid, WP_Error otherwise.
	 */
	private function validate_widget_settings( $widget, $settings ) {
		if ( ! is_array( $settings ) ) {
			return new \WP_Error(
				'invalid_settings',
				__( 'Settings must be an array.', 'elementor-mcp' )
			);
		}

		// Get widget controls to validate against.
		$stack = $widget->get_stack( false );
		$controls = $stack['controls'] ?? [];

		$validation_errors = [];

		// Check for unknown settings.
		foreach ( $settings as $setting_key => $setting_value ) {
			// Skip internal settings (prefixed with _).
			if ( '_' === substr( $setting_key, 0, 1 ) ) {
				continue;
			}

			// Check if control exists.
			if ( ! isset( $controls[ $setting_key ] ) ) {
				$validation_errors[] = sprintf(
					/* translators: %s: setting key */
					__( 'Unknown setting: %s', 'elementor-mcp' ),
					$setting_key
				);
				continue;
			}

			$control = $controls[ $setting_key ];
			$control_type = $control['type'] ?? '';

			// Type-specific validation.
			$type_validation = $this->validate_control_value( $control_type, $setting_value, $control );
			if ( is_wp_error( $type_validation ) ) {
				$validation_errors[] = sprintf(
					/* translators: 1: setting key, 2: error message */
					__( 'Invalid value for "%1$s": %2$s', 'elementor-mcp' ),
					$setting_key,
					$type_validation->get_error_message()
				);
			}
		}

		if ( ! empty( $validation_errors ) ) {
			return new \WP_Error(
				'settings_validation_failed',
				__( 'Settings validation failed.', 'elementor-mcp' ),
				[ 'errors' => $validation_errors ]
			);
		}

		return true;
	}

	/**
	 * Validate control value based on type
	 *
	 * @param string $control_type Control type.
	 * @param mixed  $value Value to validate.
	 * @param array  $control Control configuration.
	 * @return bool|\WP_Error
	 */
	private function validate_control_value( $control_type, $value, $control ) {
		// Basic type validations.
		switch ( $control_type ) {
			case 'text':
			case 'textarea':
			case 'wysiwyg':
				if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
					return new \WP_Error( 'invalid_type', __( 'Value must be a string.', 'elementor-mcp' ) );
				}
				break;

			case 'number':
			case 'slider':
				if ( ! is_numeric( $value ) ) {
					return new \WP_Error( 'invalid_type', __( 'Value must be numeric.', 'elementor-mcp' ) );
				}

				// Check min/max if defined.
				if ( isset( $control['min'] ) && $value < $control['min'] ) {
					return new \WP_Error(
						'value_too_small',
						sprintf(
							/* translators: %s: minimum value */
							__( 'Value must be at least %s.', 'elementor-mcp' ),
							$control['min']
						)
					);
				}

				if ( isset( $control['max'] ) && $value > $control['max'] ) {
					return new \WP_Error(
						'value_too_large',
						sprintf(
							/* translators: %s: maximum value */
							__( 'Value must be at most %s.', 'elementor-mcp' ),
							$control['max']
						)
					);
				}
				break;

			case 'select':
			case 'choose':
				if ( isset( $control['options'] ) && is_array( $control['options'] ) ) {
					if ( ! array_key_exists( $value, $control['options'] ) ) {
						return new \WP_Error(
							'invalid_option',
							sprintf(
								/* translators: %s: comma-separated list of valid options */
								__( 'Value must be one of: %s', 'elementor-mcp' ),
								implode( ', ', array_keys( $control['options'] ) )
							)
						);
					}
				}
				break;

			case 'color':
				if ( ! is_string( $value ) ) {
					return new \WP_Error( 'invalid_type', __( 'Color must be a string.', 'elementor-mcp' ) );
				}
				// Optional: Add color format validation (hex, rgb, etc.).
				break;

			case 'url':
				if ( is_array( $value ) ) {
					// URL control returns array with 'url', 'is_external', 'nofollow'.
					if ( isset( $value['url'] ) && ! filter_var( $value['url'], FILTER_VALIDATE_URL ) && ! empty( $value['url'] ) ) {
						return new \WP_Error( 'invalid_url', __( 'Invalid URL format.', 'elementor-mcp' ) );
					}
				}
				break;

			case 'media':
				if ( is_array( $value ) ) {
					// Media control returns array with 'id' and 'url'.
					if ( isset( $value['id'] ) && ! is_numeric( $value['id'] ) ) {
						return new \WP_Error( 'invalid_media_id', __( 'Media ID must be numeric.', 'elementor-mcp' ) );
					}
				}
				break;

			case 'repeater':
				if ( ! is_array( $value ) ) {
					return new \WP_Error( 'invalid_type', __( 'Repeater value must be an array.', 'elementor-mcp' ) );
				}
				break;
		}

		return true;
	}
}
