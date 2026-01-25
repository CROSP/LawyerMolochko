<?php
/**
 * Update Global Fonts Tool
 *
 * Updates the global typography presets in the active kit.
 * Regenerates CSS after updating to apply changes site-wide.
 *
 * @package ElementorMCP\Tools\Settings
 * @since 1.2.0
 */

namespace ElementorMCP\Tools\Settings;

use ElementorMCP\Tools\Base_Tool;
use Elementor\Plugin;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Update Global Fonts Tool Class
 *
 * Updates global typography presets in the active kit and regenerates CSS.
 * Can update both system typography (Primary, Secondary, Text, Accent)
 * and custom typography presets.
 *
 * @since 1.2.0
 */
class Update_Global_Fonts extends Base_Tool {

    /**
     * System typography IDs.
     *
     * @since 1.2.0
     * @var array
     */
    private $system_typography_ids = [ 'primary', 'secondary', 'text', 'accent' ];

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'update_global_fonts';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Update global typography presets in the active kit. Updates system fonts (Primary, Secondary, Text, Accent) or custom typography. Automatically regenerates CSS to apply changes site-wide.';
    }

    /**
     * Get input schema.
     *
     * @since 1.2.0
     * @return array
     */
    public function get_input_schema() {
        return [
            'type'       => 'object',
            'properties' => [
                'fonts' => [
                    'type'        => 'array',
                    'description' => 'Array of typography objects to update.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'id' => [
                                'type'        => 'string',
                                'description' => 'Typography ID (e.g., "primary", "secondary", "text", "accent", or custom ID)',
                            ],
                            'title' => [
                                'type'        => 'string',
                                'description' => 'Optional typography title/label',
                            ],
                            'font_family' => [
                                'type'        => 'string',
                                'description' => 'Font family name (e.g., "Roboto", "Open Sans")',
                            ],
                            'font_size' => [
                                'type'        => 'object',
                                'description' => 'Font size with unit',
                                'properties'  => [
                                    'size' => [ 'type' => 'number' ],
                                    'unit' => [
                                        'type' => 'string',
                                        'enum' => [ 'px', 'em', 'rem', '%', 'vw' ],
                                    ],
                                ],
                            ],
                            'font_weight' => [
                                'type'        => 'string',
                                'description' => 'Font weight (100-900, normal, bold)',
                                'enum'        => [ '100', '200', '300', '400', '500', '600', '700', '800', '900', 'normal', 'bold' ],
                            ],
                            'text_transform' => [
                                'type'        => 'string',
                                'description' => 'Text transform',
                                'enum'        => [ 'none', 'uppercase', 'lowercase', 'capitalize' ],
                            ],
                            'font_style' => [
                                'type'        => 'string',
                                'description' => 'Font style',
                                'enum'        => [ 'normal', 'italic', 'oblique' ],
                            ],
                            'text_decoration' => [
                                'type'        => 'string',
                                'description' => 'Text decoration',
                                'enum'        => [ 'none', 'underline', 'overline', 'line-through' ],
                            ],
                            'line_height' => [
                                'type'        => 'object',
                                'description' => 'Line height with unit',
                                'properties'  => [
                                    'size' => [ 'type' => 'number' ],
                                    'unit' => [
                                        'type' => 'string',
                                        'enum' => [ 'px', 'em', '' ],
                                    ],
                                ],
                            ],
                            'letter_spacing' => [
                                'type'        => 'object',
                                'description' => 'Letter spacing with unit',
                                'properties'  => [
                                    'size' => [ 'type' => 'number' ],
                                    'unit' => [
                                        'type' => 'string',
                                        'enum' => [ 'px', 'em' ],
                                    ],
                                ],
                            ],
                            'word_spacing' => [
                                'type'        => 'object',
                                'description' => 'Word spacing with unit',
                                'properties'  => [
                                    'size' => [ 'type' => 'number' ],
                                    'unit' => [
                                        'type' => 'string',
                                        'enum' => [ 'px', 'em' ],
                                    ],
                                ],
                            ],
                        ],
                        'required' => [ 'id' ],
                    ],
                ],
                'regenerate_css' => [
                    'type'        => 'boolean',
                    'description' => 'Whether to regenerate CSS files after update.',
                    'default'     => true,
                ],
            ],
            'required' => [ 'fonts' ],
        ];
    }

    /**
     * Execute the tool.
     *
     * @since 1.2.0
     * @param array $args Tool arguments.
     * @return array Result with update status.
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
                'Insufficient permissions to update global fonts.',
                'permission_denied'
            );
        }

        $fonts = $args['fonts'];
        $regenerate_css = isset( $args['regenerate_css'] ) ? $args['regenerate_css'] : true;

        // Validate fonts array
        if ( ! is_array( $fonts ) || empty( $fonts ) ) {
            return $this->format_error(
                'Fonts array is required and cannot be empty.',
                'invalid_fonts'
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

            // Get current typography settings
            $system_typography = $active_kit->get_settings( 'system_typography' );
            $custom_typography = $active_kit->get_settings( 'custom_typography' );

            // Ensure arrays
            if ( ! is_array( $system_typography ) ) {
                $system_typography = [];
            }
            if ( ! is_array( $custom_typography ) ) {
                $custom_typography = [];
            }

            // Typography field prefix
            $prefix = Global_Typography::TYPOGRAPHY_GROUP_PREFIX;

            // Track updates
            $updated_system = [];
            $updated_custom = [];
            $added_custom = [];

            // Process each font update
            foreach ( $fonts as $font_update ) {
                if ( empty( $font_update['id'] ) ) {
                    continue;
                }

                $font_id = $font_update['id'];
                $is_system = in_array( $font_id, $this->system_typography_ids, true );

                // Build typography properties
                $typography_props = $this->build_typography_properties( $font_update, $prefix );

                if ( $is_system ) {
                    // Update system typography
                    $found = false;
                    foreach ( $system_typography as &$sys_typo ) {
                        if ( isset( $sys_typo['_id'] ) && $sys_typo['_id'] === $font_id ) {
                            // Merge properties
                            $sys_typo = array_merge( $sys_typo, $typography_props );
                            if ( isset( $font_update['title'] ) ) {
                                $sys_typo['title'] = $font_update['title'];
                            }
                            $found = true;
                            $updated_system[] = $font_id;
                            break;
                        }
                    }

                    if ( ! $found ) {
                        return $this->format_error(
                            sprintf( 'System typography "%s" not found in kit.', $font_id ),
                            'system_typography_not_found'
                        );
                    }
                } else {
                    // Update or add custom typography
                    $found = false;
                    foreach ( $custom_typography as &$cust_typo ) {
                        if ( isset( $cust_typo['_id'] ) && $cust_typo['_id'] === $font_id ) {
                            // Merge properties
                            $cust_typo = array_merge( $cust_typo, $typography_props );
                            if ( isset( $font_update['title'] ) ) {
                                $cust_typo['title'] = $font_update['title'];
                            }
                            $found = true;
                            $updated_custom[] = $font_id;
                            break;
                        }
                    }

                    // Add new custom typography if not found
                    if ( ! $found ) {
                        $new_typo = array_merge(
                            [
                                '_id'   => $font_id,
                                'title' => isset( $font_update['title'] ) ? $font_update['title'] : ucfirst( str_replace( [ '-', '_' ], ' ', $font_id ) ),
                            ],
                            $typography_props
                        );
                        $custom_typography[] = $new_typo;
                        $added_custom[] = $font_id;
                    }
                }
            }

            // Prepare settings update
            $settings_update = [
                'settings' => [
                    'system_typography' => $system_typography,
                    'custom_typography' => $custom_typography,
                ],
            ];

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
                [
                    'kit_id'          => $kit_id,
                    'updated_system'  => $updated_system,
                    'updated_custom'  => $updated_custom,
                    'added_custom'    => $added_custom,
                    'css_regenerated' => $regenerate_css,
                ],
                sprintf(
                    'Updated %d system fonts, %d custom fonts, and added %d new custom fonts.',
                    count( $updated_system ),
                    count( $updated_custom ),
                    count( $added_custom )
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                $e->getMessage(),
                'execution_error',
                [ 'trace' => $e->getTraceAsString() ]
            );
        }
    }

    /**
     * Build typography properties array with proper field keys.
     *
     * @since 1.2.0
     * @param array  $font_update Font update data.
     * @param string $prefix      Typography field prefix.
     * @return array Typography properties with prefixed keys.
     */
    private function build_typography_properties( $font_update, $prefix ) {
        $props = [];

        // Map input keys to Elementor typography keys
        $mappings = [
            'font_family'     => $prefix . 'font_family',
            'font_size'       => $prefix . 'font_size',
            'font_weight'     => $prefix . 'font_weight',
            'text_transform'  => $prefix . 'text_transform',
            'font_style'      => $prefix . 'font_style',
            'text_decoration' => $prefix . 'text_decoration',
            'line_height'     => $prefix . 'line_height',
            'letter_spacing'  => $prefix . 'letter_spacing',
            'word_spacing'    => $prefix . 'word_spacing',
        ];

        foreach ( $mappings as $input_key => $elementor_key ) {
            if ( isset( $font_update[ $input_key ] ) ) {
                $props[ $elementor_key ] = $font_update[ $input_key ];
            }
        }

        // Set typography type to custom if we have any properties
        if ( ! empty( $props ) ) {
            $props[ $prefix . 'typography' ] = 'custom';
        }

        return $props;
    }
}
