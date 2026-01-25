<?php
/**
 * List Control Types Tool
 *
 * Retrieves all available Elementor control types with their metadata.
 * Uses the Controls Manager to get all registered control instances.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Controls;

use ElementorMCP\Tools\Base_Tool;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * List Control Types Tool
 *
 * Retrieves all control types registered in Elementor's Controls Manager,
 * including their configuration, type, and capabilities.
 *
 * @since 1.0.0
 */
class List_Control_Types extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'list_control_types';
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
		return 'Get all available Elementor control types with their properties and capabilities. Returns control type name, class, features (like base data control), and default values.';
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
				'include_ui_controls' => array(
					'type'        => 'boolean',
					'description' => 'Include UI-only controls like section, heading, divider, etc.',
					'default'     => true,
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves all control types from the Controls Manager and formats
	 * them with metadata about capabilities and configuration.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Tool arguments.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error(
				'Elementor is not active or loaded.',
				'elementor_not_active'
			);
		}

		// Check permissions
		if ( ! $this->check_permissions( 'edit_posts' ) ) {
			return $this->format_error(
				'Insufficient permissions to access control types.',
				'permission_denied'
			);
		}

		try {
			$controls_manager = Plugin::$instance->controls_manager;
			$controls = $controls_manager->get_controls();

			if ( empty( $controls ) ) {
				return $this->format_success(
					array(),
					'No control types found.'
				);
			}

			$include_ui_controls = isset( $args['include_ui_controls'] ) ? $args['include_ui_controls'] : true;

			$controls_data = array();

			// UI-only controls that don't hold data
			$ui_controls = array(
				'section',
				'heading',
				'raw_html',
				'divider',
				'tab',
				'tabs',
				'popover_toggle',
				'button',
				'alert',
				'notice',
				'deprecated_notice',
			);

			foreach ( $controls as $control_type => $control_instance ) {
				// Skip UI controls if requested
				if ( ! $include_ui_controls && in_array( $control_type, $ui_controls, true ) ) {
					continue;
				}

				$control_data = array(
					'type'  => $control_type,
					'class' => get_class( $control_instance ),
				);

				// Check if it's a base data control
				$control_data['is_data_control'] = is_a( $control_instance, '\Elementor\Base_Data_Control' );
				$control_data['is_ui_control'] = in_array( $control_type, $ui_controls, true );

				// Get control settings
				if ( method_exists( $control_instance, 'get_settings' ) ) {
					$settings = $control_instance->get_settings();

					// Add useful settings data
					if ( isset( $settings['label'] ) ) {
						$control_data['label'] = $settings['label'];
					}

					if ( isset( $settings['type'] ) ) {
						$control_data['settings_type'] = $settings['type'];
					}
				}

				// Get default value for data controls
				if ( $control_data['is_data_control'] && method_exists( $control_instance, 'get_default_value' ) ) {
					$control_data['default_value'] = $control_instance->get_default_value();
				}

				// Get type constant if available
				if ( method_exists( $control_instance, 'get_type' ) ) {
					$control_data['type_constant'] = $control_instance->get_type();
				}

				// Check for special features
				$control_data['features'] = array();

				// Check if control supports global values
				if ( method_exists( $control_instance, 'get_supported_global_values' ) ) {
					$control_data['features']['supports_global'] = true;
				}

				// Check if control is repeater
				if ( $control_type === 'repeater' ) {
					$control_data['features']['is_repeater'] = true;
				}

				// Check if control is group control
				if ( strpos( get_class( $control_instance ), 'Group_Control' ) !== false ) {
					$control_data['features']['is_group_control'] = true;
				}

				$controls_data[ $control_type ] = $control_data;
			}

			// Also get group controls
			$group_controls = $controls_manager->get_control_groups();
			$group_controls_data = array();

			foreach ( $group_controls as $group_name => $group_instance ) {
				$group_controls_data[ $group_name ] = array(
					'name'  => $group_name,
					'class' => get_class( $group_instance ),
					'type'  => 'group_control',
				);

				if ( method_exists( $group_instance, 'get_type' ) ) {
					$group_controls_data[ $group_name ]['type_constant'] = $group_instance->get_type();
				}
			}

			return $this->format_success(
				array(
					'controls'       => $controls_data,
					'group_controls' => $group_controls_data,
					'total_controls' => count( $controls_data ),
					'total_groups'   => count( $group_controls_data ),
				),
				sprintf(
					'Retrieved %d control types and %d group controls.',
					count( $controls_data ),
					count( $group_controls_data )
				)
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'execution_error',
				array( 'trace' => $e->getTraceAsString() )
			);
		}
	}
}
