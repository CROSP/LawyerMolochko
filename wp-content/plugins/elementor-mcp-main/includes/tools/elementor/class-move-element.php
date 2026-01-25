<?php
/**
 * Move Element Tool
 *
 * Moves an element to a new position or parent within the document.
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
 * Move Element Tool Class
 *
 * Allows reordering elements within the same parent or moving
 * elements to different parents in the document hierarchy.
 *
 * @since 1.2.0
 */
class Move_Element extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_move_element';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Move Element';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Move an element to a new position within the same parent or to a different parent. Validates parent-child relationships and prevents circular references.';
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
        return [ 'move', 'reorder', 'reparent', 'element', 'position' ];
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
                'post_id'       => [
                    'type'        => 'integer',
                    'description' => 'The post ID containing the element',
                    'minimum'     => 1,
                ],
                'element_id'    => [
                    'type'        => 'string',
                    'description' => 'The element ID to move',
                    'minLength'   => 1,
                ],
                'new_parent_id' => [
                    'type'        => 'string',
                    'description' => 'New parent element ID. Empty/null for root level (sections/containers only).',
                ],
                'new_position'  => [
                    'type'        => 'integer',
                    'description' => 'New position index within the parent (0-based). Use -1 to append at the end.',
                    'default'     => -1,
                ],
            ],
            'required'   => [ 'post_id', 'element_id' ],
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
        $post_id       = absint( $args['post_id'] );
        $element_id    = sanitize_text_field( $args['element_id'] );
        $new_parent_id = isset( $args['new_parent_id'] ) ? sanitize_text_field( $args['new_parent_id'] ) : null;
        $new_position  = isset( $args['new_position'] ) ? intval( $args['new_position'] ) : -1;

        // Handle empty string as null for root level
        if ( $new_parent_id === '' ) {
            $new_parent_id = null;
        }

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

        // Prevent moving to self
        if ( $element_id === $new_parent_id ) {
            return $this->format_error(
                'Cannot move element to itself',
                'circular_reference'
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
                return $this->format_error(
                    'Document has no elements',
                    'no_elements'
                );
            }

            // Find element to move
            $element_path = [];
            $element      = $this->find_element_recursive( $elements, $element_id, $element_path );

            if ( ! $element ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Check for circular reference (can't move to own descendant)
            if ( ! empty( $new_parent_id ) && $this->is_descendant_of( $elements, $element_id, $new_parent_id ) ) {
                return $this->format_error(
                    'Cannot move element to its own descendant (circular reference)',
                    'circular_reference'
                );
            }

            // Get current parent info
            $current_parent_info = $this->find_parent_and_index( $elements, $element_id );
            $current_parent_id   = $current_parent_info['parent'] ? $current_parent_info['parent']['id'] : null;
            $current_is_root     = $current_parent_info['is_root'];
            $current_index       = $current_parent_info['index'];

            // Get new parent element for validation
            $new_parent_element = null;
            if ( ! empty( $new_parent_id ) ) {
                $new_parent_element = $this->find_element_recursive( $elements, $new_parent_id );
                if ( ! $new_parent_element ) {
                    return $this->format_error(
                        sprintf( 'Target parent "%s" not found', $new_parent_id ),
                        'parent_not_found'
                    );
                }
            }

            // Validate parent-child relationship
            $validation_error = null;
            if ( ! $this->validate_parent_child_relationship( $new_parent_element, $element, $validation_error ) ) {
                return $this->format_error(
                    $validation_error['message'],
                    $validation_error['code']
                );
            }

            // Check if this is just a reorder within the same parent
            $same_parent = ( $current_parent_id === $new_parent_id ) ||
                           ( $current_is_root && empty( $new_parent_id ) );

            // Remove element from current location
            $removed = $this->remove_element_recursive( $elements, $element_id );
            if ( ! $removed ) {
                return $this->format_error(
                    'Failed to remove element from current location',
                    'remove_failed'
                );
            }

            // Adjust position if reordering within same parent
            if ( $same_parent && $new_position > $current_index && $new_position > 0 ) {
                // Since we removed the element, indices shifted down
                $new_position--;
            }

            // Insert at new location
            $inserted = $this->insert_element_at_parent(
                $elements,
                $new_parent_id,
                $removed,
                $new_position
            );

            if ( ! $inserted ) {
                return $this->format_error(
                    'Failed to insert element at new location',
                    'insert_failed'
                );
            }

            // Save document
            $save_result = $this->save_document_and_invalidate( $document, $elements, $post_id );

            if ( is_wp_error( $save_result ) ) {
                return $this->format_error(
                    'Failed to save document: ' . $save_result->get_error_message(),
                    'save_failed'
                );
            }

            // Get final position and path
            $final_position = $this->get_final_position( $elements, $element_id, $new_parent_id );
            $new_path       = $this->get_element_path( $elements, $element_id );

            return $this->format_success(
                [
                    'element_id'        => $element_id,
                    'from_parent'       => $current_parent_id,
                    'to_parent'         => $new_parent_id,
                    'from_position'     => $current_index,
                    'to_position'       => $final_position,
                    'was_root'          => $current_is_root,
                    'is_root'           => empty( $new_parent_id ),
                    'same_parent'       => $same_parent,
                    'path'              => $new_path,
                    'element'           => [
                        'id'         => $removed['id'],
                        'elType'     => $removed['elType'],
                        'widgetType' => $removed['widgetType'] ?? null,
                    ],
                ],
                sprintf(
                    'Successfully moved %s%s from %s to %s',
                    $removed['elType'],
                    isset( $removed['widgetType'] ) ? ' (' . $removed['widgetType'] . ')' : '',
                    $current_parent_id ?? 'root',
                    $new_parent_id ?? 'root'
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to move element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Get final position of element after move.
     *
     * @since 1.2.0
     * @param array       $elements   The elements array.
     * @param string      $element_id The element ID.
     * @param string|null $parent_id  The parent ID (null for root).
     * @return int|null The position or null.
     */
    private function get_final_position( $elements, $element_id, $parent_id = null ) {
        if ( empty( $parent_id ) ) {
            // Root level
            foreach ( $elements as $index => $element ) {
                if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                    return $index;
                }
            }
            return null;
        }

        // Find in parent
        $parent = $this->find_element_recursive( $elements, $parent_id );
        if ( ! $parent || empty( $parent['elements'] ) ) {
            return null;
        }

        foreach ( $parent['elements'] as $index => $child ) {
            if ( isset( $child['id'] ) && $child['id'] === $element_id ) {
                return $index;
            }
        }

        return null;
    }
}
