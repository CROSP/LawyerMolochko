<?php
/**
 * URL Replacement Script
 * Replaces old hardcoded URLs in the database with current site URL
 * 
 * Usage: ddev wp eval-file replace-old-urls.php
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

global $wpdb;

// Get current site URL - use siteurl option directly to avoid filter interference
$current_site_url = get_option('siteurl');
if (empty($current_site_url)) {
    $current_site_url = get_option('home');
}
// Normalize - remove trailing slash, colon, and ensure proper format
$current_site_url = rtrim(rtrim($current_site_url, '/'), ':');
// If URL doesn't have proper format, construct it properly
if (empty($current_site_url) || !filter_var($current_site_url, FILTER_VALIDATE_URL)) {
    // Default to HTTPS with port 8443 for ddev
    $current_site_url = 'https://lawyermolochko.ddev.site:8443';
}
// Ensure we have the correct URL format
$current_site_url = 'https://lawyermolochko.ddev.site:8443';
$current_site_url_no_port = preg_replace('/:\d+$/', '', $current_site_url);

echo "Current site URL: {$current_site_url}\n";
echo "Starting URL replacement...\n\n";

// List of old URLs to replace
$old_urls = [
    'http://localhost/theme_powerlegal',
    'https://lawyermolochko.ddev.site:8080',
    'http://lawyermolochko.ddev.site:8080',
    'https://lawyermolochko.ddev.site:8443', // In case there are variations
    'http://lawyermolochko.ddev.site:8443',
];

$total_replacements = 0;

foreach ($old_urls as $old_url) {
    echo "Replacing: {$old_url} -> {$current_site_url}\n";
    
    // 1. Replace in Elementor data (JSON format)
    $escaped_old = str_replace('/', '\\/', $old_url);
    $escaped_new = str_replace('/', '\\/', $current_site_url);
    
    $elementor_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s) 
            WHERE meta_key = '_elementor_data' 
            AND meta_value LIKE %s",
            $escaped_old,
            $escaped_new,
            '[%'
        )
    );
    
    if ($elementor_rows !== false) {
        echo "  - Elementor data: {$elementor_rows} rows updated\n";
        $total_replacements += $elementor_rows;
    }
    
    // 2. Replace in post content
    $post_content_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts} 
            SET post_content = REPLACE(post_content, %s, %s)",
            $old_url,
            $current_site_url
        )
    );
    
    if ($post_content_rows !== false) {
        echo "  - Post content: {$post_content_rows} rows updated\n";
        $total_replacements += $post_content_rows;
    }
    
    // 3. Replace in post meta (general)
    $post_meta_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s) 
            WHERE meta_key NOT IN ('_elementor_data', '_elementor_css', '_elementor_css_print_method')",
            $old_url,
            $current_site_url
        )
    );
    
    if ($post_meta_rows !== false) {
        echo "  - Post meta: {$post_meta_rows} rows updated\n";
        $total_replacements += $post_meta_rows;
    }
    
    // 4. Replace in options
    $options_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->options} 
            SET option_value = REPLACE(option_value, %s, %s)",
            $old_url,
            $current_site_url
        )
    );
    
    if ($options_rows !== false) {
        echo "  - Options: {$options_rows} rows updated\n";
        $total_replacements += $options_rows;
    }
    
    // 5. Replace in comments
    $comments_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->comments} 
            SET comment_content = REPLACE(comment_content, %s, %s)",
            $old_url,
            $current_site_url
        )
    );
    
    if ($comments_rows !== false) {
        echo "  - Comments: {$comments_rows} rows updated\n";
        $total_replacements += $comments_rows;
    }
    
    // 6. Replace in comment meta
    $comment_meta_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->commentmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s)",
            $old_url,
            $current_site_url
        )
    );
    
    if ($comment_meta_rows !== false) {
        echo "  - Comment meta: {$comment_meta_rows} rows updated\n";
        $total_replacements += $comment_meta_rows;
    }
    
    // 7. Replace in term meta
    $term_meta_rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->termmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s)",
            $old_url,
            $current_site_url
        )
    );
    
    if ($term_meta_rows !== false) {
        echo "  - Term meta: {$term_meta_rows} rows updated\n";
        $total_replacements += $term_meta_rows;
    }
    
    echo "\n";
}

// Clear Elementor cache
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    echo "Elementor cache cleared.\n";
}

// Clear WordPress cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "WordPress cache cleared.\n";
}

echo "\n=== Summary ===\n";
echo "Total replacements made: {$total_replacements}\n";
echo "Done!\n";

