<?php
/**
 * Duplicate Element Tool
 *
 * Creates a copy of an element with new unique IDs for itself and all children.
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
 * Duplicate Element Tool Class
 *
 * Clones an existing element and optionally inserts it at a specified location.
 * All IDs are regenerated to ensure uniqueness.
 *
 * @since 1.2.0
 */
class Duplicate_Element extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_duplicate_element';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Duplicate Element';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Create a copy of an element (and all children) with new unique IDs. Can insert after original or at a custom location.';
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
        return [ 'duplicate', 'copy', 'clone', 'element', 'widget', 'section' ];
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
                'post_id'          => [
                    'type'        => 'integer',
                    'description' => 'The post ID containing the element',
                    'minimum'     => 1,
                ],
                'element_id'       => [
                    'type'        => 'string',
                    'description' => 'The element ID to duplicate',
                    'minLength'   => 1,
                ],
                'insert_after'     => [
                    'type'        => 'boolean',
                    'description' => 'Insert the copy immediately after the original element',
                    'default'     => true,
                ],
                'target_parent_id' => [
                    'type'        => 'string',
                    'description' => 'Alternative parent ID for the copy (overrides insert_after). Leave empty to use same parent as original.',
                ],
                'target_position'  => [
                    'type'        => 'integer',
                    'description' => 'Position within target parent. Only used if target_parent_id is specified.',
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
        $post_id          = absint( $args['post_id'] );
        $element_id       = sanitize_text_field( $args['element_id'] );
        $insert_after     = isset( $args['insert_after'] ) ? (bool) $args['insert_after'] : true;
        $target_parent_id = isset( $args['target_parent_id'] ) ? sanitize_text_field( $args['target_parent_id'] ) : null;
        $target_position  = isset( $args['target_position'] ) ? intval( $args['target_position'] ) : -1;

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

            // Find original element
            $original_path = [];
            $original      = $this->find_element_recursive( $elements, $element_id, $original_path );

            if ( ! $original ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Clone element with new IDs
            $clone = $this->regenerate_ids_recursive( $original );

            // Track original ID mapping for reference
            $id_mapping = $this->build_id_mapping( $original, $clone );

            // Determine where to insert the clone
            $parent_info   = $this->find_parent_and_index( $elements, $element_id );
            $is_root       = $parent_info['is_root'];
            $original_idx  = $parent_info['index'];
            $original_parent = $parent_info['parent'];

            if ( ! empty( $target_parent_id ) ) {
                // Insert at specified target parent
                $target_parent = $this->find_element_recursive( $elements, $target_parent_id );
                if ( ! $target_parent ) {
                    return $this->format_error(
                        sprintf( 'Target parent "%s" not found', $target_parent_id ),
                        'parent_not_found'
                    );
                }

                // Validate parent-child relationship
                $validation_error = null;
                if ( ! $this->validate_parent_child_relationship( $target_parent, $clone, $validation_error ) ) {
                    return $this->format_error(
                        $validation_error['message'],
                        $validation_error['code']
                    );
                }

                $inserted = $this->insert_element_at_parent(
                    $elements,
                    $target_parent_id,
                    $clone,
                    $target_position
                );

                $final_parent_id = $target_parent_id;
            } elseif ( $insert_after ) {
                // Insert immediately after original
                $insert_position = $original_idx + 1;

                if ( $is_root ) {
                    // Insert at root level
                    array_splice( $elements, $insert_position, 0, [ $clone ] );
                    $inserted        = true;
                    $final_parent_id = null;
                } else {
                    // Insert in same parent
                    $parent_id       = $original_parent['id'];
                    $inserted        = $this->insert_element_after(
                        $elements,
                        $parent_id,
                        $original_idx,
                        $clone
                    );
                    $final_parent_id = $parent_id;
                }
            } else {
                // Append to same parent
                if ( $is_root ) {
                    $elements[]      = $clone;
                    $inserted        = true;
                    $final_parent_id = null;
                } else {
                    $parent_id       = $original_parent['id'];
                    $inserted        = $this->insert_element_at_parent(
                        $elements,
                        $parent_id,
                        $clone,
                        -1
                    );
                    $final_parent_id = $parent_id;
                }
            }

            if ( ! $inserted ) {
                return $this->format_error(
                    'Failed to insert duplicated element',
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

            // Count cloned elements
            $cloned_count = 1;
            if ( ! empty( $clone['elements'] ) ) {
                $cloned_count += $this->count_elements_recursive( $clone['elements'] );
            }

            // Get new element path
            $new_path = $this->get_element_path( $elements, $clone['id'] );

            return $this->format_success(
                [
                    'original_id'     => $element_id,
                    'new_element_id'  => $clone['id'],
                    'new_element'     => $clone,
                    'parent_id'       => $final_parent_id,
                    'path'            => $new_path,
                    'elements_cloned' => $cloned_count,
                    'id_mapping'      => $id_mapping,
                ],
                sprintf(
                    'Successfully duplicated %s%s (%d element%s)',
                    $original['elType'],
                    isset( $original['widgetType'] ) ? ' (' . $original['widgetType'] . ')' : '',
                    $cloned_count,
                    $cloned_count > 1 ? 's' : ''
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to duplicate element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Insert element at a specific index within a parent.
     *
     * @since 1.2.0
     * @param array  &$elements  The elements array (by reference).
     * @param string $parent_id  The parent element ID.
     * @param int    $after_idx  Index to insert after.
     * @param array  $element    The element to insert.
     * @return bool True on success.
     */
    private function insert_element_after( &$elements, $parent_id, $after_idx, $element ) {
        foreach ( $elements as &$el ) {
            if ( isset( $el['id'] ) && $el['id'] === $parent_id ) {
                if ( ! isset( $el['elements'] ) ) {
                    $el['elements'] = [];
                }
                array_splice( $el['elements'], $after_idx + 1, 0, [ $element ] );
                return true;
            }

            if ( ! empty( $el['elements'] ) ) {
                if ( $this->insert_element_after( $el['elements'], $parent_id, $after_idx, $element ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Build a mapping of original IDs to new IDs.
     *
     * @since 1.2.0
     * @param array $original Original element tree.
     * @param array $clone    Cloned element tree.
     * @return array Mapping of original_id => new_id.
     */
    private function build_id_mapping( $original, $clone ) {
        $mapping = [];

        if ( isset( $original['id'] ) && isset( $clone['id'] ) ) {
            $mapping[ $original['id'] ] = $clone['id'];
        }

        if ( ! empty( $original['elements'] ) && ! empty( $clone['elements'] ) ) {
            $count = min( count( $original['elements'] ), count( $clone['elements'] ) );
            for ( $i = 0; $i < $count; $i++ ) {
                $child_mapping = $this->build_id_mapping(
                    $original['elements'][ $i ],
                    $clone['elements'][ $i ]
                );
                $mapping = array_merge( $mapping, $child_mapping );
            }
        }

        return $mapping;
    }
}
