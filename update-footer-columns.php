<?php
/**
 * Narrow footer logo column and widen Quick Links, Practice Areas, Newsletter.
 * Post 928 (footer template).
 * - 152c680 (logo): 55% -> 40%
 * - 742b223, 1d857df, cc529ed: 15% -> 20% each
 */

require_once __DIR__ . '/wp-load.php';

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

$post_id = 928;

$updates = [
    '152c680' => ['_column_size' => 40, '_inline_size' => 40],
    '742b223' => ['_column_size' => 20, '_inline_size' => 20],
    '1d857df' => ['_column_size' => 20, '_inline_size' => 20],
    'cc529ed' => ['_column_size' => 20, '_inline_size' => 20],
];

if (!class_exists('\Elementor\Plugin')) {
    die("Elementor not active\n");
}

$document = \Elementor\Plugin::$instance->documents->get($post_id);
if (!$document) {
    die("Document not found for post $post_id\n");
}

$elements = $document->get_elements_data();
if (!$elements) {
    die("No elements in document\n");
}

function update_column_in_elements(&$elements, $target_id, $new_settings) {
    foreach ($elements as &$el) {
        if (isset($el['id']) && $el['id'] === $target_id) {
            if (!isset($el['settings'])) $el['settings'] = [];
            $el['settings'] = array_merge($el['settings'], $new_settings);
            $el['modified'] = time();
            return true;
        }
        if (!empty($el['elements']) && update_column_in_elements($el['elements'], $target_id, $new_settings)) {
            return true;
        }
    }
    return false;
}

$ok = true;
foreach ($updates as $id => $sets) {
    if (update_column_in_elements($elements, $id, $sets)) {
        echo "Updated column $id => " . $sets['_column_size'] . "%\n";
    } else {
        echo "Column $id not found\n";
        $ok = false;
    }
}

if (!$ok) {
    exit(1);
}

$result = $document->save(['elements' => $elements, 'settings' => $document->get_settings()]);
if (is_wp_error($result)) {
    die("Save failed: " . $result->get_error_message() . "\n");
}

\Elementor\Plugin::$instance->files_manager->clear_cache();
echo "Footer columns saved successfully.\n";
