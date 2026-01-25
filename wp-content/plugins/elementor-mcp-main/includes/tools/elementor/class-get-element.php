<?php
/**
 * Get Element Tool
 *
 * Retrieves a specific element by ID from a page without loading
 * the entire page data, significantly improving performance.
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
 * Get Element tool class.
 *
 * Provides efficient access to individual Elementor elements
 * without loading entire page data.
 *
 * @since 1.1.0
 */
class Get_Element extends Base_Tool {

    /**
     * Get tool name.
     *
     * @return string
     */
    public function get_name() {
        return 'elementor_get_element';
    }

    /**
     * Get tool title.
     *
     * @return string
     */
    public function get_title() {
        return 'Get Element';
    }

    /**
     * Get tool description.
     *
     * @return string
     */
    public function get_description() {
        return 'Get a specific element by ID from a page efficiently using cache';
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
        return ['element', 'widget', 'get', 'single', 'partial'];
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
                    'description' => 'The element ID to retrieve',
                    'minLength' => 1
                ],
                'include_children' => [
                    'type' => 'boolean',
                    'description' => 'Whether to include child elements',
                    'default' => true
                ],
                'use_cache' => [
                    'type' => 'boolean',
                    'description' => 'Whether to use cache for faster retrieval',
                    'default' => true
                ]
            ],
            'required' => ['post_id', 'element_id']
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
        $include_children = $params['include_children'] ?? true;
        $use_cache = $params['use_cache'] ?? true;

        // Check if post exists and is built with Elementor
        if (!$this->is_valid_elementor_post($post_id)) {
            return $this->format_error(
                'Invalid post or not built with Elementor',
                'invalid_post'
            );
        }

        // Load cache classes if needed
        if ($use_cache) {
            if (!class_exists('\ElementorMCP\Cache\Element_Cache')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/cache/class-element-cache.php';
            }
            if (!class_exists('\ElementorMCP\Cache\Page_Cache')) {
                require_once ELEMENTOR_MCP_PATH . 'includes/cache/class-page-cache.php';
            }
        }

        try {
            // Try to get element from cache first
            if ($use_cache && class_exists('\ElementorMCP\Cache\Element_Cache')) {
                $element = Element_Cache::find_element($post_id, $element_id);
                if ($element) {
                    // If we don't want children, remove them
                    if (!$include_children && isset($element['elements'])) {
                        unset($element['elements']);
                    }

                    return $this->format_success([
                        'element' => $element,
                        'post_id' => $post_id,
                        'from_cache' => true
                    ]);
                }
            }

            // Not in cache, get from document
            $document = \Elementor\Plugin::$instance->documents->get($post_id);
            if (!$document) {
                return $this->format_error('Document not found', 'document_not_found');
            }

            // Get elements data (with caching if enabled)
            if ($use_cache && class_exists('\ElementorMCP\Cache\Page_Cache')) {
                $elements = Page_Cache::remember($post_id, function($post_id) use ($document) {
                    return $document->get_elements_data();
                });
            } else {
                $elements = $document->get_elements_data();
            }

            if (!$elements) {
                return $this->format_error('No elements found in document', 'no_elements');
            }

            // Find the specific element
            $element = $this->find_element_recursive($elements, $element_id);

            if (!$element) {
                return $this->format_error(
                    sprintf('Element with ID "%s" not found', $element_id),
                    'element_not_found'
                );
            }

            // Cache the found element for future use
            if ($use_cache && class_exists('\ElementorMCP\Cache\Element_Cache')) {
                Element_Cache::set($post_id, $element_id, $element);
            }

            // Remove children if not requested
            if (!$include_children && isset($element['elements'])) {
                unset($element['elements']);
            }

            return $this->format_success([
                'element' => $element,
                'post_id' => $post_id,
                'widget_type' => $element['widgetType'] ?? null,
                'element_type' => $element['elType'] ?? null,
                'from_cache' => false
            ]);

        } catch (\Exception $e) {
            return $this->format_error(
                'Failed to get element: ' . $e->getMessage(),
                'operation_failed'
            );
        }
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