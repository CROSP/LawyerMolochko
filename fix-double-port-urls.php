<?php
/**
 * Fix Double Port URLs
 * Fixes URLs with :8443:8443 pattern
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

global $wpdb;

$old_url = 'https://lawyermolochko.ddev.site:8443:8443';
$new_url = 'https://lawyermolochko.ddev.site:8443';

echo "Fixing double port URLs...\n\n";

$total = 0;

// Fix in Elementor data
$escaped_old = str_replace('/', '\\/', $old_url);
$escaped_new = str_replace('/', '\\/', $new_url);

$rows = $wpdb->query(
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

if ($rows !== false) {
    echo "Elementor data: {$rows} rows\n";
    $total += $rows;
}

// Fix in post content
$rows = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->posts} 
        SET post_content = REPLACE(post_content, %s, %s)",
        $old_url,
        $new_url
    )
);

if ($rows !== false) {
    echo "Post content: {$rows} rows\n";
    $total += $rows;
}

// Fix in post meta
$rows = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->postmeta} 
        SET meta_value = REPLACE(meta_value, %s, %s)",
        $old_url,
        $new_url
    )
);

if ($rows !== false) {
    echo "Post meta: {$rows} rows\n";
    $total += $rows;
}

// Fix in options
$rows = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->options} 
        SET option_value = REPLACE(option_value, %s, %s)",
        $old_url,
        $new_url
    )
);

if ($rows !== false) {
    echo "Options: {$rows} rows\n";
    $total += $rows;
}

// Clear caches
if (class_exists('\Elementor\Plugin')) {
    \Elementor\Plugin::$instance->files_manager->clear_cache();
}
wp_cache_flush();

echo "\nTotal fixes: {$total}\nDone!\n";




