<?php
/**
 * Get the full path and parent structure of the Free Consultation element
 */

require_once '/var/www/html/wp-load.php';
require_once __DIR__ . '/includes/class-autoloader.php';

\ElementorMCP\Autoloader::run();
wp_set_current_user(1);

$tool_manager = new \ElementorMCP\Tools\Tool_Manager();
$tool_manager->auto_discover_tools();

// Get the element path
$path_result = $tool_manager->execute_tool('elementor_get_element_path', [
    'post_id' => 222,
    'element_id' => '322ae9a'
]);

if ($path_result['success']) {
    echo "=== Element Path ===\n\n";
    $path = $path_result['data'];
    
    echo "Element ID: 322ae9a\n";
    echo "Full Path: " . ($path['path'] ?? 'N/A') . "\n";
    echo "Depth: " . ($path['depth'] ?? 'N/A') . "\n\n";
    
    if (isset($path['ancestors'])) {
        echo "Ancestors:\n";
        foreach ($path['ancestors'] as $idx => $ancestor) {
            echo "  " . ($idx + 1) . ". " . ($ancestor['elType'] ?? 'unknown') . 
                 " (ID: " . ($ancestor['id'] ?? 'N/A') . ")\n";
        }
    }
    
    // Get parent element
    if (isset($path['parent_id'])) {
        echo "\n=== Parent Container ===\n";
        $parent_result = $tool_manager->execute_tool('elementor_get_element', [
            'post_id' => 222,
            'element_id' => $path['parent_id']
        ]);
        
        if ($parent_result['success']) {
            $parent = $parent_result['data']['element'];
            echo "Parent ID: " . ($parent['id'] ?? 'N/A') . "\n";
            echo "Parent Type: " . ($parent['elType'] ?? 'N/A') . "\n";
            if (isset($parent['settings'])) {
                echo "Parent Settings:\n";
                foreach ($parent['settings'] as $key => $value) {
                    if (!is_array($value) && strlen($value) < 100) {
                        echo "  $key: $value\n";
                    }
                }
            }
        }
    }
    
} else {
    echo "ERROR: " . $path_result['error']['message'] . "\n";
}




