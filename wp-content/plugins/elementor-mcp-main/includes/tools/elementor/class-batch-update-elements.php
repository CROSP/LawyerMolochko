<?php
/**
 * Batch Update Elements Tool
 *
 * Updates multiple elements in a single operation with one save.
 * Significantly more efficient than multiple individual updates.
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
 * Batch Update Elements Tool Class
 *
 * Provides efficient batch updates for multiple elements in a single
 * document save operation.
 *
 * @since 1.2.0
 */
class Batch_Update_Elements extends Base_Tool {

    use Element_Operations;

    /**
     * Maximum number of updates allowed per batch.
     *
     * @since 1.2.0
     * @var int
     */
    const MAX_UPDATES = 100;

    /**
     * Get tool name.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_name() {
        return 'elementor_batch_update_elements';
    }

    /**
     * Get tool title.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_title() {
        return 'Batch Update Elements';
    }

    /**
     * Get tool description.
     *
     * @since 1.2.0
     * @return string
     */
    public function get_description() {
        return 'Update multiple elements in a single operation. Much more efficient than multiple individual updates as it only saves the document once.';
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
        return [ 'batch', 'bulk', 'update', 'multiple', 'elements', 'efficient' ];
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
                    'description' => 'The post ID containing the elements',
                    'minimum'     => 1,
                ],
                'updates' => [
                    'type'        => 'array',
                    'description' => 'Array of update operations to perform',
                    'minItems'    => 1,
                    'maxItems'    => self::MAX_UPDATES,
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'element_id' => [
                                'type'        => 'string',
                                'description' => 'Element ID to update',
                                'minLength'   => 1,
                            ],
                            'settings' => [
                                'type'        => 'object',
                                'description' => 'Settings to apply (merged with existing)',
                            ],
                            'replace_settings' => [
                                'type'        => 'boolean',
                                'description' => 'Replace all settings instead of merging',
                                'default'     => false,
                            ],
                        ],
                        'required' => [ 'element_id', 'settings' ],
                    ],
                ],
                'stop_on_error' => [
                    'type'        => 'boolean',
                    'description' => 'Stop processing if an element is not found',
                    'default'     => false,
                ],
            ],
            'required' => [ 'post_id', 'updates' ],
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
        $updates       = $args['updates'];
        $stop_on_error = isset( $args['stop_on_error'] ) ? (bool) $args['stop_on_error'] : false;

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

        // Validate updates count
        if ( count( $updates ) > self::MAX_UPDATES ) {
            return $this->format_error(
                sprintf( 'Maximum %d updates allowed per batch', self::MAX_UPDATES ),
                'too_many_updates'
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

            // Process updates
            $results = [];
            $success_count = 0;
            $failed_count = 0;
            $skipped_count = 0;

            foreach ( $updates as $index => $update ) {
                $element_id = sanitize_text_field( $update['element_id'] );
                $settings = $update['settings'];
                $replace = isset( $update['replace_settings'] ) ? (bool) $update['replace_settings'] : false;

                // Validate settings
                if ( ! is_array( $settings ) || empty( $settings ) ) {
                    $results[] = [
                        'element_id' => $element_id,
                        'success'    => false,
                        'error'      => 'Empty or invalid settings',
                    ];
                    $failed_count++;

                    if ( $stop_on_error ) {
                        break;
                    }
                    continue;
                }

                // Check if element exists first
                $element = $this->find_element_recursive( $elements, $element_id );
                if ( ! $element ) {
                    $results[] = [
                        'element_id' => $element_id,
                        'success'    => false,
                        'error'      => 'Element not found',
                    ];
                    $failed_count++;

                    if ( $stop_on_error ) {
                        break;
                    }
                    continue;
                }

                // Update element
                $updated = $this->update_element_recursive( $elements, $element_id, $settings, $replace );

                if ( $updated ) {
                    $results[] = [
                        'element_id'    => $element_id,
                        'success'       => true,
                        'settings_keys' => array_keys( $settings ),
                        'replace_mode'  => $replace,
                    ];
                    $success_count++;
                } else {
                    $results[] = [
                        'element_id' => $element_id,
                        'success'    => false,
                        'error'      => 'Update failed',
                    ];
                    $failed_count++;

                    if ( $stop_on_error ) {
                        break;
                    }
                }
            }

            // Calculate skipped if stopped early
            $processed = $success_count + $failed_count;
            $skipped_count = count( $updates ) - $processed;

            // Only save if at least one update succeeded
            if ( $success_count > 0 ) {
                $save_result = $this->save_document_and_invalidate( $document, $elements, $post_id );

                if ( is_wp_error( $save_result ) ) {
                    return $this->format_error(
                        'Failed to save document: ' . $save_result->get_error_message(),
                        'save_failed',
                        [
                            'updates_attempted' => $processed,
                            'updates_successful_before_save' => $success_count,
                        ]
                    );
                }
            }

            // Build response
            $response_data = [
                'total_requested' => count( $updates ),
                'successful'      => $success_count,
                'failed'          => $failed_count,
                'skipped'         => $skipped_count,
                'results'         => $results,
                'document_saved'  => $success_count > 0,
            ];

            // Determine overall success
            if ( $failed_count === 0 && $skipped_count === 0 ) {
                return $this->format_success(
                    $response_data,
                    sprintf( 'Successfully updated all %d elements', $success_count )
                );
            } elseif ( $success_count > 0 ) {
                return $this->format_success(
                    $response_data,
                    sprintf(
                        'Partially completed: %d succeeded, %d failed, %d skipped',
                        $success_count,
                        $failed_count,
                        $skipped_count
                    )
                );
            } else {
                return $this->format_error(
                    'All updates failed',
                    'all_updates_failed',
                    $response_data
                );
            }

        } catch ( \Exception $e ) {
            return $this->format_error(
                'Failed to batch update elements: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }
}
