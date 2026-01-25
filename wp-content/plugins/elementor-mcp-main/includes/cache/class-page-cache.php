<?php
/**
 * Page Cache Manager
 *
 * Manages caching of Elementor page data to reduce database queries
 * and JSON parsing overhead.
 *
 * @package ElementorMCP\Cache
 * @since 1.1.0
 */

namespace ElementorMCP\Cache;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Page Cache class.
 *
 * Provides methods to cache and retrieve Elementor page data
 * efficiently using WordPress object cache.
 *
 * @since 1.1.0
 */
class Page_Cache {

    /**
     * Cache group name.
     *
     * @var string
     */
    const CACHE_GROUP = 'elementor_mcp_pages';

    /**
     * Cache expiry time in seconds.
     *
     * @var int
     */
    const CACHE_EXPIRY = 3600; // 1 hour

    /**
     * Cache version for invalidation.
     *
     * @var string
     */
    const CACHE_VERSION = '1.0';

    /**
     * Get cached page data.
     *
     * Retrieves cached Elementor page data if available.
     *
     * @since 1.1.0
     * @param int $post_id The post ID.
     * @return mixed|false The cached data or false if not found.
     */
    public static function get($post_id) {
        $cache_key = self::get_cache_key($post_id);
        return wp_cache_get($cache_key, self::CACHE_GROUP);
    }

    /**
     * Set page data in cache.
     *
     * Stores Elementor page data in cache with expiry.
     *
     * @since 1.1.0
     * @param int   $post_id The post ID.
     * @param mixed $data    The data to cache.
     * @return bool True on success, false on failure.
     */
    public static function set($post_id, $data) {
        $cache_key = self::get_cache_key($post_id);

        // Add metadata to cached data
        $cache_data = [
            'data' => $data,
            'timestamp' => time(),
            'version' => self::CACHE_VERSION,
            'post_modified' => get_post_modified_time('U', false, $post_id)
        ];

        return wp_cache_set($cache_key, $cache_data, self::CACHE_GROUP, self::CACHE_EXPIRY);
    }

    /**
     * Get or set cached page data.
     *
     * Retrieves cached data if available, otherwise executes callback
     * and caches the result.
     *
     * @since 1.1.0
     * @param int      $post_id  The post ID.
     * @param callable $callback Callback to generate data if not cached.
     * @return mixed The cached or generated data.
     */
    public static function remember($post_id, $callback) {
        $cached = self::get($post_id);

        if ($cached !== false && self::is_cache_valid($post_id, $cached)) {
            return $cached['data'];
        }

        // Generate fresh data
        $data = call_user_func($callback, $post_id);

        // Cache it
        if ($data !== null) {
            self::set($post_id, $data);
        }

        return $data;
    }

    /**
     * Invalidate cached page data.
     *
     * Removes cached data for a specific post.
     *
     * @since 1.1.0
     * @param int $post_id The post ID.
     * @return bool True on success, false on failure.
     */
    public static function invalidate($post_id) {
        $cache_key = self::get_cache_key($post_id);
        $result = wp_cache_delete($cache_key, self::CACHE_GROUP);

        // Also invalidate related element caches
        Element_Cache::invalidate_post_elements($post_id);

        // Trigger action for extensions
        do_action('elementor_mcp_page_cache_invalidated', $post_id);

        return $result;
    }

    /**
     * Invalidate all cached pages.
     *
     * Clears all cached page data.
     *
     * @since 1.1.0
     * @return bool True on success.
     */
    public static function invalidate_all() {
        // WordPress doesn't have a native flush group function
        // So we use a version key approach
        $version_key = 'cache_version_' . self::CACHE_GROUP;
        $current_version = (int) get_transient($version_key);
        set_transient($version_key, $current_version + 1, DAY_IN_SECONDS);

        // Trigger action for extensions
        do_action('elementor_mcp_all_page_cache_invalidated');

        return true;
    }

    /**
     * Check if cached data is valid.
     *
     * Validates cached data based on version and post modified time.
     *
     * @since 1.1.0
     * @param int   $post_id     The post ID.
     * @param array $cached_data The cached data array.
     * @return bool True if cache is valid, false otherwise.
     */
    private static function is_cache_valid($post_id, $cached_data) {
        // Check version
        if (!isset($cached_data['version']) || $cached_data['version'] !== self::CACHE_VERSION) {
            return false;
        }

        // Check if post has been modified
        $post_modified = get_post_modified_time('U', false, $post_id);
        if ($post_modified && isset($cached_data['post_modified'])) {
            if ($post_modified > $cached_data['post_modified']) {
                return false;
            }
        }

        // Check custom validity filters
        return apply_filters('elementor_mcp_page_cache_is_valid', true, $post_id, $cached_data);
    }

    /**
     * Get cache key for a post.
     *
     * Generates a unique cache key for a post ID.
     *
     * @since 1.1.0
     * @param int $post_id The post ID.
     * @return string The cache key.
     */
    private static function get_cache_key($post_id) {
        $version_key = 'cache_version_' . self::CACHE_GROUP;
        $version = (int) get_transient($version_key);

        return sprintf('page_%d_v%d_%s', $post_id, $version, self::CACHE_VERSION);
    }

    /**
     * Get cache statistics.
     *
     * Returns statistics about cache usage.
     *
     * @since 1.1.0
     * @return array Cache statistics.
     */
    public static function get_stats() {
        // This would need a more sophisticated implementation
        // with tracking of hits/misses
        return [
            'group' => self::CACHE_GROUP,
            'expiry' => self::CACHE_EXPIRY,
            'version' => self::CACHE_VERSION
        ];
    }

    /**
     * Preload cache for multiple posts.
     *
     * Batch loads page data into cache for better performance.
     *
     * @since 1.1.0
     * @param array $post_ids Array of post IDs to preload.
     * @return int Number of posts cached.
     */
    public static function preload($post_ids) {
        $cached_count = 0;

        foreach ($post_ids as $post_id) {
            // Check if already cached
            if (self::get($post_id) !== false) {
                continue;
            }

            // Get document
            $document = \Elementor\Plugin::$instance->documents->get($post_id);
            if (!$document) {
                continue;
            }

            // Get and cache elements data
            $elements = $document->get_elements_data();
            if (self::set($post_id, $elements)) {
                $cached_count++;
            }
        }

        return $cached_count;
    }
}