<?php
/**
 * Update Element Tool
 *
 * Updates a specific element by ID without rewriting the entire page data,
 * significantly improving performance for single element updates.
 *
 * @package ElementorMCP\Tools
 * @since 1.1.0
 */

namespace ElementorMCP\Tools\Elementor;

use ElementorMCP\Tools\Base_Tool;
use ElementorMCP\Cache\Page_Cache;
use ElementorMCP\Cache\Element_Cache;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Update Element tool class.
 *
 * Provides efficient updating of individual Elementor elements
 * without rewriting entire page data.
 *
 * @since 1.1.0
 */
class Update_Element extends Base_Tool {

    /**
     * Get tool name.
     *
     * @return string
     */
    public function get_name() {
        return 'elementor_update_element';
    }

    /**
     * Get tool title.
     *
     * @return string
     */
    public function get_title() {
        return 'Update Element';
    }

    /**
     * Get tool description.
     *
     * @return string
     */
    public function get_description() {
        return 'Update a specific element by ID efficiently without rewriting entire page';
    }

    /**
     * Get tool category.
     *
     * @return string
     */
    public function get_category() {
        return 'elementor';
    }

    /**
     * Get tool keywords.
     *
     * @return array
     */
    public function get_keywords() {
        return ['element', 'widget', 'update', 'edit', 'partial', 'settings'];
    }

    /**
     * Get input schema.
     *
     * @return array
     */
    public function get_input_schema() {
        return [
            'type' => 'object',
            'properties' => [
                'post_id' => [
                    'type' => 'integer',
                    'description' => 'The post ID containing the element',
                    'minimum' => 1
                ],
                'element_id' => [
                    'type' => 'string',
                    'description' => 'The element ID to update',
                    'minLength' => 1
                ],
                'settings' => [
                    'type' => 'object',
                    'description' => 'The settings to update (will be merged with existing)',
                    'additionalProperties' => true
                ],
                'replace_settings' => [
                    'type' => 'boolean',
                    'description' => 'Whether to replace all settings instead of merging',
                    'default' => false
                ],
                'invalidate_cache' => [
                    'type' => 'boolean',
                    'description' => 'Whether to invalidate cache after update',
                    'default' => true
                ]
            ],
            'required' => ['post_id', 'element_id', 'settings']
        ];
    }

    /**
     * Execute the tool.
     *
     * @param array $args Tool parameters.
     * @return array
     */
    public function execute($args) {
        $params = $args; // For backward compatibility with existing code
        $post_id = absint($params['post_id']);
        $element_id = sanitize_text_field($params['element_id']);
        $new_settings = $params['settings'];
        $replace_settings = $params['replace_settings'] ?? false;
        $invalidate_cache = $params['invalidate_cache'] ?? true;

        // Validate settings parameter
        if (!is_array($new_settings)) {
            return $this->format_error(
                'Settings must be an associative array',
                'invalid_settings'
            );
        }

        // Check for indexed array (should be associative)
        if (!empty($new_settings) && array_keys($new_settings) === range(0, count($new_settings) - 1)) {
            return $this->format_error(
                'Settings must be an associative array, not an indexed array',
                'invalid_settings'
            );
        }

        // Add size limit to prevent DoS (10MB max)
        $settings_size = strlen(json_encode($new_settings));
        if ($settings_size > 10 * 1024 * 1024) {
            return $this->format_error(
                'Settings size exceeds maximum limit of 10MB',
                'settings_too_large'
            );
        }

        // Check if post exists and is built with Elementor
        if (!$this->is_valid_elementor_post($post_id)) {
            return $this->format_error(
                'Invalid post or not built with Elementor',
                'invalid_post'
            );
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return $this->format_error(
                'You do not have permission to edit this post',
                'permission_denied'
            );
        }

        try {
            // Get document
            $document = \Elementor\Plugin::$instance->documents->get($post_id);
            if (!$document) {
                return $this->format_error('Document not found', 'document_not_found');
            }

            // Get current elements data
            $elements = $document->get_elements_data();
            if (!$elements) {
                return $this->format_error('No elements found in document', 'no_elements');
            }

            // Update the element recursively
            $updated = $this->update_element_recursive(
                $elements,
                $element_id,
                $new_settings,
                $replace_settings
            );

            if (!$updated) {
                return $this->format_error(
                    sprintf('Element with ID "%s" not found', $element_id),
                    'element_not_found'
                );
            }

            // Save the updated elements back to the document
            $result = $document->save([
                'elements' => $elements,
                'settings' => $document->get_settings()
            ]);

            if (is_wp_error($result)) {
                return $this->format_error(
                    'Failed to save document: ' . $result->get_error_message(),
                    'save_failed'
                );
            }

            // Invalidate caches if requested
            if ($invalidate_cache) {
                // Load cache classes if needed
                if (class_exists('\ElementorMCP\Cache\Page_Cache')) {
                    Page_Cache::invalidate($post_id);
                }
                if (class_exists('\ElementorMCP\Cache\Element_Cache')) {
                    Element_Cache::invalidate($post_id, $element_id);
                }

                // Clear Elementor's internal cache
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            }

            // Get the updated element to return
            $updated_element = $this->find_element_recursive($elements, $element_id);

            return $this->format_success([
                'message' => 'Element updated successfully',
                'post_id' => $post_id,
                'element_id' => $element_id,
                'element' => $updated_element,
                'cache_invalidated' => $invalidate_cache
            ]);

        } catch (\Exception $e) {
            return $this->format_error(
                'Failed to update element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
    }

    /**
     * Update element recursively in elements array.
     *
     * @param array  &$elements        The elements array (passed by reference).
     * @param string $element_id       The element ID to update.
     * @param array  $new_settings     The new settings to apply.
     * @param bool   $replace_settings Whether to replace all settings.
     * @return bool True if element was found and updated.
     */
    private function update_element_recursive(&$elements, $element_id, $new_settings, $replace_settings = false) {
        foreach ($elements as &$element) {
            if (isset($element['id']) && $element['id'] === $element_id) {
                // Update settings
                if ($replace_settings) {
                    // Replace all settings
                    $element['settings'] = $new_settings;
                } else {
                    // Merge settings (new settings override existing)
                    if (!isset($element['settings'])) {
                        $element['settings'] = [];
                    }
                    $element['settings'] = array_merge($element['settings'], $new_settings);
                }

                // Update modified timestamp
                $element['modified'] = current_time('timestamp');

                return true;
            }

            // Check children recursively
            if (!empty($element['elements'])) {
                if ($this->update_element_recursive($element['elements'], $element_id, $new_settings, $replace_settings)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find element recursively in elements array.
     *
     * @param array  $elements   The elements array.
     * @param string $element_id The element ID to find.
     * @return array|null The found element or null.
     */
    private function find_element_recursive($elements, $element_id) {
        foreach ($elements as $element) {
            if (isset($element['id']) && $element['id'] === $element_id) {
                return $element;
            }

            if (!empty($element['elements'])) {
                $found = $this->find_element_recursive($element['elements'], $element_id);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Check if a post is valid and built with Elementor.
     *
     * @param int $post_id The post ID.
     * @return bool
     */
    private function is_valid_elementor_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        // Check if built with Elementor
        if (!\Elementor\Plugin::$instance->documents->get($post_id)) {
            return false;
        }

        return true;
    }
}