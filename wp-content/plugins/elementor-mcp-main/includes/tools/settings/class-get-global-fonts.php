<?php
/**
 * Get Global Fonts Tool
 *
 * Retrieves the global typography presets from the active kit.
 * Global fonts are part of Elementor's design system (Kits).
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Settings;

use ElementorMCP\Tools\Base_Tool;
use Elementor\Plugin;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Global Fonts Tool
 *
 * Retrieves all global typography presets (system and custom) from the active kit.
 * These are stored in the kit's settings and include font families, sizes, weights, etc.
 *
 * @since 1.0.0
 */
class Get_Global_Fonts extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_global_fonts';
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
		return 'Get global typography presets from the active kit. Returns both system fonts (Primary, Secondary, Text, Accent) and custom typography with all properties like font family, size, weight, line height, etc.';
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
					'description' => 'Include custom typography in addition to system fonts.',
					'default'     => true,
				),
			),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves global typography from the active kit's settings.
	 * Typography is stored with CSS variables like --e-global-typography-{id}-font-family.
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
				'Insufficient permissions to access global fonts.',
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
			$system_typography = $active_kit->get_settings( 'system_typography' );
			$custom_typography = $include_custom ? $active_kit->get_settings( 'custom_typography' ) : array();

			// Get fallback font family
			$default_generic_fonts = $active_kit->get_settings( 'default_generic_fonts' );

			// Typography group prefix
			$typography_prefix = Global_Typography::TYPOGRAPHY_GROUP_PREFIX;

			// Format system typography
			$formatted_system_typography = array();
			if ( is_array( $system_typography ) ) {
				foreach ( $system_typography as $typography ) {
					if ( isset( $typography['_id'] ) ) {
						$formatted_system_typography[] = $this->format_typography_item( $typography, 'system', $typography_prefix );
					}
				}
			}

			// Format custom typography
			$formatted_custom_typography = array();
			if ( is_array( $custom_typography ) ) {
				foreach ( $custom_typography as $typography ) {
					if ( isset( $typography['_id'] ) ) {
						$formatted_custom_typography[] = $this->format_typography_item( $typography, 'custom', $typography_prefix );
					}
				}
			}

			$response_data = array(
				'kit_id'             => $kit_id,
				'system_typography'  => $formatted_system_typography,
				'custom_typography'  => $formatted_custom_typography,
				'total_system'       => count( $formatted_system_typography ),
				'total_custom'       => count( $formatted_custom_typography ),
				'fallback_font_family' => $default_generic_fonts,
			);

			return $this->format_success(
				$response_data,
				sprintf(
					'Retrieved %d system fonts and %d custom fonts from kit #%d.',
					count( $formatted_system_typography ),
					count( $formatted_custom_typography ),
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

	/**
	 * Format typography item.
	 *
	 * Extracts and formats all typography properties.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array  $typography       Typography data from kit.
	 * @param string $type             Type (system or custom).
	 * @param string $typography_prefix Typography field prefix.
	 * @return array Formatted typography data.
	 */
	private function format_typography_item( $typography, $type, $typography_prefix ) {
		$id = $typography['_id'];

		// Build field keys with prefix
		$font_family_key = $typography_prefix . 'font_family';
		$font_size_key = $typography_prefix . 'font_size';
		$font_weight_key = $typography_prefix . 'font_weight';
		$text_transform_key = $typography_prefix . 'text_transform';
		$font_style_key = $typography_prefix . 'font_style';
		$text_decoration_key = $typography_prefix . 'text_decoration';
		$line_height_key = $typography_prefix . 'line_height';
		$letter_spacing_key = $typography_prefix . 'letter_spacing';
		$word_spacing_key = $typography_prefix . 'word_spacing';

		$formatted = array(
			'id'    => $id,
			'title' => isset( $typography['title'] ) ? $typography['title'] : '',
			'type'  => $type,
			'reference' => 'globals/typography?id=' . $id,
		);

		// Extract typography properties
		$properties = array();

		if ( isset( $typography[ $font_family_key ] ) ) {
			$properties['font_family'] = $typography[ $font_family_key ];
		}

		if ( isset( $typography[ $font_size_key ] ) ) {
			$properties['font_size'] = $typography[ $font_size_key ];
		}

		if ( isset( $typography[ $font_weight_key ] ) ) {
			$properties['font_weight'] = $typography[ $font_weight_key ];
		}

		if ( isset( $typography[ $text_transform_key ] ) ) {
			$properties['text_transform'] = $typography[ $text_transform_key ];
		}

		if ( isset( $typography[ $font_style_key ] ) ) {
			$properties['font_style'] = $typography[ $font_style_key ];
		}

		if ( isset( $typography[ $text_decoration_key ] ) ) {
			$properties['text_decoration'] = $typography[ $text_decoration_key ];
		}

		if ( isset( $typography[ $line_height_key ] ) ) {
			$properties['line_height'] = $typography[ $line_height_key ];
		}

		if ( isset( $typography[ $letter_spacing_key ] ) ) {
			$properties['letter_spacing'] = $typography[ $letter_spacing_key ];
		}

		if ( isset( $typography[ $word_spacing_key ] ) ) {
			$properties['word_spacing'] = $typography[ $word_spacing_key ];
		}

		$formatted['properties'] = $properties;

		// CSS variables
		$formatted['css_variables'] = array(
			'font_family'     => '--e-global-typography-' . $id . '-font-family',
			'font_size'       => '--e-global-typography-' . $id . '-font-size',
			'font_weight'     => '--e-global-typography-' . $id . '-font-weight',
			'text_transform'  => '--e-global-typography-' . $id . '-text-transform',
			'font_style'      => '--e-global-typography-' . $id . '-font-style',
			'text_decoration' => '--e-global-typography-' . $id . '-text-decoration',
			'line_height'     => '--e-global-typography-' . $id . '-line-height',
			'letter_spacing'  => '--e-global-typography-' . $id . '-letter-spacing',
			'word_spacing'    => '--e-global-typography-' . $id . '-word-spacing',
		);

		return $formatted;
	}
}
