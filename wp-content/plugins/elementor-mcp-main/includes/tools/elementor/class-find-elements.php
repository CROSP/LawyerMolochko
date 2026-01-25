<?php
/**
 * Find Elements Tool
 *
 * Search for elements matching specific criteria within a document.
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
 * Find Elements Tool Class
 *
 * Provides powerful search capabilities for finding elements
 * by type, widget type, settings values, and more.
 *
 * @since 1.2.0
 */
class Find_Elements extends Base_Tool {

    use Element_Operations;

    /**
     * Default result limit.
     *
     * @since 1.2.0
     * @var int
     */
    const DEFAULT_LIMIT = 100;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_find_elements';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Find Elements';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Search for elements matching specific criteria: element type, widget type, settings values, or custom conditions. Returns matching elements with their paths.';
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
        return [ 'find', 'search', 'query', 'filter', 'elements', 'widgets' ];
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
                'post_id' => [
                    'type'        => 'integer',
                    'description' => 'The post ID to search within',
                    'minimum'     => 1,
                ],
                'criteria' => [
                    'type'        => 'object',
                    'description' => 'Search criteria (all conditions are AND-ed together)',
                    'properties'  => [
                        'elType' => [
                            'type'        => 'string',
                            'description' => 'Element type to match',
                            'enum'        => [ 'section', 'column', 'container', 'widget' ],
                        ],
                        'widgetType' => [
                            'type'        => 'string',
                            'description' => 'Widget type to match (only for widgets)',
                        ],
                        'widgetTypes' => [
                            'type'        => 'array',
                            'description' => 'Multiple widget types to match (OR)',
                            'items'       => [ 'type' => 'string' ],
                        ],
                        'has_setting' => [
                            'type'        => 'string',
                            'description' => 'Setting key that must exist (non-empty)',
                        ],
                        'has_settings' => [
                            'type'        => 'array',
                            'description' => 'Multiple setting keys that must all exist',
                            'items'       => [ 'type' => 'string' ],
                        ],
                        'setting_equals' => [
                            'type'        => 'object',
                            'description' => 'Settings that must match exactly (key-value pairs)',
                        ],
                        'setting_contains' => [
                            'type'        => 'object',
                            'description' => 'Settings where value must contain substring (for strings)',
                        ],
                        'setting_matches' => [
                            'type'        => 'object',
                            'description' => 'Settings where value must match regex pattern',
                        ],
                        'has_children' => [
                            'type'        => 'boolean',
                            'description' => 'Element must have (true) or not have (false) children',
                        ],
                        'min_children' => [
                            'type'        => 'integer',
                            'description' => 'Minimum number of direct children',
                            'minimum'     => 0,
                        ],
                        'max_children' => [
                            'type'        => 'integer',
                            'description' => 'Maximum number of direct children',
                            'minimum'     => 0,
                        ],
                        'is_empty' => [
                            'type'        => 'boolean',
                            'description' => 'Find empty containers/columns (no children)',
                        ],
                        'custom_css_contains' => [
                            'type'        => 'string',
                            'description' => 'Search within custom CSS field',
                        ],
                    ],
                ],
                'include_path' => [
                    'type'        => 'boolean',
                    'description' => 'Include element path (ancestry) in results',
                    'default'     => true,
                ],
                'include_settings' => [
                    'type'        => 'boolean',
                    'description' => 'Include full settings in results',
                    'default'     => false,
                ],
                'include_children' => [
                    'type'        => 'boolean',
                    'description' => 'Include children in results',
                    'default'     => false,
                ],
                'limit' => [
                    'type'        => 'integer',
                    'description' => 'Maximum number of results',
                    'default'     => self::DEFAULT_LIMIT,
                    'minimum'     => 1,
                    'maximum'     => 500,
                ],
            ],
            'required' => [ 'post_id', 'criteria' ],
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
        $criteria         = $args['criteria'];
        $include_path     = isset( $args['include_path'] ) ? (bool) $args['include_path'] : true;
        $include_settings = isset( $args['include_settings'] ) ? (bool) $args['include_settings'] : false;
        $include_children = isset( $args['include_children'] ) ? (bool) $args['include_children'] : false;
        $limit            = isset( $args['limit'] ) ? absint( $args['limit'] ) : self::DEFAULT_LIMIT;

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

        // Validate criteria is not empty
        if ( empty( $criteria ) ) {
            return $this->format_error(
                'Search criteria cannot be empty',
                'empty_criteria'
            );
        }

        try {
            // Get document
            $document = $this->get_document( $post_id );
            if ( ! $document ) {
                return $this->format_error( 'Document not found', 'document_not_found' );
            }

            // Get current elements
            $elements = $document->get_elements_data();
            if ( ! is_array( $elements ) || empty( $elements ) ) {
                return $this->format_success(
                    [
                        'count'    => 0,
                        'elements' => [],
                        'criteria' => $criteria,
                    ],
                    'Document has no elements'
                );
            }

            // Search for matching elements
            $matches = [];
            $this->search_elements_recursive(
                $elements,
                $criteria,
                $matches,
                [],
                $limit
            );

            // Format results
            $results = [];
            foreach ( $matches as $match ) {
                $result = [
                    'id'         => $match['element']['id'],
                    'elType'     => $match['element']['elType'],
                    'widgetType' => $match['element']['widgetType'] ?? null,
                ];

                if ( $include_path ) {
                    $result['path'] = $match['path'];
                    $result['depth'] = count( $match['path'] );
                }

                if ( $include_settings ) {
                    $result['settings'] = $match['element']['settings'] ?? [];
                }

                if ( $include_children ) {
                    $result['elements'] = $match['element']['elements'] ?? [];
                    $result['children_count'] = count( $result['elements'] );
                } else {
                    $result['children_count'] = count( $match['element']['elements'] ?? [] );
                }

                $results[] = $result;
            }

            return $this->format_success(
                [
                    'count'         => count( $results ),
                    'limit'         => $limit,
                    'limit_reached' => count( $results ) >= $limit,
                    'criteria'      => $criteria,
                    'elements'      => $results,
                ],
                sprintf( 'Found %d matching element(s)', count( $results ) )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to search elements: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Recursively search for matching elements.
     *
     * @since 1.2.0
     * @param array $elements The elements array to search.
     * @param array $criteria Search criteria.
     * @param array &$matches Reference to matches array.
     * @param array $path     Current path.
     * @param int   $limit    Maximum results.
     */
    private function search_elements_recursive( $elements, $criteria, &$matches, $path, $limit ) {
        foreach ( $elements as $index => $element ) {
            // Stop if limit reached
            if ( count( $matches ) >= $limit ) {
                return;
            }

            $element_id = $element['id'] ?? 'unknown';
            $current_path = array_merge( $path, [ $element_id ] );

            // Check if element matches criteria
            if ( $this->element_matches_criteria( $element, $criteria ) ) {
                $matches[] = [
                    'element' => $element,
                    'path'    => $current_path,
                    'index'   => $index,
                ];
            }

            // Search children
            if ( ! empty( $element['elements'] ) ) {
                $this->search_elements_recursive(
                    $element['elements'],
                    $criteria,
                    $matches,
                    $current_path,
                    $limit
                );
            }
        }
    }

    /**
     * Check if an element matches the search criteria.
     *
     * @since 1.2.0
     * @param array $element  The element to check.
     * @param array $criteria Search criteria.
     * @return bool True if matches.
     */
    private function element_matches_criteria( $element, $criteria ) {
        // Check elType
        if ( isset( $criteria['elType'] ) ) {
            if ( ( $element['elType'] ?? '' ) !== $criteria['elType'] ) {
                return false;
            }
        }

        // Check widgetType (single)
        if ( isset( $criteria['widgetType'] ) ) {
            if ( ( $element['widgetType'] ?? '' ) !== $criteria['widgetType'] ) {
                return false;
            }
        }

        // Check widgetTypes (multiple - OR)
        if ( isset( $criteria['widgetTypes'] ) && is_array( $criteria['widgetTypes'] ) ) {
            $widget_type = $element['widgetType'] ?? '';
            if ( ! in_array( $widget_type, $criteria['widgetTypes'], true ) ) {
                return false;
            }
        }

        // Check has_setting (single)
        if ( isset( $criteria['has_setting'] ) ) {
            $settings = $element['settings'] ?? [];
            if ( ! $this->has_non_empty_setting( $settings, $criteria['has_setting'] ) ) {
                return false;
            }
        }

        // Check has_settings (multiple - AND)
        if ( isset( $criteria['has_settings'] ) && is_array( $criteria['has_settings'] ) ) {
            $settings = $element['settings'] ?? [];
            foreach ( $criteria['has_settings'] as $setting_key ) {
                if ( ! $this->has_non_empty_setting( $settings, $setting_key ) ) {
                    return false;
                }
            }
        }

        // Check setting_equals (exact match)
        if ( isset( $criteria['setting_equals'] ) && is_array( $criteria['setting_equals'] ) ) {
            $settings = $element['settings'] ?? [];
            foreach ( $criteria['setting_equals'] as $key => $value ) {
                $actual_value = $this->get_nested_value( $settings, $key );
                if ( $actual_value !== $value ) {
                    return false;
                }
            }
        }

        // Check setting_contains (substring match)
        if ( isset( $criteria['setting_contains'] ) && is_array( $criteria['setting_contains'] ) ) {
            $settings = $element['settings'] ?? [];
            foreach ( $criteria['setting_contains'] as $key => $substring ) {
                $actual_value = $this->get_nested_value( $settings, $key );
                if ( ! is_string( $actual_value ) || strpos( $actual_value, $substring ) === false ) {
                    return false;
                }
            }
        }

        // Check setting_matches (regex)
        if ( isset( $criteria['setting_matches'] ) && is_array( $criteria['setting_matches'] ) ) {
            $settings = $element['settings'] ?? [];
            foreach ( $criteria['setting_matches'] as $key => $pattern ) {
                $actual_value = $this->get_nested_value( $settings, $key );
                if ( ! is_string( $actual_value ) || ! preg_match( $pattern, $actual_value ) ) {
                    return false;
                }
            }
        }

        // Check has_children
        if ( isset( $criteria['has_children'] ) ) {
            $has_children = ! empty( $element['elements'] );
            if ( $criteria['has_children'] !== $has_children ) {
                return false;
            }
        }

        // Check min_children
        if ( isset( $criteria['min_children'] ) ) {
            $children_count = count( $element['elements'] ?? [] );
            if ( $children_count < $criteria['min_children'] ) {
                return false;
            }
        }

        // Check max_children
        if ( isset( $criteria['max_children'] ) ) {
            $children_count = count( $element['elements'] ?? [] );
            if ( $children_count > $criteria['max_children'] ) {
                return false;
            }
        }

        // Check is_empty (shorthand for has_children: false for containers)
        if ( isset( $criteria['is_empty'] ) && $criteria['is_empty'] ) {
            $el_type = $element['elType'] ?? '';
            if ( in_array( $el_type, [ 'section', 'column', 'container' ], true ) ) {
                if ( ! empty( $element['elements'] ) ) {
                    return false;
                }
            }
        }

        // Check custom_css_contains
        if ( isset( $criteria['custom_css_contains'] ) ) {
            $custom_css = $element['settings']['custom_css'] ?? '';
            if ( strpos( $custom_css, $criteria['custom_css_contains'] ) === false ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a setting exists and is non-empty.
     *
     * @since 1.2.0
     * @param array  $settings    Settings array.
     * @param string $setting_key Setting key (supports dot notation).
     * @return bool True if exists and non-empty.
     */
    private function has_non_empty_setting( $settings, $setting_key ) {
        $value = $this->get_nested_value( $settings, $setting_key );

        if ( $value === null ) {
            return false;
        }

        if ( is_string( $value ) && trim( $value ) === '' ) {
            return false;
        }

        if ( is_array( $value ) && empty( $value ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get a nested value using dot notation.
     *
     * @since 1.2.0
     * @param array  $array Array to search.
     * @param string $key   Dot-notation key.
     * @return mixed|null Value or null if not found.
     */
    private function get_nested_value( $array, $key ) {
        $keys = explode( '.', $key );

        foreach ( $keys as $k ) {
            if ( ! is_array( $array ) || ! isset( $array[ $k ] ) ) {
                return null;
            }
            $array = $array[ $k ];
        }

        return $array;
    }
}
