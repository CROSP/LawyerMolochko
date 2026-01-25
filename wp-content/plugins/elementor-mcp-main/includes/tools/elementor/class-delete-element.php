<?php
/**
 * Delete Element Tool
 *
 * Removes an element and all its children from the document.
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
 * Delete Element Tool Class
 *
 * Provides the ability to remove elements from the Elementor document.
 * Automatically removes all child elements.
 *
 * @since 1.2.0
 */
class Delete_Element extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_delete_element';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Delete Element';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Remove an element and all its children from the document by ID.';
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
        return [ 'delete', 'remove', 'element', 'widget', 'section' ];
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
                'post_id'    => [
                    'type'        => 'integer',
                    'description' => 'The post ID containing the element',
                    'minimum'     => 1,
                ],
                'element_id' => [
                    'type'        => 'string',
                    'description' => 'The element ID to delete',
                    'minLength'   => 1,
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
        $post_id    = absint( $args['post_id'] );
        $element_id = sanitize_text_field( $args['element_id'] );

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

            // Find element first to get info before deletion
            $path = [];
            $element_to_delete = $this->find_element_recursive( $elements, $element_id, $path );

            if ( ! $element_to_delete ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Count children that will be deleted
            $children_count = 0;
            if ( ! empty( $element_to_delete['elements'] ) ) {
                $children_count = $this->count_elements_recursive( $element_to_delete['elements'] );
            }

            // Get parent info before deletion
            $parent_info = $this->find_parent_and_index( $elements, $element_id );
            $parent_id   = $parent_info && $parent_info['parent'] ? $parent_info['parent']['id'] : null;

            // Remove element
            $removed = $this->remove_element_recursive( $elements, $element_id );

            if ( ! $removed ) {
                return $this->format_error(
                    'Failed to remove element',
                    'delete_failed'
                );
            }

            // Validate document structure after deletion
            $validation_result = $this->validate_document_structure( $elements );
            if ( ! $validation_result['valid'] ) {
                // Warn but still proceed - some invalid structures may be intentional
                // during development/editing
            }

            // Save document
            $save_result = $this->save_document_and_invalidate( $document, $elements, $post_id );

            if ( is_wp_error( $save_result ) ) {
                return $this->format_error(
                    'Failed to save document: ' . $save_result->get_error_message(),
                    'save_failed'
                );
            }

            return $this->format_success(
                [
                    'deleted_element_id' => $element_id,
                    'deleted_element'    => [
                        'id'         => $removed['id'],
                        'elType'     => $removed['elType'],
                        'widgetType' => $removed['widgetType'] ?? null,
                    ],
                    'children_deleted'   => $children_count,
                    'parent_id'          => $parent_id,
                    'path'               => array_column( $path, 'id' ),
                ],
                sprintf(
                    'Successfully deleted %s%s%s',
                    $removed['elType'],
                    isset( $removed['widgetType'] ) ? ' (' . $removed['widgetType'] . ')' : '',
                    $children_count > 0 ? ' and ' . $children_count . ' child elements' : ''
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to delete element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Validate document structure after modifications.
     *
     * Checks for common structural issues like:
     * - Empty sections without columns
     * - Orphaned widgets
     *
     * @since 1.2.0
     * @param array $elements The elements array.
     * @return array Validation result with 'valid' boolean and 'warnings' array.
     */
    private function validate_document_structure( $elements ) {
        $warnings = [];

        foreach ( $elements as $element ) {
            // Check sections have columns
            if ( $element['elType'] === 'section' ) {
                if ( empty( $element['elements'] ) ) {
                    $warnings[] = sprintf(
                        'Section "%s" has no columns',
                        $element['id']
                    );
                }
            }

            // Recursively check children
            if ( ! empty( $element['elements'] ) ) {
                $child_result = $this->validate_document_structure( $element['elements'] );
                $warnings     = array_merge( $warnings, $child_result['warnings'] );
            }
        }

        return [
            'valid'    => empty( $warnings ),
            'warnings' => $warnings,
        ];
    }
}
