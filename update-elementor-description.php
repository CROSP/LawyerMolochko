<?php
/**
 * Update Elementor Element Description
 * Updates the description text for the Free Consultation widget on homepage
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

// New Ukrainian description text - Professional and compelling
$new_description = 'Отримайте безкоштовну консультацію від досвідчених юристів. Ми захищаємо ваші інтереси у кримінальних справах, ДТП, військових питаннях, сімейних та трудових спорах. Прозорість, чесність та ефективність — наші принципи роботи.';

echo "Updating Elementor element description...\n\n";
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

// Function to find and update element recursively
function findAndUpdateElement(&$elements, $target_id, $new_description) {
    foreach ($elements as &$element) {
        if (isset($element['id']) && $element['id'] === $target_id) {
            if (isset($element['settings']['description'])) {
                $old_description = $element['settings']['description'];
                $element['settings']['description'] = $new_description;
                echo "Found element!\n";
                echo "Old description: {$old_description}\n";
                echo "New description: {$new_description}\n";
                return true;
            } else {
                echo "Warning: Element found but 'description' setting not found. Adding it.\n";
                $element['settings']['description'] = $new_description;
                return true;
            }
        }
        
        // Recursively search in child elements
        if (isset($element['elements']) && is_array($element['elements'])) {
            if (findAndUpdateElement($element['elements'], $target_id, $new_description)) {
                return true;
            }
        }
    }
    
    return false;
}

// Find and update the element
$updated = findAndUpdateElement($elements, $element_id, $new_description);

if ($updated) {
    // Save the document
    $document->save(['elements' => $elements]);
    
    // Clear Elementor cache
    \Elementor\Plugin::$instance->files_manager->clear_cache();
    
    echo "\n✅ Element updated successfully!\n";
    echo "Elementor cache cleared.\n";
} else {
    echo "\n❌ Error: Element with ID '{$element_id}' not found in document.\n";
    echo "Available element IDs:\n";
    
    function printElementIds($elements, $indent = '') {
        foreach ($elements as $element) {
            if (isset($element['id'])) {
                echo "{$indent}- {$element['id']} ({$element['elType']}";
                if (isset($element['widgetType'])) {
                    echo ", {$element['widgetType']}";
                }
                echo ")\n";
            }
            if (isset($element['elements']) && is_array($element['elements'])) {
                printElementIds($element['elements'], $indent . '  ');
            }
        }
    }
    
    printElementIds($elements);
}

echo "\nDone!\n";

