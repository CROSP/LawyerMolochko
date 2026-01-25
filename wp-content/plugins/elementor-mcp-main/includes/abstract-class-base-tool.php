<?php
/**
 * Base Tool Abstract Class
 *
 * Provides common functionality for all MCP tools including:
 * - Elementor availability verification
 * - Input validation
 * - Error handling
 * - Response formatting
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract base class for all MCP tools
 */
abstract class Base_Tool {

	/**
	 * Tool name/identifier
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Tool description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Required input schema
	 *
	 * @var array
	 */
	protected $input_schema = [];

	/**
	 * Execute the tool
	 *
	 * @param array $args Tool arguments.
	 * @return array Tool response with success status and data/error.
	 */
	abstract public function execute( $args = [] );

	/**
	 * Get tool name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get tool description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get input schema
	 *
	 * @return array
	 */
	public function get_input_schema() {
		return $this->input_schema;
	}

	/**
	 * Get tool schema (for MCP protocol)
	 *
	 * @return array
	 */
	public function get_schema() {
		return [
			'description' => $this->get_description(),
			'inputSchema' => $this->get_input_schema(),
		];
	}

	/**
	 * Verify Elementor is active and available
	 *
	 * @return bool|\WP_Error True if available, WP_Error otherwise.
	 */
	protected function verify_elementor() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return new \WP_Error(
				'elementor_not_loaded',
				__( 'Elementor is not loaded.', 'elementor-mcp' )
			);
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new \WP_Error(
				'elementor_not_available',
				__( 'Elementor Plugin class is not available.', 'elementor-mcp' )
			);
		}

		if ( ! Plugin::$instance ) {
			return new \WP_Error(
				'elementor_not_initialized',
				__( 'Elementor is not initialized.', 'elementor-mcp' )
			);
		}

		return true;
	}

	/**
	 * Validate required arguments
	 *
	 * @param array $args Input arguments.
	 * @param array $required Required argument keys.
	 * @return bool|\WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_required_args( $args, $required ) {
		foreach ( $required as $key ) {
			if ( ! isset( $args[ $key ] ) || '' === $args[ $key ] ) {
				return new \WP_Error(
					'missing_required_argument',
					sprintf(
						/* translators: %s: argument name */
						__( 'Missing required argument: %s', 'elementor-mcp' ),
						$key
					)
				);
			}
		}

		return true;
	}

	/**
	 * Format success response
	 *
	 * @param mixed $data Response data.
	 * @param string $message Optional success message.
	 * @return array
	 */
	protected function success( $data, $message = '' ) {
		$response = [
			'success' => true,
			'data'    => $data,
		];

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return $response;
	}

	/**
	 * Format error response
	 *
	 * @param string|\WP_Error $error Error message or WP_Error object.
	 * @param string $code Error code.
	 * @return array
	 */
	protected function error( $error, $code = 'tool_error' ) {
		if ( is_wp_error( $error ) ) {
			return [
				'success' => false,
				'error'   => [
					'code'    => $error->get_error_code(),
					'message' => $error->get_error_message(),
					'data'    => $error->get_error_data(),
				],
			];
		}

		return [
			'success' => false,
			'error'   => [
				'code'    => $code,
				'message' => $error,
			],
		];
	}

	/**
	 * Sanitize widget settings recursively
	 *
	 * @param array $settings Widget settings.
	 * @return array Sanitized settings.
	 */
	protected function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		$sanitized = [];

		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_settings( $value );
			} elseif ( is_string( $value ) ) {
				// Basic sanitization - can be extended based on control types.
				$sanitized[ $key ] = sanitize_text_field( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Generate unique element ID
	 *
	 * @return string
	 */
	protected function generate_element_id() {
		return dechex( mt_rand() );
	}

	/**
	 * Get widget instance by name
	 *
	 * @param string $widget_name Widget name.
	 * @return \Elementor\Widget_Base|\WP_Error Widget instance or error.
	 */
	protected function get_widget_instance( $widget_name ) {
		$verify = $this->verify_elementor();
		if ( is_wp_error( $verify ) ) {
			return $verify;
		}

		$widget = Plugin::$instance->widgets_manager->get_widget_types( $widget_name );

		if ( ! $widget ) {
			return new \WP_Error(
				'widget_not_found',
				sprintf(
					/* translators: %s: widget name */
					__( 'Widget not found: %s', 'elementor-mcp' ),
					$widget_name
				)
			);
		}

		return $widget;
	}

	/**
	 * Extract control data for MCP response
	 *
	 * @param array $control Control array from Elementor.
	 * @return array Cleaned control data.
	 */
	protected function extract_control_data( $control ) {
		$data = [
			'name'  => $control['name'] ?? '',
			'label' => $control['label'] ?? '',
			'type'  => $control['type'] ?? '',
		];

		// Add optional fields if they exist.
		$optional_fields = [
			'default',
			'options',
			'placeholder',
			'description',
			'separator',
			'conditions',
			'condition',
			'selectors',
			'responsive',
			'dynamic',
			'ai',
			'min',
			'max',
			'step',
			'rows',
			'show_label',
			'label_block',
		];

		foreach ( $optional_fields as $field ) {
			if ( isset( $control[ $field ] ) ) {
				$data[ $field ] = $control[ $field ];
			}
		}

		return $data;
	}
}
