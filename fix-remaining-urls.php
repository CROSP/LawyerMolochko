<?php
/**
 * Fix Remaining Old URLs
 * Handles escaped and encoded URLs
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

global $wpdb;

$current_site_url = 'https://lawyermolochko.ddev.site:8443';

echo "Fixing remaining old URLs...\n\n";

// Old URLs with various formats
$old_urls = [
    'http://localhost/theme_powerlegal',
    'https://lawyermolochko.ddev.site:8080',
    'http://lawyermolochko.ddev.site:8080',
    // Escaped versions
    'http:\/\/localhost\/theme_powerlegal',
    'https:\/\/lawyermolochko.ddev.site:8080',
    'http:\/\/lawyermolochko.ddev.site:8080',
    // URL encoded
    'http%3A%2F%2Flocalhost%2Ftheme_powerlegal',
    'https%3A%2F%2Flawyermolochko.ddev.site%3A8080',
];

$total = 0;

foreach ($old_urls as $old_url) {
    echo "Replacing: {$old_url}\n";
    
    // Determine replacement URL (escape if needed)
    $new_url = $current_site_url;
    if (strpos($old_url, '\\/') !== false) {
        $new_url = str_replace('/', '\\/', $new_url);
    } elseif (strpos($old_url, '%') !== false) {
        $new_url = urlencode($current_site_url);
    }
    
    // Update postmeta
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s)",
            $old_url,
            $new_url
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "  - Post meta: {$rows} rows\n";
        $total += $rows;
    }
    
    // Update posts
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)",
            $old_url,
            $new_url
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "  - Posts: {$rows} rows\n";
        $total += $rows;
    }
    
    // Update options
    $rows = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->options} SET option_value = REPLACE(option_value, %s, %s)",
            $old_url,
            $new_url
        )
    );
    
    if ($rows !== false && $rows > 0) {
        echo "  - Options: {$rows} rows\n";
        $total += $rows;
    }
}

// Clear caches
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}
wp_cache_flush();

echo "\nTotal fixes: {$total}\nDone!\n";




