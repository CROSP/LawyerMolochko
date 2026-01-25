<?php
/**
 * Add Element Tool
 *
 * Inserts an element at a specific position in the document hierarchy.
 * Supports inserting sections, containers, columns, and widgets.
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
 * Add Element Tool Class
 *
 * Provides the ability to insert elements at any position in the
 * Elementor document hierarchy with proper validation.
 *
 * @since 1.2.0
 */
class Add_Element extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_add_element';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Add Element';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Insert an element (section, container, column, or widget) at a specific position in the document hierarchy. Validates parent-child relationships automatically.';
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
        return [ 'add', 'insert', 'element', 'widget', 'section', 'container', 'create' ];
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
                'post_id'   => [
                    'type'        => 'integer',
                    'description' => 'The post ID to add the element to',
                    'minimum'     => 1,
                ],
                'element'   => [
                    'type'        => 'object',
                    'description' => 'The element data to insert. Can be created using create_section, create_widget_instance, or custom structure.',
                    'properties'  => [
                        'id'         => [
                            'type'        => 'string',
                            'description' => 'Element ID (auto-generated if not provided)',
                        ],
                        'elType'     => [
                            'type'        => 'string',
                            'description' => 'Element type',
                            'enum'        => [ 'section', 'column', 'container', 'widget' ],
                        ],
                        'widgetType' => [
                            'type'        => 'string',
                            'description' => 'Widget type (required if elType is widget)',
                        ],
                        'settings'   => [
                            'type'        => 'object',
                            'description' => 'Element settings',
                        ],
                        'elements'   => [
                            'type'        => 'array',
                            'description' => 'Child elements',
                        ],
                    ],
                    'required'    => [ 'elType' ],
                ],
                'parent_id' => [
                    'type'        => 'string',
                    'description' => 'Parent element ID. Leave empty or null for root level (sections/containers only).',
                ],
                'position'  => [
                    'type'        => 'integer',
                    'description' => 'Position index within parent (0-based). Use -1 to append at the end.',
                    'default'     => -1,
                ],
            ],
            'required'   => [ 'post_id', 'element' ],
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
        $post_id   = absint( $args['post_id'] );
        $element   = $args['element'];
        $parent_id = isset( $args['parent_id'] ) ? sanitize_text_field( $args['parent_id'] ) : null;
        $position  = isset( $args['position'] ) ? intval( $args['position'] ) : -1;

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

        // Validate element structure
        if ( empty( $element['elType'] ) ) {
            return $this->format_error(
                'Element must have an elType (section, column, container, or widget)',
                'invalid_element'
            );
        }

        // Validate widget type if element is a widget
        if ( $element['elType'] === 'widget' && empty( $element['widgetType'] ) ) {
            return $this->format_error(
                'Widget elements must have a widgetType',
                'invalid_widget'
            );
        }

        // Normalize element (add ID if missing, ensure structure)
        $element = $this->normalize_element( $element );

        try {
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

            // Get parent element for validation
            $parent_element = null;
            if ( ! empty( $parent_id ) ) {
                $parent_element = $this->find_element_recursive( $elements, $parent_id );
                if ( ! $parent_element ) {
                    return $this->format_error(
                        sprintf( 'Parent element with ID "%s" not found', $parent_id ),
                        'parent_not_found'
                    );
                }
            }

            // Validate parent-child relationship
            $validation_error = null;
            if ( ! $this->validate_parent_child_relationship( $parent_element, $element, $validation_error ) ) {
                return $this->format_error(
                    $validation_error['message'],
                    $validation_error['code']
                );
            }

            // Insert element
            $inserted = $this->insert_element_at_parent( $elements, $parent_id, $element, $position );

            if ( ! $inserted ) {
                return $this->format_error(
                    'Failed to insert element at specified location',
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

            // Calculate actual position
            $actual_position = $this->get_element_position( $elements, $element['id'], $parent_id );

            // Get element path
            $path = $this->get_element_path( $elements, $element['id'] );

            return $this->format_success(
                [
                    'element_id'  => $element['id'],
                    'parent_id'   => $parent_id,
                    'position'    => $actual_position,
                    'path'        => $path,
                    'element'     => $element,
                    'elType'      => $element['elType'],
                    'widgetType'  => $element['widgetType'] ?? null,
                ],
                sprintf(
                    'Successfully added %s%s to %s',
                    $element['elType'],
                    $element['elType'] === 'widget' ? ' (' . $element['widgetType'] . ')' : '',
                    $parent_id ? 'parent ' . $parent_id : 'root level'
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to add element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Get the position of an element within its parent.
     *
     * @since 1.2.0
     * @param array       $elements   The elements array.
     * @param string      $element_id The element ID to find.
     * @param string|null $parent_id  The parent ID (null for root).
     * @return int|null The position or null if not found.
     */
    private function get_element_position( $elements, $element_id, $parent_id = null ) {
        // Search at root level
        if ( empty( $parent_id ) ) {
            foreach ( $elements as $index => $element ) {
                if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                    return $index;
                }
            }
            return null;
        }

        // Search within parent
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
