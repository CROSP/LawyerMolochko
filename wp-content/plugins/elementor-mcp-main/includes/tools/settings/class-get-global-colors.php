<?php
/**
 * Get Global Colors Tool
 *
 * Retrieves the global color palette from the active kit.
 * Global colors are part of Elementor's design system (Kits).
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
 * Get Global Colors Tool
 *
 * Retrieves all global colors (system and custom) from the active kit.
 * Global colors are stored in the kit's settings and used throughout the site.
 *
 * @since 1.0.0
 */
class Get_Global_Colors extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_global_colors';
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
		return 'Get the global color palette from the active kit. Returns both system colors (Primary, Secondary, Text, Accent) and custom colors with their IDs, titles, and hex values.';
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
				'include_custom' => array(
					'type'        => 'boolean',
					'description' => 'Include custom colors in addition to system colors.',
					'default'     => true,
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves global colors from the active kit's settings.
	 * Colors are stored in CSS variables like --e-global-color-{id}.
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
		if ( ! $this->check_permissions( 'edit_theme_options' ) ) {
			return $this->format_error(
				'Insufficient permissions to access global colors.',
				'permission_denied'
			);
		}

		try {
			$kits_manager = Plugin::$instance->kits_manager;
			$active_kit = $kits_manager->get_active_kit();

			if ( ! $active_kit ) {
				return $this->format_error(
					'No active kit found.',
					'no_active_kit'
				);
			}

			$kit_id = $active_kit->get_id();
			$include_custom = isset( $args['include_custom'] ) ? $args['include_custom'] : true;

			// Get kit settings
			$system_colors = $active_kit->get_settings( 'system_colors' );
			$custom_colors = $include_custom ? $active_kit->get_settings( 'custom_colors' ) : array();

			// Format system colors
			$formatted_system_colors = array();
			if ( is_array( $system_colors ) ) {
				foreach ( $system_colors as $color ) {
					if ( isset( $color['_id'] ) ) {
						$formatted_system_colors[] = array(
							'id'    => $color['_id'],
							'title' => isset( $color['title'] ) ? $color['title'] : '',
							'color' => isset( $color['color'] ) ? $color['color'] : '',
							'type'  => 'system',
							'css_variable' => '--e-global-color-' . $color['_id'],
							'reference' => 'globals/colors?id=' . $color['_id'],
						);
					}
				}
			}

			// Format custom colors
			$formatted_custom_colors = array();
			if ( is_array( $custom_colors ) ) {
				foreach ( $custom_colors as $color ) {
					if ( isset( $color['_id'] ) ) {
						$formatted_custom_colors[] = array(
							'id'    => $color['_id'],
							'title' => isset( $color['title'] ) ? $color['title'] : '',
							'color' => isset( $color['color'] ) ? $color['color'] : '',
							'type'  => 'custom',
							'css_variable' => '--e-global-color-' . $color['_id'],
							'reference' => 'globals/colors?id=' . $color['_id'],
						);
					}
				}
			}

			$response_data = array(
				'kit_id'        => $kit_id,
				'system_colors' => $formatted_system_colors,
				'custom_colors' => $formatted_custom_colors,
				'total_system'  => count( $formatted_system_colors ),
				'total_custom'  => count( $formatted_custom_colors ),
			);

			return $this->format_success(
				$response_data,
				sprintf(
					'Retrieved %d system colors and %d custom colors from kit #%d.',
					count( $formatted_system_colors ),
					count( $formatted_custom_colors ),
					$kit_id
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
