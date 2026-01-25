<?php
/**
 * Add Widget to Column Tool
 *
 * Simplified tool for inserting widgets directly into columns or containers.
 * Handles widget instance creation automatically.
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
 * Add Widget to Column Tool Class
 *
 * A convenience wrapper that creates a widget instance and inserts it
 * into a specified column or container in a single operation.
 *
 * @since 1.2.0
 */
class Add_Widget_To_Column extends Base_Tool {

    use Element_Operations;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_add_widget_to_column';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Add Widget to Column';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Quickly add a widget to a column or container. Creates the widget instance automatically - just specify the widget type and settings.';
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
        return [ 'add', 'widget', 'column', 'container', 'insert', 'quick' ];
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
                    'description' => 'The post ID to add the widget to',
                    'minimum'     => 1,
                ],
                'parent_id'   => [
                    'type'        => 'string',
                    'description' => 'The column or container ID to add the widget to',
                    'minLength'   => 1,
                ],
                'widget_type' => [
                    'type'        => 'string',
                    'description' => 'The widget type (e.g., "heading", "text-editor", "button", "image", "video", "icon", "divider", "spacer", "html")',
                    'minLength'   => 1,
                ],
                'settings'    => [
                    'type'        => 'object',
                    'description' => 'Widget settings. Common settings vary by widget type. Use get_widget_schema for full details.',
                    'default'     => new \stdClass(),
                ],
                'position'    => [
                    'type'        => 'integer',
                    'description' => 'Position within the column (0-based). Use -1 to append at the end.',
                    'default'     => -1,
                ],
            ],
            'required'   => [ 'post_id', 'parent_id', 'widget_type' ],
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
        $post_id     = absint( $args['post_id'] );
        $parent_id   = sanitize_text_field( $args['parent_id'] );
        $widget_type = sanitize_text_field( $args['widget_type'] );
        $settings    = isset( $args['settings'] ) ? $args['settings'] : [];
        $position    = isset( $args['position'] ) ? intval( $args['position'] ) : -1;

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

        // Validate widget type exists
        if ( ! $this->is_valid_widget_type( $widget_type ) ) {
            return $this->format_error(
                sprintf( 'Widget type "%s" is not registered', $widget_type ),
                'invalid_widget_type',
                [ 'suggestion' => 'Use list_widgets to see available widget types' ]
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
            if ( ! is_array( $elements ) ) {
                $elements = [];
            }

            // Find parent element
            $parent = $this->find_element_recursive( $elements, $parent_id );
            if ( ! $parent ) {
                return $this->format_error(
                    sprintf( 'Parent element "%s" not found', $parent_id ),
                    'parent_not_found'
                );
            }

            // Validate parent can accept widgets
            $parent_type = $parent['elType'] ?? '';
            if ( ! in_array( $parent_type, [ 'column', 'container' ], true ) ) {
                return $this->format_error(
                    sprintf(
                        'Cannot add widget to element of type "%s". Widgets can only be added to columns or containers.',
                        $parent_type
                    ),
                    'invalid_parent_type'
                );
            }

            // Create widget instance
            $widget = $this->create_widget( $widget_type, $settings );

            // Insert widget
            $inserted = $this->insert_element_at_parent( $elements, $parent_id, $widget, $position );

            if ( ! $inserted ) {
                return $this->format_error(
                    'Failed to insert widget',
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

            // Get final position
            $final_position = $this->get_widget_position( $elements, $widget['id'], $parent_id );

            // Get path
            $path = $this->get_element_path( $elements, $widget['id'] );

            return $this->format_success(
                [
                    'widget_id'   => $widget['id'],
                    'widget_type' => $widget_type,
                    'parent_id'   => $parent_id,
                    'parent_type' => $parent_type,
                    'position'    => $final_position,
                    'path'        => $path,
                    'widget'      => $widget,
                ],
                sprintf(
                    'Successfully added %s widget to %s',
                    $widget_type,
                    $parent_type
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to add widget: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Check if a widget type is registered in Elementor.
     *
     * @since 1.2.0
     * @param string $widget_type The widget type to check.
     * @return bool True if valid.
     */
    private function is_valid_widget_type( $widget_type ) {
        $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
        $widget = $widgets_manager->get_widget_types( $widget_type );
        return $widget !== null;
    }

    /**
     * Create a widget element structure.
     *
     * @since 1.2.0
     * @param string $widget_type The widget type.
     * @param array  $settings    Widget settings.
     * @return array Widget element structure.
     */
    private function create_widget( $widget_type, $settings = [] ) {
        $widget_id = $this->generate_element_id();

        // Ensure settings is an object/array
        if ( empty( $settings ) ) {
            $settings = new \stdClass();
        }

        return [
            'id'         => $widget_id,
            'elType'     => 'widget',
            'widgetType' => $widget_type,
            'settings'   => $settings,
            'elements'   => [],
        ];
    }

    /**
     * Get widget position within parent.
     *
     * @since 1.2.0
     * @param array  $elements  The elements array.
     * @param string $widget_id The widget ID.
     * @param string $parent_id The parent ID.
     * @return int|null Position or null.
     */
    private function get_widget_position( $elements, $widget_id, $parent_id ) {
        $parent = $this->find_element_recursive( $elements, $parent_id );
        if ( ! $parent || empty( $parent['elements'] ) ) {
            return null;
        }

        foreach ( $parent['elements'] as $index => $child ) {
            if ( isset( $child['id'] ) && $child['id'] === $widget_id ) {
                return $index;
            }
        }

        return null;
    }
}
