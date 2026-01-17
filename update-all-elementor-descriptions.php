<?php
/**
 * Update ALL Elementor Elements with Old Text
 * Finds and updates all instances of the old description text across all pages
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

// Check if Elementor is active
if (!class_exists('\Elementor\Plugin')) {
    die('Elementor plugin is not active');
}

// Old text to find and replace
$old_text_patterns = [
    'Якщо вам потрібна допомога юриста, ми надамо вам безкоштовну консультацію незалежно від справи. Розвивайтеся органічно з цілісним світоглядом.',
    'Якщо вам потрібна допомога юриста, ми надамо вам безкоштовну консультацію незалежно від справи',
    'Розвивайтеся органічно з цілісним світоглядом',
];

// New Ukrainian description text
$new_description = 'Отримайте безкоштовну консультацію від досвідчених юристів. Ми захищаємо ваші інтереси у кримінальних справах, ДТП, військових питаннях, сімейних та трудових спорах. Прозорість, чесність та ефективність — наші принципи роботи.';

echo "Searching for and updating all Elementor elements with old text...\n\n";

// Get all Elementor pages
$pages = get_posts([
    'post_type' => ['page', 'post'],
    'meta_query' => [
        [
            'key' => '_elementor_edit_mode',
            'compare' => 'EXISTS'
        ]
    ],
    'posts_per_page' => -1,
    'post_status' => 'any'
]);

echo "Found " . count($pages) . " Elementor pages to check.\n\n";

$total_updated = 0;

// Function to recursively search and update elements
function findAndUpdateTextInElements(&$elements, $old_patterns, $new_text, &$updated_count, $page_id, $path = '') {
    foreach ($elements as &$element) {
        $current_path = $path . '/' . ($element['id'] ?? 'unknown');
        
        // Check description field
        if (isset($element['settings']['description'])) {
            $description = $element['settings']['description'];
            
            foreach ($old_patterns as $old_pattern) {
                if (stripos($description, $old_pattern) !== false) {
                    echo "Found old text in element {$element['id']} on page {$page_id}\n";
                    echo "  Path: {$current_path}\n";
                    echo "  Old: {$description}\n";
                    
                    $element['settings']['description'] = $new_text;
                    $updated_count++;
                    
                    echo "  ✅ Updated!\n\n";
                    break;
                }
            }
        }
        
        // Check title field
        if (isset($element['settings']['title'])) {
            $title = $element['settings']['title'];
            foreach ($old_patterns as $old_pattern) {
                if (stripos($title, $old_pattern) !== false) {
                    echo "Found old text in title of element {$element['id']} on page {$page_id}\n";
                    echo "  Path: {$current_path}\n";
                    echo "  Old: {$title}\n";
                    // Don't update title, just log it
                    echo "  ⚠️  Found in title (not updating)\n\n";
                    break;
                }
            }
        }
        
        // Recursively search in child elements
        if (isset($element['elements']) && is_array($element['elements'])) {
            findAndUpdateTextInElements($element['elements'], $old_patterns, $new_text, $updated_count, $page_id, $current_path);
        }
    }
}

// Process each page
foreach ($pages as $page) {
    $document = \Elementor\Plugin::$instance->documents->get($page->ID);
    
    if (!$document) {
        continue;
    }
    
    $elements = $document->get_elements_data();
    
    if (empty($elements)) {
        continue;
    }
    
    echo "Checking page: {$page->post_title} (ID: {$page->ID})\n";
    
    $page_updated = 0;
    findAndUpdateTextInElements($elements, $old_text_patterns, $new_description, $page_updated, $page->ID);
    
    if ($page_updated > 0) {
        // Save the document
        $document->save(['elements' => $elements]);
        echo "  💾 Saved page {$page->ID}\n";
        $total_updated += $page_updated;
    }
    
    echo "\n";
}

// Also check post meta directly for any escaped/encoded versions
global $wpdb;

echo "Checking post meta for old text patterns...\n";

foreach ($old_text_patterns as $old_pattern) {
    // Search in _elementor_data meta
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta} 
         WHERE meta_key = '_elementor_data' 
         AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($old_pattern) . '%'
    ));
    
    if (!empty($results)) {
        echo "Found old text in post meta for " . count($results) . " posts\n";
        
        foreach ($results as $result) {
            $post_id = $result->post_id;
            $meta_value = $result->meta_value;
            
            // Decode JSON
            $data = json_decode($meta_value, true);
            
            if ($data) {
                $updated_in_meta = false;
                
                // Recursively update
                function updateInData(&$data, $old_patterns, $new_text, &$updated_flag) {
                    if (is_array($data)) {
                        foreach ($data as $key => &$value) {
                            if ($key === 'settings' && is_array($value)) {
                                if (isset($value['description'])) {
                                    foreach ($old_patterns as $old_pattern) {
                                        if (stripos($value['description'], $old_pattern) !== false) {
                                            $value['description'] = $new_text;
                                            $updated_flag = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (is_array($value)) {
                                updateInData($value, $old_patterns, $new_text, $updated_flag);
                            }
                        }
                    }
                }
                
                updateInData($data, $old_text_patterns, $new_description, $updated_in_meta);
                
                if ($updated_in_meta) {
                    // Save back
                    $new_meta_value = wp_slash(wp_json_encode($data));
                    update_post_meta($post_id, '_elementor_data', $new_meta_value);
                    echo "  ✅ Updated post meta for post ID: {$post_id}\n";
                    $total_updated++;
                }
            }
        }
    }
}

// Clear Elementor cache
\Elementor\Plugin::$instance->files_manager->clear_cache();
wp_cache_flush();

echo "\n";
echo "═══════════════════════════════════════\n";
echo "✅ Total elements updated: {$total_updated}\n";
echo "✅ Elementor cache cleared\n";
echo "✅ WordPress cache flushed\n";
echo "═══════════════════════════════════════\n";
echo "\nDone!\n";



