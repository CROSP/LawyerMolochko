<?php
/**
 * Element Cache Manager
 *
 * Manages caching of individual Elementor elements/widgets for
 * efficient partial data operations.
 *
 * @package ElementorMCP\Cache
 * @since 1.1.0
 */

namespace ElementorMCP\Cache;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Element Cache class.
 *
 * Provides methods to cache and retrieve individual Elementor elements
 * without needing to parse entire page data.
 *
 * @since 1.1.0
 */
class Element_Cache {

    /**
     * Cache group name.
     *
     * @var string
     */
    const CACHE_GROUP = 'elementor_mcp_elements';

    /**
     * Cache expiry time in seconds.
     *
     * @var int
     */
    const CACHE_EXPIRY = 3600; // 1 hour

    /**
     * Get cached element data.
     *
     * Retrieves cached element data if available.
     *
     * @since 1.1.0
     * @param int    $post_id    The post ID.
     * @param string $element_id The element ID.
     * @return mixed|false The cached element data or false if not found.
     */
    public static function get($post_id, $element_id) {
        $cache_key = self::get_cache_key($post_id, $element_id);
        return wp_cache_get($cache_key, self::CACHE_GROUP);
    }

    /**
     * Set element data in cache.
     *
     * Stores element data in cache with expiry.
     *
     * @since 1.1.0
     * @param int    $post_id    The post ID.
     * @param string $element_id The element ID.
     * @param array  $data       The element data to cache.
     * @return bool True on success, false on failure.
     */
    public static function set($post_id, $element_id, $data) {
        $cache_key = self::get_cache_key($post_id, $element_id);

        // Add metadata
        $cache_data = [
            'element' => $data,
            'post_id' => $post_id,
            'element_id' => $element_id,
            'widget_type' => $data['widgetType'] ?? null,
            'element_type' => $data['elType'] ?? null,
            'timestamp' => time()
        ];

        return wp_cache_set($cache_key, $cache_data, self::CACHE_GROUP, self::CACHE_EXPIRY);
    }

    /**
     * Get or set cached element data.
     *
     * Retrieves cached element if available, otherwise executes callback
     * and caches the result.
     *
     * @since 1.1.0
     * @param int      $post_id    The post ID.
     * @param string   $element_id The element ID.
     * @param callable $callback   Callback to find element if not cached.
     * @return mixed The cached or found element data.
     */
    public static function remember($post_id, $element_id, $callback) {
        $cached = self::get($post_id, $element_id);

        if ($cached !== false) {
            return $cached['element'] ?? $cached;
        }

        // Find element using callback
        $element = call_user_func($callback, $post_id, $element_id);

        // Cache it if found
        if ($element !== null) {
            self::set($post_id, $element_id, $element);
        }

        return $element;
    }

    /**
     * Invalidate cached element.
     *
     * Removes cached data for a specific element.
     *
     * @since 1.1.0
     * @param int    $post_id    The post ID.
     * @param string $element_id The element ID.
     * @return bool True on success, false on failure.
     */
    public static function invalidate($post_id, $element_id) {
        $cache_key = self::get_cache_key($post_id, $element_id);
        return wp_cache_delete($cache_key, self::CACHE_GROUP);
    }

    /**
     * Invalidate all elements for a post.
     *
     * Clears all cached element data for a specific post.
     *
     * @since 1.1.0
     * @param int $post_id The post ID.
     * @return bool True on success.
     */
    public static function invalidate_post_elements($post_id) {
        // Since we can't easily clear by pattern in WordPress cache,
        // we track elements per post in a separate key
        $elements_key = "post_{$post_id}_elements";
        $element_ids = wp_cache_get($elements_key, self::CACHE_GROUP);

        if (is_array($element_ids)) {
            foreach ($element_ids as $element_id) {
                self::invalidate($post_id, $element_id);
            }
            wp_cache_delete($elements_key, self::CACHE_GROUP);
        }

        return true;
    }

    /**
     * Cache multiple elements from page data.
     *
     * Parses page data and caches individual elements for quick access.
     *
     * @since 1.1.0
     * @param int   $post_id  The post ID.
     * @param array $elements The page elements data.
     * @return int Number of elements cached.
     */
    public static function cache_page_elements($post_id, $elements) {
        $cached_count = 0;
        $element_ids = [];

        $cached_count = self::cache_elements_recursive($post_id, $elements, $element_ids);

        // Track element IDs for this post
        wp_cache_set("post_{$post_id}_elements", $element_ids, self::CACHE_GROUP, self::CACHE_EXPIRY);

        return $cached_count;
    }

    /**
     * Recursively cache elements.
     *
     * @since 1.1.0
     * @param int   $post_id     The post ID.
     * @param array $elements    The elements array.
     * @param array &$element_ids Reference to array tracking element IDs.
     * @return int Number of elements cached.
     */
    private static function cache_elements_recursive($post_id, $elements, &$element_ids) {
        $cached_count = 0;

        foreach ($elements as $element) {
            if (isset($element['id'])) {
                // Cache this element
                if (self::set($post_id, $element['id'], $element)) {
                    $cached_count++;
                    $element_ids[] = $element['id'];
                }

                // Process children if any
                if (!empty($element['elements'])) {
                    $cached_count += self::cache_elements_recursive(
                        $post_id,
                        $element['elements'],
                        $element_ids
                    );
                }
            }
        }

        return $cached_count;
    }

    /**
     * Get cache key for an element.
     *
     * Generates a unique cache key for a post and element ID combination.
     *
     * @since 1.1.0
     * @param int    $post_id    The post ID.
     * @param string $element_id The element ID.
     * @return string The cache key.
     */
    private static function get_cache_key($post_id, $element_id) {
        return sprintf('element_%d_%s', $post_id, $element_id);
    }

    /**
     * Find element in cached or fresh data.
     *
     * Searches for an element in cached page data, falling back to
     * fresh data if needed.
     *
     * @since 1.1.0
     * @param int    $post_id    The post ID.
     * @param string $element_id The element ID.
     * @return array|null The element data or null if not found.
     */
    public static function find_element($post_id, $element_id) {
        return self::remember($post_id, $element_id, function($post_id, $element_id) {
            // Try to get from page cache first
            $page_data = Page_Cache::remember($post_id, function($post_id) {
                $document = \Elementor\Plugin::$instance->documents->get($post_id);
                return $document ? $document->get_elements_data() : null;
            });

            if (!$page_data) {
                return null;
            }

            // Find element recursively
            return self::find_element_in_data($page_data, $element_id);
        });
    }

    /**
     * Find element in data array.
     *
     * Recursively searches for an element by ID in the elements array.
     *
     * @since 1.1.0
     * @param array  $elements   The elements array.
     * @param string $element_id The element ID to find.
     * @return array|null The element data or null if not found.
     */
    private static function find_element_in_data($elements, $element_id) {
        foreach ($elements as $element) {
            if (isset($element['id']) && $element['id'] === $element_id) {
                return $element;
            }

            if (!empty($element['elements'])) {
                $found = self::find_element_in_data($element['elements'], $element_id);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }
}