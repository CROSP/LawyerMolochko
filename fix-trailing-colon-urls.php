<?php
/**
 * Fix Trailing Colon URLs
 * Fixes URLs with :8443:/ pattern (trailing colon after port)
 * 
 * Usage: ddev wp eval-file fix-trailing-colon-urls.php
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

global $wpdb;

echo "Fixing trailing colon URLs (e.g., :8443:/ -> :8443/)...\n\n";

$total = 0;

// Use simple string replacement for common cases (more reliable than regex in MySQL)
$common_fixes = [
    ':8443:/' => ':8443/',
    ':8443:?' => ':8443?',
    ':8443:' => ':8443',
    ':8080:/' => ':8080/',
    ':8080:?' => ':8080?',
    ':8080:' => ':8080',
];

// Fix in Elementor data and all other tables

foreach ($common_fixes as $old => $new) {
    // Fix in post content
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts} 
            SET post_content = REPLACE(post_content, %s, %s)
            WHERE post_content LIKE %s",
            $old,
            $new,
            '%' . $wpdb->esc_like($old) . '%'
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "Post content ({$old}): {$rows} rows\n";
        $total += $rows;
    }
    
    // Fix in post meta
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} 
            SET meta_value = REPLACE(meta_value, %s, %s)
            WHERE meta_value LIKE %s",
            $old,
            $new,
            '%' . $wpdb->esc_like($old) . '%'
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "Post meta ({$old}): {$rows} rows\n";
        $total += $rows;
    }
    
    // Fix in options
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->options} 
            SET option_value = REPLACE(option_value, %s, %s)
            WHERE option_value LIKE %s",
            $old,
            $new,
            '%' . $wpdb->esc_like($old) . '%'
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "Options ({$old}): {$rows} rows\n";
        $total += $rows;
    }
}

// Clear caches
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}
wp_cache_flush();

echo "\nTotal fixes: {$total}\nDone!\n";

