<?php
/**
 * Get Element Siblings Tool
 *
 * Retrieves siblings (elements at the same level) of a specific element.
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
 * Get Element Siblings Tool Class
 *
 * Returns all elements at the same hierarchical level as the target element,
 * including information about previous and next siblings.
 *
 * @since 1.2.0
 */
class Get_Element_Siblings extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_get_element_siblings';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Get Element Siblings';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Get all sibling elements at the same level as the specified element. Returns previous, next, and all siblings with their positions.';
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
        return [ 'siblings', 'adjacent', 'neighbor', 'previous', 'next', 'same level' ];
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
                    'description' => 'The element ID to find siblings for',
                    'minLength'   => 1,
                ],
                'include_self' => [
                    'type'        => 'boolean',
                    'description' => 'Include the target element in the siblings list',
                    'default'     => true,
                ],
                'include_settings' => [
                    'type'        => 'boolean',
                    'description' => 'Include settings for each sibling',
                    'default'     => false,
                ],
                'include_children' => [
                    'type'        => 'boolean',
                    'description' => 'Include child elements for each sibling',
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
     * @return array Result with siblings information.
     */
    public function execute( $args ) {
        $post_id           = absint( $args['post_id'] );
        $element_id        = sanitize_text_field( $args['element_id'] );
        $include_self      = isset( $args['include_self'] ) ? (bool) $args['include_self'] : true;
        $include_settings  = isset( $args['include_settings'] ) ? (bool) $args['include_settings'] : false;
        $include_children  = isset( $args['include_children'] ) ? (bool) $args['include_children'] : false;

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

            // Find parent and siblings
            $result = $this->find_siblings( $elements, $element_id );

            if ( ! $result ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            $siblings_array = $result['siblings'];
            $target_index = $result['target_index'];
            $parent_id = $result['parent_id'];
            $is_root = $result['is_root'];

            // Format siblings
            $formatted_siblings = [];
            foreach ( $siblings_array as $index => $sibling ) {
                // Skip self if not including
                if ( ! $include_self && $sibling['id'] === $element_id ) {
                    continue;
                }

                $sibling_info = [
                    'id'            => $sibling['id'],
                    'elType'        => $sibling['elType'],
                    'widgetType'    => $sibling['widgetType'] ?? null,
                    'index'         => $index,
                    'is_target'     => $sibling['id'] === $element_id,
                    'is_first'      => $index === 0,
                    'is_last'       => $index === count( $siblings_array ) - 1,
                    'children_count' => count( $sibling['elements'] ?? [] ),
                ];

                if ( $include_settings ) {
                    $sibling_info['settings'] = $sibling['settings'] ?? [];
                }

                if ( $include_children ) {
                    $sibling_info['elements'] = $sibling['elements'] ?? [];
                }

                $formatted_siblings[] = $sibling_info;
            }

            // Get previous and next
            $previous = null;
            $next = null;

            if ( $target_index > 0 ) {
                $prev_sibling = $siblings_array[ $target_index - 1 ];
                $previous = [
                    'id'         => $prev_sibling['id'],
                    'elType'     => $prev_sibling['elType'],
                    'widgetType' => $prev_sibling['widgetType'] ?? null,
                    'index'      => $target_index - 1,
                ];
            }

            if ( $target_index < count( $siblings_array ) - 1 ) {
                $next_sibling = $siblings_array[ $target_index + 1 ];
                $next = [
                    'id'         => $next_sibling['id'],
                    'elType'     => $next_sibling['elType'],
                    'widgetType' => $next_sibling['widgetType'] ?? null,
                    'index'      => $target_index + 1,
                ];
            }

            // Calculate sibling count (excluding self if needed)
            $sibling_count = $include_self ? count( $siblings_array ) : count( $siblings_array ) - 1;

            return $this->format_success(
                [
                    'element_id'     => $element_id,
                    'parent_id'      => $parent_id,
                    'is_root_level'  => $is_root,
                    'position'       => $target_index,
                    'total_siblings' => count( $siblings_array ),
                    'sibling_count'  => $sibling_count,
                    'is_first'       => $target_index === 0,
                    'is_last'        => $target_index === count( $siblings_array ) - 1,
                    'is_only_child'  => count( $siblings_array ) === 1,
                    'previous'       => $previous,
                    'next'           => $next,
                    'siblings'       => $formatted_siblings,
                ],
                sprintf(
                    'Found %d sibling(s) at %s (position %d of %d)',
                    $sibling_count,
                    $is_root ? 'root level' : 'parent ' . $parent_id,
                    $target_index + 1,
                    count( $siblings_array )
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to get element siblings: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Find siblings of an element.
     *
     * @since 1.2.0
     * @param array  $elements   Elements to search.
     * @param string $element_id Element ID to find siblings for.
     * @param array  $parent     Current parent (for recursion).
     * @return array|null Result with siblings array and info, or null if not found.
     */
    private function find_siblings( $elements, $element_id, $parent = null ) {
        // Check if element is at this level
        foreach ( $elements as $index => $element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                return [
                    'siblings'     => $elements,
                    'target_index' => $index,
                    'parent_id'    => $parent ? $parent['id'] : null,
                    'is_root'      => $parent === null,
                ];
            }
        }

        // Search in children
        foreach ( $elements as $element ) {
            if ( ! empty( $element['elements'] ) ) {
                $result = $this->find_siblings( $element['elements'], $element_id, $element );
                if ( $result ) {
                    return $result;
                }
            }
        }

        return null;
    }
}
