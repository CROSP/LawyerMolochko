<?php
/**
 * Update Global Colors Tool
 *
 * Updates the global color palette in the active kit.
 * Regenerates CSS after updating to apply changes site-wide.
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
 * Update Global Colors Tool
 *
 * Updates global colors in the active kit and regenerates CSS.
 * Can update both system colors and custom colors.
 *
 * @since 1.0.0
 */
class Update_Global_Colors extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'update_global_colors';
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
		return 'Update global colors in the active kit. Updates system colors (Primary, Secondary, Text, Accent) or custom colors. Automatically regenerates CSS to apply changes site-wide.';
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
				'colors' => array(
					'type'        => 'array',
					'description' => 'Array of color objects to update. Each must have id and color (hex value).',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'type'        => 'string',
								'description' => 'Color ID (e.g., "primary", "secondary", or custom ID)',
							),
							'color' => array(
								'type'        => 'string',
								'description' => 'Hex color value (e.g., "#FF0000" or "#FF0000FF" with alpha)',
							),
							'title' => array(
								'type'        => 'string',
								'description' => 'Optional color title/label',
							),
						),
						'required' => array( 'id', 'color' ),
					),
				),
				'regenerate_css' => array(
					'type'        => 'boolean',
					'description' => 'Whether to regenerate CSS files after update.',
					'default'     => true,
				),
			),
			'required' => array( 'colors' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Updates global colors in the active kit and regenerates CSS.
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
				'Insufficient permissions to update global colors.',
				'permission_denied'
			);
		}

		$colors = $args['colors'];
		$regenerate_css = isset( $args['regenerate_css'] ) ? $args['regenerate_css'] : true;

		// Validate colors array
		if ( ! is_array( $colors ) || empty( $colors ) ) {
			return $this->format_error(
				'Colors array is required and cannot be empty.',
				'invalid_colors'
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

			// Get current settings
			$system_colors = $active_kit->get_settings( 'system_colors' );
			$custom_colors = $active_kit->get_settings( 'custom_colors' );

			// System color IDs
			$system_color_ids = array( 'primary', 'secondary', 'text', 'accent' );

			$updated_system = array();
			$updated_custom = array();
			$added_custom = array();

			// Process each color update
			foreach ( $colors as $color_update ) {
				if ( ! isset( $color_update['id'] ) || ! isset( $color_update['color'] ) ) {
					continue;
				}

				$color_id = $color_update['id'];
				$color_value = $color_update['color'];
				$color_title = isset( $color_update['title'] ) ? $color_update['title'] : '';

				// Validate hex color
				if ( ! $this->is_valid_color( $color_value ) ) {
					return $this->format_error(
						sprintf( 'Invalid color value for ID "%s": %s', $color_id, $color_value ),
						'invalid_color_value'
					);
				}

				// Check if it's a system color
				$is_system_color = in_array( $color_id, $system_color_ids, true );

				if ( $is_system_color ) {
					// Update system color
					$found = false;
					if ( is_array( $system_colors ) ) {
						foreach ( $system_colors as &$sys_color ) {
							if ( isset( $sys_color['_id'] ) && $sys_color['_id'] === $color_id ) {
								$sys_color['color'] = $color_value;
								if ( ! empty( $color_title ) ) {
									$sys_color['title'] = $color_title;
								}
								$found = true;
								$updated_system[] = $color_id;
								break;
							}
						}
					}

					if ( ! $found ) {
						return $this->format_error(
							sprintf( 'System color "%s" not found in kit.', $color_id ),
							'system_color_not_found'
						);
					}
				} else {
					// Update or add custom color
					$found = false;
					if ( is_array( $custom_colors ) ) {
						foreach ( $custom_colors as &$cust_color ) {
							if ( isset( $cust_color['_id'] ) && $cust_color['_id'] === $color_id ) {
								$cust_color['color'] = $color_value;
								if ( ! empty( $color_title ) ) {
									$cust_color['title'] = $color_title;
								}
								$found = true;
								$updated_custom[] = $color_id;
								break;
							}
						}
					} else {
						$custom_colors = array();
					}

					// Add new custom color if not found
					if ( ! $found ) {
						$custom_colors[] = array(
							'_id'   => $color_id,
							'title' => ! empty( $color_title ) ? $color_title : ucfirst( $color_id ),
							'color' => $color_value,
						);
						$added_custom[] = $color_id;
					}
				}
			}

			// Prepare settings update
			$settings_update = array(
				'settings' => array(
					'system_colors' => $system_colors,
					'custom_colors' => $custom_colors,
				),
			);

			// Save the kit
			$saved = $active_kit->save( $settings_update );

			if ( ! $saved ) {
				return $this->format_error(
					'Failed to save kit settings.',
					'save_failed'
				);
			}

			// Regenerate CSS if requested
			if ( $regenerate_css ) {
				Plugin::$instance->files_manager->clear_cache();
			}

			return $this->format_success(
				array(
					'kit_id'         => $kit_id,
					'updated_system' => $updated_system,
					'updated_custom' => $updated_custom,
					'added_custom'   => $added_custom,
					'css_regenerated' => $regenerate_css,
				),
				sprintf(
					'Updated %d system colors, %d custom colors, and added %d new custom colors.',
					count( $updated_system ),
					count( $updated_custom ),
					count( $added_custom )
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

	/**
	 * Validate color value.
	 *
	 * Checks if the color is a valid hex color (with or without alpha).
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $color Color value to validate.
	 * @return bool Whether the color is valid.
	 */
	private function is_valid_color( $color ) {
		// Match hex colors: #RGB, #RRGGBB, #RRGGBBAA
		return (bool) preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color );
	}
}
