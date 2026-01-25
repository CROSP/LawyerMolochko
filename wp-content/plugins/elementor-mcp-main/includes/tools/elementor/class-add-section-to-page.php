<?php
/**
 * Add Section to Page Tool
 *
 * Simplified tool for adding complete section structures to a page.
 * Supports both classic sections and modern containers.
 *
 * @package ElementorMCP\Tools\Elementor
 * @since 1.2.0
 */

namespace ElementorMCP\Tools\Elementor;

use ElementorMCP\Tools\Base_Tool;
use ElementorMCP\Tools\Traits\Element_Operations;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Section to Page Tool Class
 *
 * A convenience tool for quickly adding sections or containers
 * to the root level of an Elementor page.
 *
 * @since 1.2.0
 */
class Add_Section_To_Page extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_add_section_to_page';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Add Section to Page';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Add a complete section or container structure to a page. Can use pre-built section data from create_section or build inline. Supports positioning at top, bottom, or specific index.';
    }

    /**
     * Get tool category.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_category() {
        return 'elementor';
    }

    /**
     * Get tool keywords.
     *
     * @since 1.2.0
     * @return array
     */
    public function get_keywords() {
        return [ 'add', 'section', 'container', 'page', 'root', 'insert' ];
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
                'post_id'  => [
                    'type'        => 'integer',
                    'description' => 'The post ID to add the section to',
                    'minimum'     => 1,
                ],
                'section'  => [
                    'type'        => 'object',
                    'description' => 'Complete section/container structure. Can be from create_section tool or custom structure.',
                    'properties'  => [
                        'id'       => [
                            'type'        => 'string',
                            'description' => 'Element ID (auto-generated if not provided)',
                        ],
                        'elType'   => [
                            'type'        => 'string',
                            'description' => 'Must be "section" or "container"',
                            'enum'        => [ 'section', 'container' ],
                        ],
                        'settings' => [
                            'type'        => 'object',
                            'description' => 'Section/container settings',
                        ],
                        'elements' => [
                            'type'        => 'array',
                            'description' => 'Child elements (columns for section, any for container)',
                        ],
                    ],
                ],
                'position' => [
                    'type'        => 'integer',
                    'description' => 'Position in page (0 = top, -1 = bottom/append). Default is -1 (append).',
                    'default'     => -1,
                ],
                'columns'  => [
                    'type'        => 'array',
                    'description' => 'Alternative: Define columns inline instead of providing full section structure. Each column can have widgets.',
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'column_size'     => [
                                'type'        => 'integer',
                                'description' => 'Column width percentage (1-100)',
                                'minimum'     => 1,
                                'maximum'     => 100,
                                'default'     => 100,
                            ],
                            'column_settings' => [
                                'type'        => 'object',
                                'description' => 'Column settings',
                            ],
                            'widgets'         => [
                                'type'        => 'array',
                                'description' => 'Widgets in this column',
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
                'section_settings' => [
                    'type'        => 'object',
                    'description' => 'Section settings when using inline columns definition',
                    'default'     => new \stdClass(),
                ],
                'use_container' => [
                    'type'        => 'boolean',
                    'description' => 'Use modern container instead of section/column when building inline',
                    'default'     => false,
                ],
            ],
            'required'   => [ 'post_id' ],
        ];
    }

    /**
     * Execute the tool.
     *
     * @since 1.2.0
     * @param array $args Tool arguments.
     * @return array Result with success status and data.
     */
    public function execute( $args ) {
        $post_id          = absint( $args['post_id'] );
        $section          = isset( $args['section'] ) ? $args['section'] : null;
        $position         = isset( $args['position'] ) ? intval( $args['position'] ) : -1;
        $columns          = isset( $args['columns'] ) ? $args['columns'] : null;
        $section_settings = isset( $args['section_settings'] ) ? $args['section_settings'] : [];
        $use_container    = isset( $args['use_container'] ) ? (bool) $args['use_container'] : false;

        // Validate Elementor is active
        if ( ! $this->verify_elementor_active() ) {
            return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
        }

        // Validate post
        if ( ! $this->is_valid_elementor_post( $post_id ) ) {
            return $this->format_error(
                'Invalid post or not built with Elementor',
                'invalid_post'
            );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $this->format_error(
                'You do not have permission to edit this post',
                'permission_denied'
            );
        }

        // Must provide either section or columns
        if ( empty( $section ) && empty( $columns ) ) {
            return $this->format_error(
                'Must provide either "section" (complete structure) or "columns" (inline definition)',
                'missing_input'
            );
        }

        try {
            // Build section structure
            if ( ! empty( $section ) ) {
                // Use provided section structure
                $section_element = $this->normalize_section( $section );
            } else {
                // Build from inline columns definition
                $section_element = $this->build_section_from_columns(
                    $columns,
                    $section_settings,
                    $use_container
                );
            }

            // Validate section type
            $el_type = $section_element['elType'] ?? '';
            if ( ! in_array( $el_type, [ 'section', 'container' ], true ) ) {
                return $this->format_error(
                    sprintf( 'Root element must be section or container, got "%s"', $el_type ),
                    'invalid_element_type'
                );
            }

            // Get document
            $document = $this->get_document( $post_id );
            if ( ! $document ) {
                return $this->format_error( 'Document not found', 'document_not_found' );
            }

            // Get current elements
            $elements = $document->get_elements_data();
            if ( ! is_array( $elements ) ) {
                $elements = [];
            }

            // Insert section at position
            if ( $position < 0 || $position >= count( $elements ) ) {
                // Append
                $elements[] = $section_element;
                $final_position = count( $elements ) - 1;
            } elseif ( $position === 0 ) {
                // Prepend
                array_unshift( $elements, $section_element );
                $final_position = 0;
            } else {
                // Insert at specific position
                array_splice( $elements, $position, 0, [ $section_element ] );
                $final_position = $position;
            }

            // Save document
            $save_result = $this->save_document_and_invalidate( $document, $elements, $post_id );

            if ( is_wp_error( $save_result ) ) {
                return $this->format_error(
                    'Failed to save document: ' . $save_result->get_error_message(),
                    'save_failed'
                );
            }

            // Count elements added
            $element_count = 1 + $this->count_elements_recursive( $section_element['elements'] ?? [] );

            // Count widgets
            $widget_count = $this->count_widgets_recursive( $section_element );

            // Count columns
            $column_count = $this->count_columns( $section_element );

            return $this->format_success(
                [
                    'section_id'    => $section_element['id'],
                    'section_type'  => $el_type,
                    'position'      => $final_position,
                    'total_sections' => count( $elements ),
                    'elements_added' => $element_count,
                    'columns_added'  => $column_count,
                    'widgets_added'  => $widget_count,
                    'section'        => $section_element,
                ],
                sprintf(
                    'Successfully added %s with %d column(s) and %d widget(s) at position %d',
                    $el_type,
                    $column_count,
                    $widget_count,
                    $final_position
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to add section: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Normalize section structure and ensure IDs exist.
     *
     * @since 1.2.0
     * @param array $section Section data.
     * @return array Normalized section.
     */
    private function normalize_section( $section ) {
        // Ensure ID
        if ( empty( $section['id'] ) ) {
            $section['id'] = $this->generate_element_id();
        }

        // Ensure elType
        if ( empty( $section['elType'] ) ) {
            $section['elType'] = 'section';
        }

        // Ensure settings
        if ( ! isset( $section['settings'] ) ) {
            $section['settings'] = new \stdClass();
        }

        // Ensure elements array
        if ( ! isset( $section['elements'] ) ) {
            $section['elements'] = [];
        }

        // Recursively normalize children
        $section['elements'] = array_map(
            [ $this, 'normalize_element_recursive' ],
            $section['elements']
        );

        return $section;
    }

    /**
     * Recursively normalize an element.
     *
     * @since 1.2.0
     * @param array $element Element data.
     * @return array Normalized element.
     */
    private function normalize_element_recursive( $element ) {
        if ( empty( $element['id'] ) ) {
            $element['id'] = $this->generate_element_id();
        }

        if ( ! isset( $element['settings'] ) ) {
            $element['settings'] = new \stdClass();
        }

        if ( ! empty( $element['elements'] ) ) {
            $element['elements'] = array_map(
                [ $this, 'normalize_element_recursive' ],
                $element['elements']
            );
        } elseif ( ! isset( $element['elements'] ) ) {
            $element['elements'] = [];
        }

        return $element;
    }

    /**
     * Build section structure from inline columns definition.
     *
     * @since 1.2.0
     * @param array $columns          Column definitions.
     * @param array $section_settings Section settings.
     * @param bool  $use_container    Use container instead of section.
     * @return array Section/container element.
     */
    private function build_section_from_columns( $columns, $section_settings, $use_container ) {
        if ( $use_container ) {
            return $this->build_container_structure( $columns, $section_settings );
        }

        return $this->build_section_structure( $columns, $section_settings );
    }

    /**
     * Build classic section/column structure.
     *
     * @since 1.2.0
     * @param array $columns          Column definitions.
     * @param array $section_settings Section settings.
     * @return array Section element.
     */
    private function build_section_structure( $columns, $section_settings ) {
        $section_id = $this->generate_element_id();
        $column_elements = [];

        foreach ( $columns as $column_def ) {
            $column_elements[] = $this->build_column( $column_def );
        }

        return [
            'id'       => $section_id,
            'elType'   => 'section',
            'settings' => ! empty( $section_settings ) ? $section_settings : new \stdClass(),
            'elements' => $column_elements,
        ];
    }

    /**
     * Build modern container structure.
     *
     * @since 1.2.0
     * @param array $columns            Column definitions.
     * @param array $container_settings Container settings.
     * @return array Container element.
     */
    private function build_container_structure( $columns, $container_settings ) {
        $container_id = $this->generate_element_id();
        $elements = [];

        if ( count( $columns ) === 1 ) {
            // Single column - widgets go directly in container
            if ( ! empty( $columns[0]['widgets'] ) ) {
                foreach ( $columns[0]['widgets'] as $widget_def ) {
                    $elements[] = $this->build_widget( $widget_def );
                }
            }
        } else {
            // Multiple columns - nested containers
            foreach ( $columns as $column_def ) {
                $nested_settings = [
                    'flex_grow' => isset( $column_def['column_size'] ) ? (string) $column_def['column_size'] : '1',
                ];

                if ( ! empty( $column_def['column_settings'] ) ) {
                    $nested_settings = array_merge( $nested_settings, $column_def['column_settings'] );
                }

                $nested_container = [
                    'id'       => $this->generate_element_id(),
                    'elType'   => 'container',
                    'settings' => $nested_settings,
                    'elements' => [],
                ];

                if ( ! empty( $column_def['widgets'] ) ) {
                    foreach ( $column_def['widgets'] as $widget_def ) {
                        $nested_container['elements'][] = $this->build_widget( $widget_def );
                    }
                }

                $elements[] = $nested_container;
            }

            // Set parent container to row layout
            if ( ! isset( $container_settings['flex_direction'] ) ) {
                $container_settings['flex_direction'] = 'row';
            }
        }

        return [
            'id'       => $container_id,
            'elType'   => 'container',
            'settings' => ! empty( $container_settings ) ? $container_settings : new \stdClass(),
            'elements' => $elements,
        ];
    }

    /**
     * Build a column element.
     *
     * @since 1.2.0
     * @param array $column_def Column definition.
     * @return array Column element.
     */
    private function build_column( $column_def ) {
        $column_id = $this->generate_element_id();
        $column_size = isset( $column_def['column_size'] ) ? $column_def['column_size'] : 100;
        $column_settings = isset( $column_def['column_settings'] ) ? $column_def['column_settings'] : [];

        $column_settings['_column_size'] = $column_size;

        $widget_elements = [];
        if ( ! empty( $column_def['widgets'] ) ) {
            foreach ( $column_def['widgets'] as $widget_def ) {
                $widget_elements[] = $this->build_widget( $widget_def );
            }
        }

        return [
            'id'       => $column_id,
            'elType'   => 'column',
            'settings' => $column_settings,
            'elements' => $widget_elements,
        ];
    }

    /**
     * Build a widget element.
     *
     * @since 1.2.0
     * @param array $widget_def Widget definition.
     * @return array Widget element.
     */
    private function build_widget( $widget_def ) {
        $widget_id = $this->generate_element_id();
        $widget_type = $widget_def['widget_type'] ?? 'text-editor';
        $settings = isset( $widget_def['settings'] ) ? $widget_def['settings'] : [];

        return [
            'id'         => $widget_id,
            'elType'     => 'widget',
            'widgetType' => $widget_type,
            'settings'   => ! empty( $settings ) ? $settings : new \stdClass(),
            'elements'   => [],
        ];
    }

    /**
     * Count widgets in element tree.
     *
     * @since 1.2.0
     * @param array $element Element to count widgets in.
     * @return int Widget count.
     */
    private function count_widgets_recursive( $element ) {
        $count = 0;

        if ( ( $element['elType'] ?? '' ) === 'widget' ) {
            $count = 1;
        }

        if ( ! empty( $element['elements'] ) ) {
            foreach ( $element['elements'] as $child ) {
                $count += $this->count_widgets_recursive( $child );
            }
        }

        return $count;
    }

    /**
     * Count columns in section/container.
     *
     * @since 1.2.0
     * @param array $element Section or container element.
     * @return int Column count.
     */
    private function count_columns( $element ) {
        $count = 0;

        if ( empty( $element['elements'] ) ) {
            return 0;
        }

        foreach ( $element['elements'] as $child ) {
            $child_type = $child['elType'] ?? '';
            if ( $child_type === 'column' ) {
                $count++;
            } elseif ( $child_type === 'container' ) {
                // For nested containers in flex layout, count as column
                $count++;
            }
        }

        return $count;
    }
}
