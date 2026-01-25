<?php
/**
 * Create Container Tool
 *
 * Creates modern Elementor container structures with flexbox/grid layouts.
 *
 * @package ElementorMCP\Tools\Widgets
 * @since 1.2.0
 */

namespace ElementorMCP\Tools\Widgets;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create Container Tool Class
 *
 * Creates properly structured Elementor containers with modern
 * flexbox or grid layouts and nested container support.
 *
 * @since 1.2.0
 */
class Create_Container extends Base_Tool {

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'create_container';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Create Container';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Create a modern Elementor container with flexbox or grid layout. Supports nested containers, direct widgets, and responsive settings.';
    }

    /**
     * Get tool category.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_category() {
        return 'widgets';
    }

    /**
     * Get tool keywords.
     *
     * @since 1.2.0
     * @return array
     */
    public function get_keywords() {
        return [ 'container', 'flexbox', 'grid', 'layout', 'modern', 'create' ];
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
                'layout' => [
                    'type'        => 'string',
                    'description' => 'Container layout type',
                    'enum'        => [ 'flex', 'grid' ],
                    'default'     => 'flex',
                ],
                'direction' => [
                    'type'        => 'string',
                    'description' => 'Flex direction (for flex layout)',
                    'enum'        => [ 'row', 'column' ],
                    'default'     => 'column',
                ],
                'justify' => [
                    'type'        => 'string',
                    'description' => 'Justify content alignment',
                    'enum'        => [ 'flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly' ],
                    'default'     => 'flex-start',
                ],
                'align' => [
                    'type'        => 'string',
                    'description' => 'Align items',
                    'enum'        => [ 'flex-start', 'center', 'flex-end', 'stretch' ],
                    'default'     => 'stretch',
                ],
                'gap' => [
                    'type'        => 'object',
                    'description' => 'Gap between items',
                    'properties'  => [
                        'size' => [
                            'type'    => 'integer',
                            'default' => 20,
                        ],
                        'unit' => [
                            'type'    => 'string',
                            'enum'    => [ 'px', 'em', 'rem', '%', 'vw' ],
                            'default' => 'px',
                        ],
                    ],
                ],
                'wrap' => [
                    'type'        => 'string',
                    'description' => 'Flex wrap behavior',
                    'enum'        => [ 'nowrap', 'wrap' ],
                    'default'     => 'nowrap',
                ],
                'width' => [
                    'type'        => 'string',
                    'description' => 'Container width type',
                    'enum'        => [ 'boxed', 'full' ],
                    'default'     => 'boxed',
                ],
                'content_width' => [
                    'type'        => 'object',
                    'description' => 'Content width for boxed layout',
                    'properties'  => [
                        'size' => [
                            'type'    => 'integer',
                            'default' => 1140,
                        ],
                        'unit' => [
                            'type'    => 'string',
                            'enum'    => [ 'px', '%', 'vw' ],
                            'default' => 'px',
                        ],
                    ],
                ],
                'min_height' => [
                    'type'        => 'object',
                    'description' => 'Minimum container height',
                    'properties'  => [
                        'size' => [ 'type' => 'integer' ],
                        'unit' => [
                            'type'    => 'string',
                            'enum'    => [ 'px', 'vh', '%' ],
                            'default' => 'px',
                        ],
                    ],
                ],
                'widgets' => [
                    'type'        => 'array',
                    'description' => 'Widgets to place directly in the container',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'widget_type' => [
                                'type'        => 'string',
                                'description' => 'Widget type (heading, button, image, etc.)',
                            ],
                            'settings' => [
                                'type'        => 'object',
                                'description' => 'Widget settings',
                                'default'     => new \stdClass(),
                            ],
                        ],
                        'required' => [ 'widget_type' ],
                    ],
                ],
                'nested_containers' => [
                    'type'        => 'array',
                    'description' => 'Nested containers for multi-column layouts',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'width' => [
                                'type'        => 'integer',
                                'description' => 'Container width percentage or flex grow value',
                                'minimum'     => 1,
                                'maximum'     => 100,
                            ],
                            'settings' => [
                                'type'        => 'object',
                                'description' => 'Nested container settings',
                            ],
                            'widgets' => [
                                'type'        => 'array',
                                'description' => 'Widgets in this nested container',
                                'items'       => [
                                    'type'       => 'object',
                                    'properties' => [
                                        'widget_type' => [ 'type' => 'string' ],
                                        'settings'    => [ 'type' => 'object' ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'type'        => 'object',
                    'description' => 'Additional container settings (background, padding, etc.)',
                    'default'     => new \stdClass(),
                ],
                'background' => [
                    'type'        => 'object',
                    'description' => 'Background settings shorthand',
                    'properties'  => [
                        'type'  => [
                            'type' => 'string',
                            'enum' => [ 'classic', 'gradient', 'video' ],
                        ],
                        'color' => [ 'type' => 'string' ],
                        'image' => [
                            'type'       => 'object',
                            'properties' => [
                                'url' => [ 'type' => 'string' ],
                                'id'  => [ 'type' => 'integer' ],
                            ],
                        ],
                    ],
                ],
                'padding' => [
                    'type'        => 'object',
                    'description' => 'Padding shorthand',
                    'properties'  => [
                        'top'    => [ 'type' => 'integer' ],
                        'right'  => [ 'type' => 'integer' ],
                        'bottom' => [ 'type' => 'integer' ],
                        'left'   => [ 'type' => 'integer' ],
                        'unit'   => [
                            'type'    => 'string',
                            'enum'    => [ 'px', 'em', '%' ],
                            'default' => 'px',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Execute the tool.
     *
     * @since 1.2.0
     * @param array $args Tool arguments.
     * @return array Result with container structure.
     */
    public function execute( $args ) {
        // Verify Elementor is active
        if ( ! $this->verify_elementor_active() ) {
            return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
        }

        try {
            // Extract parameters
            $layout            = $args['layout'] ?? 'flex';
            $direction         = $args['direction'] ?? 'column';
            $justify           = $args['justify'] ?? 'flex-start';
            $align             = $args['align'] ?? 'stretch';
            $gap               = $args['gap'] ?? null;
            $wrap              = $args['wrap'] ?? 'nowrap';
            $width_type        = $args['width'] ?? 'boxed';
            $content_width     = $args['content_width'] ?? null;
            $min_height        = $args['min_height'] ?? null;
            $widgets           = $args['widgets'] ?? [];
            $nested_containers = $args['nested_containers'] ?? [];
            $extra_settings    = $args['settings'] ?? [];
            $background        = $args['background'] ?? null;
            $padding           = $args['padding'] ?? null;

            // Build container settings
            $container_settings = $this->build_container_settings(
                $layout,
                $direction,
                $justify,
                $align,
                $gap,
                $wrap,
                $width_type,
                $content_width,
                $min_height,
                $background,
                $padding
            );

            // Merge with extra settings
            $container_settings = array_merge( $container_settings, $extra_settings );

            // Build child elements
            $elements = [];

            // Add nested containers if provided
            if ( ! empty( $nested_containers ) ) {
                foreach ( $nested_containers as $nested ) {
                    $elements[] = $this->create_nested_container( $nested );
                }

                // Ensure parent is row direction for multi-column layout
                if ( count( $nested_containers ) > 1 && ! isset( $extra_settings['flex_direction'] ) ) {
                    $container_settings['flex_direction'] = 'row';
                }
            }

            // Add direct widgets
            if ( ! empty( $widgets ) ) {
                foreach ( $widgets as $widget_def ) {
                    $elements[] = $this->create_widget( $widget_def );
                }
            }

            // Create container
            $container = [
                'id'       => $this->generate_element_id(),
                'elType'   => 'container',
                'settings' => ! empty( $container_settings ) ? $container_settings : new \stdClass(),
                'elements' => $elements,
            ];

            // Count stats
            $widget_count = $this->count_widgets( $container );
            $nested_count = count( $nested_containers );

            return $this->format_success(
                [
                    'container'       => $container,
                    'layout'          => $layout,
                    'direction'       => $container_settings['flex_direction'] ?? $direction,
                    'nested_count'    => $nested_count,
                    'widget_count'    => $widget_count,
                    'total_elements'  => count( $elements ),
                ],
                sprintf(
                    'Created %s container with %d nested container(s) and %d widget(s)',
                    $layout,
                    $nested_count,
                    $widget_count
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to create container: ' . $e->getMessage(),
                'creation_failed'
            );
        }
    }

    /**
     * Build container settings from parameters.
     *
     * @since 1.2.0
     * @param string     $layout        Layout type.
     * @param string     $direction     Flex direction.
     * @param string     $justify       Justify content.
     * @param string     $align         Align items.
     * @param array|null $gap           Gap settings.
     * @param string     $wrap          Flex wrap.
     * @param string     $width_type    Width type.
     * @param array|null $content_width Content width.
     * @param array|null $min_height    Minimum height.
     * @param array|null $background    Background settings.
     * @param array|null $padding       Padding settings.
     * @return array Container settings.
     */
    private function build_container_settings(
        $layout,
        $direction,
        $justify,
        $align,
        $gap,
        $wrap,
        $width_type,
        $content_width,
        $min_height,
        $background,
        $padding
    ) {
        $settings = [];

        // Flex settings
        $settings['flex_direction'] = $direction;
        $settings['flex_justify_content'] = $justify;
        $settings['flex_align_items'] = $align;
        $settings['flex_wrap'] = $wrap;

        // Gap
        if ( $gap !== null ) {
            $settings['flex_gap'] = [
                'size'     => $gap['size'] ?? 20,
                'unit'     => $gap['unit'] ?? 'px',
                'isLinked' => true,
            ];
        }

        // Width
        $settings['content_width'] = $width_type;
        if ( $width_type === 'boxed' && $content_width !== null ) {
            $settings['boxed_width'] = [
                'size' => $content_width['size'] ?? 1140,
                'unit' => $content_width['unit'] ?? 'px',
            ];
        }

        // Min height
        if ( $min_height !== null && isset( $min_height['size'] ) ) {
            $settings['min_height'] = [
                'size' => $min_height['size'],
                'unit' => $min_height['unit'] ?? 'px',
            ];
        }

        // Background
        if ( $background !== null ) {
            if ( isset( $background['type'] ) ) {
                $settings['background_background'] = $background['type'];
            }
            if ( isset( $background['color'] ) ) {
                $settings['background_color'] = $background['color'];
            }
            if ( isset( $background['image'] ) ) {
                $settings['background_image'] = $background['image'];
            }
        }

        // Padding
        if ( $padding !== null ) {
            $settings['padding'] = [
                'top'      => (string) ( $padding['top'] ?? 0 ),
                'right'    => (string) ( $padding['right'] ?? 0 ),
                'bottom'   => (string) ( $padding['bottom'] ?? 0 ),
                'left'     => (string) ( $padding['left'] ?? 0 ),
                'unit'     => $padding['unit'] ?? 'px',
                'isLinked' => false,
            ];
        }

        return $settings;
    }

    /**
     * Create a nested container element.
     *
     * @since 1.2.0
     * @param array $config Nested container configuration.
     * @return array Container element.
     */
    private function create_nested_container( $config ) {
        $settings = $config['settings'] ?? [];

        // Set flex width/grow based on width parameter
        if ( isset( $config['width'] ) ) {
            $settings['flex_size'] = 'none';
            $settings['width'] = [
                'size' => $config['width'],
                'unit' => '%',
            ];
        }

        // Build child widgets
        $elements = [];
        if ( ! empty( $config['widgets'] ) ) {
            foreach ( $config['widgets'] as $widget_def ) {
                $elements[] = $this->create_widget( $widget_def );
            }
        }

        return [
            'id'       => $this->generate_element_id(),
            'elType'   => 'container',
            'settings' => ! empty( $settings ) ? $settings : new \stdClass(),
            'elements' => $elements,
        ];
    }

    /**
     * Create a widget element.
     *
     * @since 1.2.0
     * @param array $widget_def Widget definition.
     * @return array Widget element.
     */
    private function create_widget( $widget_def ) {
        $widget_type = $widget_def['widget_type'] ?? 'text-editor';
        $settings = $widget_def['settings'] ?? [];

        return [
            'id'         => $this->generate_element_id(),
            'elType'     => 'widget',
            'widgetType' => $widget_type,
            'settings'   => ! empty( $settings ) ? $settings : new \stdClass(),
            'elements'   => [],
        ];
    }

    /**
     * Generate unique element ID.
     *
     * @since 1.2.0
     * @return string 8-character hex ID.
     */
    private function generate_element_id() {
        return substr( md5( uniqid( mt_rand(), true ) ), 0, 8 );
    }

    /**
     * Count widgets in container tree.
     *
     * @since 1.2.0
     * @param array $element Element to count.
     * @return int Widget count.
     */
    private function count_widgets( $element ) {
        $count = 0;

        if ( ( $element['elType'] ?? '' ) === 'widget' ) {
            return 1;
        }

        if ( ! empty( $element['elements'] ) ) {
            foreach ( $element['elements'] as $child ) {
                $count += $this->count_widgets( $child );
            }
        }

        return $count;
    }
}
