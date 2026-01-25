<?php
/**
 * Replace Element Tool
 *
 * Replaces an existing element with a new one while preserving position.
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
 * Replace Element Tool Class
 *
 * Allows swapping one element for another while maintaining
 * the same position in the document hierarchy.
 *
 * @since 1.2.0
 */
class Replace_Element extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_replace_element';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Replace Element';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Replace an existing element with a new one while preserving its position. Can optionally preserve the original element ID.';
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
        return [ 'replace', 'swap', 'exchange', 'element', 'widget' ];
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
                'post_id'     => [
                    'type'        => 'integer',
                    'description' => 'The post ID containing the element',
                    'minimum'     => 1,
                ],
                'element_id'  => [
                    'type'        => 'string',
                    'description' => 'The ID of the element to replace',
                    'minLength'   => 1,
                ],
                'new_element' => [
                    'type'        => 'object',
                    'description' => 'The new element to insert in place of the old one',
                    'properties'  => [
                        'id'         => [
                            'type'        => 'string',
                            'description' => 'Element ID (auto-generated if not provided unless preserve_id is true)',
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
                    'required' => [ 'elType' ],
                ],
                'preserve_id' => [
                    'type'        => 'boolean',
                    'description' => 'Keep the original element ID on the new element',
                    'default'     => false,
                ],
                'merge_settings' => [
                    'type'        => 'boolean',
                    'description' => 'Merge original settings with new settings (new takes precedence)',
                    'default'     => false,
                ],
                'preserve_children' => [
                    'type'        => 'boolean',
                    'description' => 'Keep children from original element (if new element has none)',
                    'default'     => false,
                ],
            ],
            'required' => [ 'post_id', 'element_id', 'new_element' ],
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
        $post_id           = absint( $args['post_id'] );
        $element_id        = sanitize_text_field( $args['element_id'] );
        $new_element       = $args['new_element'];
        $preserve_id       = isset( $args['preserve_id'] ) ? (bool) $args['preserve_id'] : false;
        $merge_settings    = isset( $args['merge_settings'] ) ? (bool) $args['merge_settings'] : false;
        $preserve_children = isset( $args['preserve_children'] ) ? (bool) $args['preserve_children'] : false;

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

        // Validate new element structure
        if ( empty( $new_element['elType'] ) ) {
            return $this->format_error(
                'New element must have an elType',
                'invalid_element'
            );
        }

        // Validate widget type if element is a widget
        if ( $new_element['elType'] === 'widget' && empty( $new_element['widgetType'] ) ) {
            return $this->format_error(
                'Widget elements must have a widgetType',
                'invalid_widget'
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

            // Find original element and its parent
            $original_path = [];
            $original = $this->find_element_recursive( $elements, $element_id, $original_path );

            if ( ! $original ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Get parent info
            $parent_info = $this->find_parent_and_index( $elements, $element_id );
            $parent_id = $parent_info['parent'] ? $parent_info['parent']['id'] : null;
            $original_index = $parent_info['index'];
            $is_root = $parent_info['is_root'];

            // Validate parent-child relationship for new element
            $parent_element = $parent_info['parent'];
            $validation_error = null;
            if ( ! $this->validate_parent_child_relationship( $parent_element, $new_element, $validation_error ) ) {
                return $this->format_error(
                    $validation_error['message'],
                    $validation_error['code']
                );
            }

            // Prepare the replacement element
            $replacement = $this->prepare_replacement(
                $original,
                $new_element,
                $preserve_id,
                $merge_settings,
                $preserve_children
            );

            // Perform the replacement
            $replaced = $this->replace_element_at_position(
                $elements,
                $element_id,
                $replacement
            );

            if ( ! $replaced ) {
                return $this->format_error(
                    'Failed to replace element',
                    'replace_failed'
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

            // Get path of new element
            $new_path = $this->get_element_path( $elements, $replacement['id'] );

            return $this->format_success(
                [
                    'original_id'     => $element_id,
                    'new_id'          => $replacement['id'],
                    'id_preserved'    => $preserve_id,
                    'original_type'   => $original['elType'],
                    'new_type'        => $replacement['elType'],
                    'original_widget' => $original['widgetType'] ?? null,
                    'new_widget'      => $replacement['widgetType'] ?? null,
                    'parent_id'       => $parent_id,
                    'position'        => $original_index,
                    'path'            => $new_path,
                    'settings_merged' => $merge_settings,
                    'children_preserved' => $preserve_children && ! empty( $original['elements'] ) && empty( $new_element['elements'] ),
                    'element'         => $replacement,
                ],
                sprintf(
                    'Successfully replaced %s%s with %s%s',
                    $original['elType'],
                    isset( $original['widgetType'] ) ? ' (' . $original['widgetType'] . ')' : '',
                    $replacement['elType'],
                    isset( $replacement['widgetType'] ) ? ' (' . $replacement['widgetType'] . ')' : ''
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to replace element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Prepare the replacement element with appropriate settings.
     *
     * @since 1.2.0
     * @param array $original          Original element.
     * @param array $new_element       New element data.
     * @param bool  $preserve_id       Whether to keep original ID.
     * @param bool  $merge_settings    Whether to merge settings.
     * @param bool  $preserve_children Whether to keep original children.
     * @return array Prepared replacement element.
     */
    private function prepare_replacement( $original, $new_element, $preserve_id, $merge_settings, $preserve_children ) {
        $replacement = $new_element;

        // Handle ID
        if ( $preserve_id ) {
            $replacement['id'] = $original['id'];
        } elseif ( empty( $replacement['id'] ) ) {
            $replacement['id'] = $this->generate_element_id();
        }

        // Handle settings merge
        if ( $merge_settings ) {
            $original_settings = $original['settings'] ?? [];
            $new_settings = $new_element['settings'] ?? [];
            $replacement['settings'] = array_merge( $original_settings, $new_settings );
        } elseif ( ! isset( $replacement['settings'] ) ) {
            $replacement['settings'] = new \stdClass();
        }

        // Handle children preservation
        if ( $preserve_children && empty( $new_element['elements'] ) && ! empty( $original['elements'] ) ) {
            // Only preserve if new element can have children
            if ( in_array( $replacement['elType'], [ 'section', 'column', 'container' ], true ) ) {
                $replacement['elements'] = $original['elements'];
            }
        }

        // Ensure elements array exists for container types
        if ( ! isset( $replacement['elements'] ) ) {
            if ( in_array( $replacement['elType'], [ 'section', 'column', 'container' ], true ) ) {
                $replacement['elements'] = [];
            } else {
                $replacement['elements'] = [];
            }
        }

        // Regenerate child IDs if not preserving ID (to avoid conflicts)
        if ( ! $preserve_id && ! empty( $replacement['elements'] ) ) {
            $replacement['elements'] = array_map(
                [ $this, 'regenerate_ids_recursive' ],
                $replacement['elements']
            );
        }

        return $replacement;
    }

    /**
     * Replace element at its position in the tree.
     *
     * @since 1.2.0
     * @param array  &$elements   Elements array (by reference).
     * @param string $element_id  ID of element to replace.
     * @param array  $replacement Replacement element.
     * @return bool True on success.
     */
    private function replace_element_at_position( &$elements, $element_id, $replacement ) {
        foreach ( $elements as $index => &$element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                $elements[ $index ] = $replacement;
                return true;
            }

            if ( ! empty( $element['elements'] ) ) {
                if ( $this->replace_element_at_position( $element['elements'], $element_id, $replacement ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
