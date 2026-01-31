<?php
/**
 * Replace "Power Legal" and "PowerLegal Law" with "Адвокатське бюро Тараса Молочко"
 * in all _elementor_data postmeta.
 *
 * Run: ddev exec "php /var/www/html/replace-company-name.php"
 * Make sure to backup DB before running.
 */

require_once __DIR__ . '/wp-load.php';

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

$replace = [
    'Power Legal' => 'Адвокатське бюро Тараса Молочко',
    'PowerLegal Law' => 'Адвокатське бюро «Тараса Молочко»',
];

global $wpdb;
$ids = $wpdb->get_col(
    "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' "
    . "AND (meta_value LIKE '%Power Legal%' OR meta_value LIKE '%PowerLegal Law%')"
);

if (empty($ids)) {
    echo "Nothing to replace.\n";
    exit(0);
}

$updated = 0;
foreach ($ids as $meta_id) {
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_id = %d",
        $meta_id
    ), ARRAY_A);
    if (!$row) continue;

    $val = $row['meta_value'];
    $new = $val;
    foreach ($replace as $from => $to) {
        $new = str_replace($from, $to, $new);
    }
    if ($new === $val) continue;

    $r = $wpdb->update(
        $wpdb->postmeta,
        ['meta_value' => $new],
        ['meta_id' => $meta_id]
    );
    if ($r !== false) {
        $updated++;
        echo "Updated post_id {$row['post_id']}\n";
    }
}

echo "Done. Updated $updated postmeta row(s).\n";
