<?php
/**
 * Get Element Path Tool
 *
 * Retrieves the full ancestry path of an element in the document.
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
 * Get Element Path Tool Class
 *
 * Returns the complete path from document root to a specific element,
 * including all ancestor elements with their types and indices.
 *
 * @since 1.2.0
 */
class Get_Element_Path extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_get_element_path';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Get Element Path';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Get the full ancestry path of an element from document root. Returns all parent elements with their types, IDs, and positions.';
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
        return [ 'path', 'ancestry', 'parent', 'location', 'navigate', 'hierarchy' ];
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
                    'description' => 'The post ID containing the element',
                    'minimum'     => 1,
                ],
                'element_id' => [
                    'type'        => 'string',
                    'description' => 'The element ID to find path for',
                    'minLength'   => 1,
                ],
                'include_settings' => [
                    'type'        => 'boolean',
                    'description' => 'Include settings for each ancestor',
                    'default'     => false,
                ],
            ],
            'required' => [ 'post_id', 'element_id' ],
        ];
    }

    /**
     * Execute the tool.
     *
     * @since 1.2.0
     * @param array $args Tool arguments.
     * @return array Result with path information.
     */
    public function execute( $args ) {
        $post_id          = absint( $args['post_id'] );
        $element_id       = sanitize_text_field( $args['element_id'] );
        $include_settings = isset( $args['include_settings'] ) ? (bool) $args['include_settings'] : false;

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

        try {
            // Get document
            $document = $this->get_document( $post_id );
            if ( ! $document ) {
                return $this->format_error( 'Document not found', 'document_not_found' );
            }

            // Get elements
            $elements = $document->get_elements_data();
            if ( ! is_array( $elements ) || empty( $elements ) ) {
                return $this->format_error(
                    'Document has no elements',
                    'no_elements'
                );
            }

            // Find element and build path
            $path = [];
            $element = $this->find_with_path( $elements, $element_id, $path, $include_settings );

            if ( ! $element ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Build breadcrumb string
            $breadcrumb = $this->build_breadcrumb( $path );

            // Get parent info
            $parent_id = null;
            $parent_type = null;
            if ( count( $path ) > 1 ) {
                $parent = $path[ count( $path ) - 2 ];
                $parent_id = $parent['id'];
                $parent_type = $parent['type'];
            }

            return $this->format_success(
                [
                    'element_id'  => $element_id,
                    'element'     => [
                        'id'         => $element['id'],
                        'elType'     => $element['elType'],
                        'widgetType' => $element['widgetType'] ?? null,
                    ],
                    'depth'       => count( $path ) - 1,
                    'is_root'     => count( $path ) === 1,
                    'parent_id'   => $parent_id,
                    'parent_type' => $parent_type,
                    'path'        => $path,
                    'breadcrumb'  => $breadcrumb,
                    'path_ids'    => array_column( $path, 'id' ),
                ],
                sprintf(
                    'Found element at depth %d: %s',
                    count( $path ) - 1,
                    $breadcrumb
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to get element path: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Find element and build detailed path.
     *
     * @since 1.2.0
     * @param array  $elements         Elements to search.
     * @param string $element_id       Element ID to find.
     * @param array  &$path            Reference to path array.
     * @param bool   $include_settings Include settings in path.
     * @param int    $index            Current index in parent.
     * @return array|null Found element or null.
     */
    private function find_with_path( $elements, $element_id, &$path, $include_settings, $index = 0 ) {
        foreach ( $elements as $idx => $element ) {
            $el_id = $element['id'] ?? null;

            // Build path entry
            $path_entry = [
                'id'          => $el_id,
                'type'        => $element['elType'] ?? 'unknown',
                'index'       => $idx,
                'widgetType'  => $element['widgetType'] ?? null,
                'has_children' => ! empty( $element['elements'] ),
                'children_count' => count( $element['elements'] ?? [] ),
            ];

            if ( $include_settings && isset( $element['settings'] ) ) {
                $path_entry['settings'] = $element['settings'];
            }

            // Check if this is the target
            if ( $el_id === $element_id ) {
                $path[] = $path_entry;
                return $element;
            }

            // Search children
            if ( ! empty( $element['elements'] ) ) {
                $path[] = $path_entry;
                $found = $this->find_with_path(
                    $element['elements'],
                    $element_id,
                    $path,
                    $include_settings,
                    $idx
                );

                if ( $found ) {
                    return $found;
                }

                // Not found in this branch, remove from path
                array_pop( $path );
            }
        }

        return null;
    }

    /**
     * Build human-readable breadcrumb string.
     *
     * @since 1.2.0
     * @param array $path Path array.
     * @return string Breadcrumb string.
     */
    private function build_breadcrumb( $path ) {
        $parts = [];

        foreach ( $path as $node ) {
            $type = $node['type'];

            if ( $type === 'widget' && isset( $node['widgetType'] ) ) {
                $parts[] = sprintf( '%s[%d]', $node['widgetType'], $node['index'] );
            } else {
                $parts[] = sprintf( '%s[%d]', $type, $node['index'] );
            }
        }

        return implode( ' > ', $parts );
    }
}
