<?php
/**
 * Update Widget Content Tool
 *
 * Simplified tool for updating common widget content fields.
 * Automatically maps friendly field names to proper widget settings.
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
 * Update Widget Content Tool Class
 *
 * Provides a simplified interface for updating widget content without
 * needing to know the exact setting key names for each widget type.
 *
 * @since 1.2.0
 */
class Update_Widget_Content extends Base_Tool {

    use Element_Operations;

    /**
     * Content field mappings for each widget type.
     *
     * Maps friendly field names to actual widget setting keys.
     *
     * @since 1.2.0
     * @var array
     */
    private $content_mappings = [
        'heading' => [
            'text'        => 'title',
            'title'       => 'title',
            'link'        => 'link.url',
            'size'        => 'size',
            'tag'         => 'header_size',
            'align'       => 'align',
        ],
        'text-editor' => [
            'text'        => 'editor',
            'content'     => 'editor',
            'editor'      => 'editor',
            'align'       => 'align',
        ],
        'button' => [
            'text'        => 'text',
            'button_text' => 'text',
            'link'        => 'link.url',
            'url'         => 'link.url',
            'size'        => 'size',
            'align'       => 'align',
            'icon'        => 'selected_icon.value',
        ],
        'image' => [
            'image_url'   => 'image.url',
            'image_id'    => 'image.id',
            'url'         => 'image.url',
            'alt'         => 'image.alt',
            'caption'     => 'caption',
            'link'        => 'link.url',
            'size'        => 'image_size',
            'align'       => 'align',
        ],
        'video' => [
            'url'         => 'youtube_url',
            'youtube_url' => 'youtube_url',
            'vimeo_url'   => 'vimeo_url',
            'video_url'   => 'hosted_url.url',
            'autoplay'    => 'autoplay',
            'mute'        => 'mute',
            'loop'        => 'loop',
        ],
        'icon' => [
            'icon'        => 'selected_icon.value',
            'link'        => 'link.url',
            'align'       => 'align',
            'size'        => 'icon_size.size',
        ],
        'icon-box' => [
            'icon'        => 'selected_icon.value',
            'title'       => 'title_text',
            'text'        => 'title_text',
            'description' => 'description_text',
            'link'        => 'link.url',
        ],
        'image-box' => [
            'image_url'   => 'image.url',
            'image_id'    => 'image.id',
            'title'       => 'title_text',
            'text'        => 'title_text',
            'description' => 'description_text',
            'link'        => 'link.url',
        ],
        'star-rating' => [
            'rating'      => 'rating',
            'title'       => 'title',
        ],
        'testimonial' => [
            'content'     => 'testimonial_content',
            'text'        => 'testimonial_content',
            'name'        => 'testimonial_name',
            'title'       => 'testimonial_job',
            'job'         => 'testimonial_job',
            'image_url'   => 'testimonial_image.url',
            'image_id'    => 'testimonial_image.id',
        ],
        'counter' => [
            'number'      => 'ending_number',
            'start'       => 'starting_number',
            'end'         => 'ending_number',
            'prefix'      => 'prefix',
            'suffix'      => 'suffix',
            'title'       => 'title',
        ],
        'progress' => [
            'title'       => 'title',
            'percent'     => 'percent',
            'text'        => 'inner_text',
        ],
        'tabs' => [
            // Tabs require special handling for repeater
        ],
        'accordion' => [
            // Accordion requires special handling for repeater
        ],
        'social-icons' => [
            // Requires special handling for repeater
        ],
        'alert' => [
            'title'       => 'alert_title',
            'text'        => 'alert_description',
            'description' => 'alert_description',
            'type'        => 'alert_type',
        ],
        'html' => [
            'html'        => 'html',
            'content'     => 'html',
        ],
        'shortcode' => [
            'shortcode'   => 'shortcode',
        ],
        'divider' => [
            'style'       => 'style',
            'weight'      => 'weight.size',
            'gap'         => 'gap.size',
        ],
        'spacer' => [
            'space'       => 'space.size',
            'height'      => 'space.size',
        ],
        'google-maps' => [
            'address'     => 'address',
            'zoom'        => 'zoom.size',
            'height'      => 'height.size',
        ],
    ];

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_update_widget_content';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Update Widget Content';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Quickly update widget content using friendly field names. Automatically maps to correct widget settings (e.g., "text" becomes "title" for headings, "editor" for text-editor).';
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
        return [ 'update', 'widget', 'content', 'text', 'edit', 'change', 'quick' ];
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
                    'description' => 'The post ID containing the widget',
                    'minimum'     => 1,
                ],
                'element_id' => [
                    'type'        => 'string',
                    'description' => 'The widget element ID to update',
                    'minLength'   => 1,
                ],
                'content'    => [
                    'type'        => 'object',
                    'description' => 'Content updates using friendly field names. Supported fields vary by widget type.',
                    'properties'  => [
                        'text'        => [
                            'type'        => 'string',
                            'description' => 'Main text content (heading title, button text, etc.)',
                        ],
                        'title'       => [
                            'type'        => 'string',
                            'description' => 'Title text',
                        ],
                        'description' => [
                            'type'        => 'string',
                            'description' => 'Description text',
                        ],
                        'link'        => [
                            'type'        => 'string',
                            'description' => 'URL link',
                        ],
                        'url'         => [
                            'type'        => 'string',
                            'description' => 'URL (alias for link)',
                        ],
                        'image_url'   => [
                            'type'        => 'string',
                            'description' => 'Image URL',
                        ],
                        'image_id'    => [
                            'type'        => 'integer',
                            'description' => 'WordPress attachment ID for image',
                        ],
                        'button_text' => [
                            'type'        => 'string',
                            'description' => 'Button text',
                        ],
                        'html'        => [
                            'type'        => 'string',
                            'description' => 'Raw HTML content',
                        ],
                        'align'       => [
                            'type'        => 'string',
                            'description' => 'Alignment (left, center, right, justify)',
                            'enum'        => [ 'left', 'center', 'right', 'justify' ],
                        ],
                    ],
                ],
                'raw_settings' => [
                    'type'        => 'object',
                    'description' => 'Optional: Raw settings to merge directly (bypasses content mapping)',
                    'default'     => new \stdClass(),
                ],
            ],
            'required'   => [ 'post_id', 'element_id', 'content' ],
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
        $post_id      = absint( $args['post_id'] );
        $element_id   = sanitize_text_field( $args['element_id'] );
        $content      = isset( $args['content'] ) ? $args['content'] : [];
        $raw_settings = isset( $args['raw_settings'] ) ? $args['raw_settings'] : [];

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

        // Validate content is not empty
        if ( empty( $content ) && empty( $raw_settings ) ) {
            return $this->format_error(
                'Must provide "content" or "raw_settings" to update',
                'missing_content'
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

            // Find the widget
            $widget = $this->find_element_recursive( $elements, $element_id );

            if ( ! $widget ) {
                return $this->format_error(
                    sprintf( 'Element with ID "%s" not found', $element_id ),
                    'element_not_found'
                );
            }

            // Verify it's a widget
            if ( ( $widget['elType'] ?? '' ) !== 'widget' ) {
                return $this->format_error(
                    sprintf(
                        'Element "%s" is a %s, not a widget. Use elementor_update_element for non-widget elements.',
                        $element_id,
                        $widget['elType'] ?? 'unknown'
                    ),
                    'not_a_widget'
                );
            }

            $widget_type = $widget['widgetType'] ?? 'unknown';

            // Map content to settings
            $mapped_settings = $this->map_content_to_settings( $content, $widget_type );

            // Merge with raw settings (raw takes precedence)
            $final_settings = array_merge( $mapped_settings, $raw_settings );

            if ( empty( $final_settings ) ) {
                return $this->format_error(
                    sprintf(
                        'No valid content fields for widget type "%s". Use raw_settings for custom fields.',
                        $widget_type
                    ),
                    'no_valid_fields'
                );
            }

            // Track what was mapped
            $mapped_fields = array_keys( $mapped_settings );
            $unmapped_fields = [];
            foreach ( array_keys( $content ) as $field ) {
                if ( ! isset( $mapped_settings[ $this->get_setting_key( $field, $widget_type ) ] ) ) {
                    // Check if it was actually mapped (could be nested)
                    $found = false;
                    foreach ( $mapped_fields as $mapped ) {
                        if ( strpos( $mapped, $field ) !== false ) {
                            $found = true;
                            break;
                        }
                    }
                    if ( ! $found && ! isset( $mapped_settings[ $field ] ) ) {
                        $unmapped_fields[] = $field;
                    }
                }
            }

            // Update the element
            $updated = $this->update_element_recursive( $elements, $element_id, $final_settings, false );

            if ( ! $updated ) {
                return $this->format_error(
                    'Failed to update widget',
                    'update_failed'
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

            // Get updated widget
            $updated_widget = $this->find_element_recursive( $elements, $element_id );

            $result_data = [
                'element_id'      => $element_id,
                'widget_type'     => $widget_type,
                'fields_updated'  => array_keys( $final_settings ),
                'settings_applied' => $final_settings,
                'updated_widget'  => $updated_widget,
            ];

            if ( ! empty( $unmapped_fields ) ) {
                $result_data['unmapped_fields'] = $unmapped_fields;
                $result_data['unmapped_note'] = 'These fields were not recognized for this widget type';
            }

            return $this->format_success(
                $result_data,
                sprintf(
                    'Successfully updated %s widget with %d field(s)',
                    $widget_type,
                    count( $final_settings )
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to update widget content: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Map friendly content fields to widget settings.
     *
     * @since 1.2.0
     * @param array  $content     Content with friendly field names.
     * @param string $widget_type The widget type.
     * @return array Mapped settings.
     */
    private function map_content_to_settings( $content, $widget_type ) {
        $settings = [];
        $mappings = $this->content_mappings[ $widget_type ] ?? [];

        foreach ( $content as $field => $value ) {
            // Check if we have a mapping for this field
            if ( isset( $mappings[ $field ] ) ) {
                $setting_key = $mappings[ $field ];
                $this->set_nested_value( $settings, $setting_key, $value );
            } else {
                // No mapping - use field name directly
                $settings[ $field ] = $value;
            }
        }

        return $settings;
    }

    /**
     * Get the setting key for a content field.
     *
     * @since 1.2.0
     * @param string $field       Field name.
     * @param string $widget_type Widget type.
     * @return string Setting key.
     */
    private function get_setting_key( $field, $widget_type ) {
        $mappings = $this->content_mappings[ $widget_type ] ?? [];
        return $mappings[ $field ] ?? $field;
    }

    /**
     * Set a nested value using dot notation.
     *
     * @since 1.2.0
     * @param array  &$array Target array.
     * @param string $key    Dot-notation key (e.g., "link.url").
     * @param mixed  $value  Value to set.
     */
    private function set_nested_value( &$array, $key, $value ) {
        $keys = explode( '.', $key );

        if ( count( $keys ) === 1 ) {
            $array[ $key ] = $value;
            return;
        }

        $current = &$array;
        foreach ( $keys as $i => $k ) {
            if ( $i === count( $keys ) - 1 ) {
                $current[ $k ] = $value;
            } else {
                if ( ! isset( $current[ $k ] ) || ! is_array( $current[ $k ] ) ) {
                    $current[ $k ] = [];
                }
                $current = &$current[ $k ];
            }
        }
    }
}
