<?php
/**
 * Get Breakpoints Tool
 *
 * Retrieves the responsive breakpoints configuration from Elementor.
 * Includes both active and available breakpoints.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Settings;

use ElementorMCP\Tools\Base_Tool;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Breakpoints Tool
 *
 * Retrieves breakpoint configuration including mobile, tablet, laptop, widescreen, etc.
 * Shows which breakpoints are active and their min/max values.
 *
 * @since 1.0.0
 */
class Get_Breakpoints extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_breakpoints';
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
		return 'Get responsive breakpoints configuration. Returns all available breakpoints (mobile, tablet, laptop, widescreen) with their values, directions (min/max), and active status.';
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
				'active_only' => array(
					'type'        => 'boolean',
					'description' => 'Return only active breakpoints.',
					'default'     => false,
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves breakpoint configuration from the Breakpoints Manager.
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
				'Insufficient permissions to access breakpoints.',
				'permission_denied'
			);
		}

		try {
			$breakpoints_manager = Plugin::$instance->breakpoints;
			$active_only = isset( $args['active_only'] ) ? $args['active_only'] : false;

			// Get all breakpoints or just active ones
			if ( $active_only ) {
				$breakpoints = $breakpoints_manager->get_active_breakpoints();
			} else {
				$breakpoints = $breakpoints_manager->get_breakpoints();
			}

			$formatted_breakpoints = array();

			foreach ( $breakpoints as $breakpoint_name => $breakpoint ) {
				$formatted_breakpoints[ $breakpoint_name ] = array(
					'name'          => $breakpoint_name,
					'label'         => $breakpoint->get_label(),
					'value'         => $breakpoint->get_value(),
					'default_value' => $breakpoint->get_default_value(),
					'direction'     => $breakpoint->get_direction(), // 'min' or 'max'
					'is_enabled'    => $breakpoint->is_enabled(),
					'is_custom'     => $breakpoint->is_custom(),
				);

				// Add min breakpoint for context
				if ( method_exists( $breakpoints_manager, 'get_device_min_breakpoint' ) ) {
					$formatted_breakpoints[ $breakpoint_name ]['min_breakpoint'] = $breakpoints_manager->get_device_min_breakpoint( $breakpoint_name );
				}
			}

			// Get active devices list
			$active_devices = $breakpoints_manager->get_active_devices_list();

			// Get desktop min point
			$desktop_min_point = method_exists( $breakpoints_manager, 'get_desktop_min_point' )
				? $breakpoints_manager->get_desktop_min_point()
				: null;

			// Check if there are custom breakpoints
			$has_custom = method_exists( $breakpoints_manager, 'has_custom_breakpoints' )
				? $breakpoints_manager->has_custom_breakpoints()
				: false;

			// Get responsive icons map
			$icons_map = method_exists( $breakpoints_manager, 'get_responsive_icons_classes_map' )
				? $breakpoints_manager->get_responsive_icons_classes_map()
				: array();

			$response_data = array(
				'breakpoints'       => $formatted_breakpoints,
				'active_devices'    => $active_devices,
				'desktop_min_point' => $desktop_min_point,
				'has_custom_breakpoints' => $has_custom,
				'icons_map'         => $icons_map,
				'total_breakpoints' => count( $formatted_breakpoints ),
			);

			// Add usage notes
			$response_data['usage_notes'] = array(
				'mobile'        => 'Devices <= ' . ( isset( $formatted_breakpoints['mobile'] ) ? $formatted_breakpoints['mobile']['value'] : 'N/A' ) . 'px',
				'tablet'        => 'Devices <= ' . ( isset( $formatted_breakpoints['tablet'] ) ? $formatted_breakpoints['tablet']['value'] : 'N/A' ) . 'px',
				'desktop'       => 'Devices >= ' . $desktop_min_point . 'px',
				'direction_max' => 'Uses @media (max-width) - applies to this size and smaller',
				'direction_min' => 'Uses @media (min-width) - applies to this size and larger',
			);

			return $this->format_success(
				$response_data,
				sprintf(
					'Retrieved %d breakpoints (%d active).',
					count( $formatted_breakpoints ),
					count( $active_devices )
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
