<?php
/**
 * Force Update Elementor Element Description
 * Directly updates the database and Elementor cache
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

// Check if Elementor is active
if (!class_exists('\Elementor\Plugin')) {
    die('Elementor plugin is not active');
}

$page_id = 222; // Homepage ID
$element_id = '322ae9a'; // Free Consultation widget ID

// Old text patterns to find
$old_texts = [
    'Якщо вам потрібна допомога юриста, ми надамо вам безкоштовну консультацію незалежно від справи. Розвивайтеся органічно з цілісним світоглядом.',
    'Якщо вам потрібна допомога юриста, ми надамо вам безкоштовну консультацію незалежно від справи',
    'Розвивайтеся органічно з цілісним світоглядом',
];

// New text
$new_description = 'Отримайте безкоштовну консультацію від досвідчених юристів. Ми захищаємо ваші інтереси у кримінальних справах, ДТП, військових питаннях, сімейних та трудових спорах. Прозорість, чесність та ефективність — наші принципи роботи.';

echo "Force updating Elementor element description...\n\n";
echo "Page ID: {$page_id}\n";
echo "Element ID: {$element_id}\n";
echo "New description: {$new_description}\n\n";

// Get the document
$document = \Elementor\Plugin::$instance->documents->get($page_id);

if (!$document) {
    die("Error: Document not found for page ID {$page_id}\n");
}

// Get elements data
$elements = $document->get_elements_data();

if (empty($elements)) {
    die("Error: No elements found in document\n");
}

// Function to find and update element by ID
function findAndUpdateElementById(&$elements, $target_id, $new_description, $old_texts) {
    foreach ($elements as &$element) {
        if (isset($element['id']) && $element['id'] === $target_id) {
            echo "Found element with ID: {$target_id}\n";
            
            // Check current description
            if (isset($element['settings']['description'])) {
                $current = $element['settings']['description'];
                echo "Current description: {$current}\n";
                
                // Check if it matches any old text
                $needs_update = false;
                foreach ($old_texts as $old_text) {
                    if (stripos($current, $old_text) !== false) {
                        $needs_update = true;
                        echo "Matches old text pattern!\n";
                        break;
                    }
                }
                
                if ($needs_update || stripos($current, 'Розвивайтеся органічно') !== false) {
                    $element['settings']['description'] = $new_description;
                    echo "✅ Updated description!\n";
                    return true;
                } else {
                    // Force update anyway
                    $element['settings']['description'] = $new_description;
                    echo "✅ Force updated description!\n";
                    return true;
                }
            } else {
                // Add description if it doesn't exist
                $element['settings']['description'] = $new_description;
                echo "✅ Added new description!\n";
                return true;
            }
        }
        
        // Recursively search in child elements
        if (isset($element['elements']) && is_array($element['elements'])) {
            if (findAndUpdateElementById($element['elements'], $target_id, $new_description, $old_texts)) {
                return true;
            }
        }
    }
    
    return false;
}

// Find and update the element
$updated = findAndUpdateElementById($elements, $element_id, $new_description, $old_texts);

if ($updated) {
    // Save the document
    echo "\nSaving document...\n";
    $result = $document->save(['elements' => $elements]);
    
    if ($result) {
        echo "✅ Document saved successfully!\n";
    } else {
        echo "⚠️  Document save returned false, but continuing...\n";
    }
    
    // Also update directly in post meta as backup
    echo "\nUpdating post meta directly...\n";
    $meta_data = get_post_meta($page_id, '_elementor_data', true);
    
    if ($meta_data) {
        $data = json_decode($meta_data, true);
        if ($data) {
            // Update in the data structure
            function updateInMetaData(&$data, $target_id, $new_text) {
                if (is_array($data)) {
                    foreach ($data as &$item) {
                        if (isset($item['id']) && $item['id'] === $target_id) {
                            if (isset($item['settings']['description'])) {
                                $item['settings']['description'] = $new_text;
                                return true;
                            }
                        }
                        if (isset($item['elements']) && is_array($item['elements'])) {
                            if (updateInMetaData($item['elements'], $target_id, $new_text)) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }
            
            if (updateInMetaData($data, $element_id, $new_description)) {
                $new_meta = wp_slash(wp_json_encode($data));
                update_post_meta($page_id, '_elementor_data', $new_meta);
                echo "✅ Post meta updated!\n";
            }
        }
    }
    
    // Clear ALL caches
    echo "\nClearing caches...\n";
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
    
    // Clear WordPress caches
    wp_cache_flush();
    
    // Clear object cache if available
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete('elementor_document_' . $page_id);
    }
    
    // Delete Elementor CSS cache (try different methods)
    try {
        $uploads = wp_upload_dir();
        $css_file = $uploads['basedir'] . '/elementor/css/post-' . $page_id . '.css';
        if (file_exists($css_file)) {
            @unlink($css_file);
            echo "✅ Deleted CSS cache file\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Could not delete CSS cache (not critical)\n";
    }
    
    echo "\n✅ All caches cleared!\n";
    
} else {
    echo "\n❌ Error: Element with ID '{$element_id}' not found!\n";
    echo "Searching for all elements...\n";
    
    function printAllElements($elements, $indent = '') {
        foreach ($elements as $element) {
            if (isset($element['id'])) {
                $type = $element['elType'] ?? 'unknown';
                $widget = $element['widgetType'] ?? '';
                $title = $element['settings']['title'] ?? '';
                $desc = isset($element['settings']['description']) ? substr($element['settings']['description'], 0, 50) : '';
                echo "{$indent}- ID: {$element['id']} | Type: {$type}";
                if ($widget) echo " | Widget: {$widget}";
                if ($title) echo " | Title: {$title}";
                if ($desc) echo " | Desc: {$desc}...";
                echo "\n";
            }
            if (isset($element['elements']) && is_array($element['elements'])) {
                printAllElements($element['elements'], $indent . '  ');
            }
        }
    }
    
    printAllElements($elements);
}

echo "\n═══════════════════════════════════════\n";
echo "Done! Please refresh your browser with Ctrl+Shift+R (hard refresh)\n";
echo "═══════════════════════════════════════\n";

