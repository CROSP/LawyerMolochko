<?php
/**
 * Update Page Settings Tool
 *
 * Updates document-level settings for an Elementor page.
 *
 * @package ElementorMCP\Tools\Documents
 * @since 1.2.0
 */

namespace ElementorMCP\Tools\Documents;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Update Page Settings Tool Class
 *
 * Updates document-level settings including template, background,
 * custom CSS, and other page-specific configurations.
 *
 * @since 1.2.0
 */
class Update_Page_Settings extends Base_Tool {

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'update_page_settings';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Update document-level page settings including template, hide title, background, padding, custom CSS, and more.';
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
                    'description' => 'The post ID to update settings for',
                    'minimum'     => 1,
                ],
                'settings' => [
                    'type'        => 'object',
                    'description' => 'Page settings to update',
                    'properties'  => [
                        'template' => [
                            'type'        => 'string',
                            'description' => 'Page template (default, elementor_header_footer, elementor_canvas, etc.)',
                        ],
                        'hide_title' => [
                            'type'        => 'string',
                            'description' => 'Hide the page title',
                            'enum'        => [ 'yes', 'no', '' ],
                        ],
                        'content_width' => [
                            'type'        => 'object',
                            'description' => 'Content area width',
                            'properties'  => [
                                'size' => [ 'type' => 'integer' ],
                                'unit' => [ 'type' => 'string', 'enum' => [ 'px', '%', 'vw' ] ],
                            ],
                        ],
                        'content_position' => [
                            'type'        => 'string',
                            'description' => 'Vertical content position',
                            'enum'        => [ '', 'top', 'center', 'bottom' ],
                        ],
                        'background_background' => [
                            'type'        => 'string',
                            'description' => 'Background type',
                            'enum'        => [ '', 'classic', 'gradient', 'video', 'slideshow' ],
                        ],
                        'background_color' => [
                            'type'        => 'string',
                            'description' => 'Background color (hex)',
                        ],
                        'background_image' => [
                            'type'        => 'object',
                            'description' => 'Background image',
                            'properties'  => [
                                'url' => [ 'type' => 'string' ],
                                'id'  => [ 'type' => 'integer' ],
                            ],
                        ],
                        'background_position' => [
                            'type'        => 'string',
                            'description' => 'Background image position',
                        ],
                        'background_size' => [
                            'type'        => 'string',
                            'description' => 'Background size',
                            'enum'        => [ '', 'auto', 'cover', 'contain' ],
                        ],
                        'background_repeat' => [
                            'type'        => 'string',
                            'description' => 'Background repeat',
                            'enum'        => [ '', 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' ],
                        ],
                        'padding' => [
                            'type'        => 'object',
                            'description' => 'Page padding',
                            'properties'  => [
                                'top'      => [ 'type' => 'string' ],
                                'right'    => [ 'type' => 'string' ],
                                'bottom'   => [ 'type' => 'string' ],
                                'left'     => [ 'type' => 'string' ],
                                'unit'     => [ 'type' => 'string', 'enum' => [ 'px', 'em', '%' ] ],
                                'isLinked' => [ 'type' => 'boolean' ],
                            ],
                        ],
                        'margin' => [
                            'type'        => 'object',
                            'description' => 'Page margin',
                            'properties'  => [
                                'top'      => [ 'type' => 'string' ],
                                'right'    => [ 'type' => 'string' ],
                                'bottom'   => [ 'type' => 'string' ],
                                'left'     => [ 'type' => 'string' ],
                                'unit'     => [ 'type' => 'string', 'enum' => [ 'px', 'em', '%' ] ],
                                'isLinked' => [ 'type' => 'boolean' ],
                            ],
                        ],
                        'custom_css' => [
                            'type'        => 'string',
                            'description' => 'Custom CSS for this page',
                        ],
                        'scroll_snap' => [
                            'type'        => 'string',
                            'description' => 'Enable scroll snapping',
                            'enum'        => [ '', 'yes', 'no' ],
                        ],
                        'stretch_section' => [
                            'type'        => 'string',
                            'description' => 'Default section stretch behavior',
                            'enum'        => [ '', 'yes', 'no' ],
                        ],
                    ],
                ],
                'merge_settings' => [
                    'type'        => 'boolean',
                    'description' => 'Merge with existing settings (true) or replace (false)',
                    'default'     => true,
                ],
            ],
            'required' => [ 'post_id', 'settings' ],
        ];
    }

    /**
     * Execute the tool.
     *
     * @since 1.2.0
     * @param array $args Tool arguments.
     * @return array Result with update status.
     */
    public function execute( $args ) {
        $post_id        = absint( $args['post_id'] );
        $new_settings   = $args['settings'];
        $merge_settings = isset( $args['merge_settings'] ) ? (bool) $args['merge_settings'] : true;

        // Verify Elementor is active
        if ( ! $this->verify_elementor_active() ) {
            return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
        }

        // Validate post
        $post = get_post( $post_id );
        if ( ! $post ) {
            return $this->format_error(
                'Post not found',
                'post_not_found'
            );
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $this->format_error(
                'You do not have permission to edit this post',
                'permission_denied'
            );
        }

        // Validate settings
        if ( empty( $new_settings ) || ! is_array( $new_settings ) ) {
            return $this->format_error(
                'Settings cannot be empty',
                'empty_settings'
            );
        }

        try {
            // Get document
            $document = \Elementor\Plugin::$instance->documents->get( $post_id );

            if ( ! $document ) {
                return $this->format_error(
                    'Could not get Elementor document for this post',
                    'document_not_found'
                );
            }

            // Get current settings
            $current_settings = $document->get_settings();

            // Handle template separately (it's stored in post meta)
            $template_updated = false;
            if ( isset( $new_settings['template'] ) ) {
                $template = sanitize_text_field( $new_settings['template'] );
                update_post_meta( $post_id, '_wp_page_template', $template );
                $template_updated = true;
                unset( $new_settings['template'] );
            }

            // Prepare final settings
            if ( $merge_settings ) {
                $final_settings = array_merge( $current_settings, $new_settings );
            } else {
                $final_settings = $new_settings;
            }

            // Get current elements (we need to pass them to save)
            $elements = $document->get_elements_data();

            // Save document with new settings
            $save_result = $document->save( [
                'elements' => $elements,
                'settings' => $final_settings,
            ] );

            if ( is_wp_error( $save_result ) ) {
                return $this->format_error(
                    'Failed to save document: ' . $save_result->get_error_message(),
                    'save_failed'
                );
            }

            // Clear Elementor cache
            \Elementor\Plugin::$instance->files_manager->clear_cache();

            // Build response
            $updated_keys = array_keys( $new_settings );
            if ( $template_updated ) {
                $updated_keys[] = 'template';
            }

            return $this->format_success(
                [
                    'post_id'          => $post_id,
                    'updated_settings' => $updated_keys,
                    'settings_count'   => count( $updated_keys ),
                    'merge_mode'       => $merge_settings,
                    'template_updated' => $template_updated,
                    'current_settings' => $document->get_settings(),
                ],
                sprintf(
                    'Successfully updated %d page setting(s) for post #%d',
                    count( $updated_keys ),
                    $post_id
                )
            );

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to update page settings: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }
}
