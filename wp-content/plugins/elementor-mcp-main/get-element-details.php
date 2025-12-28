<?php
/**
 * Get full details of the Free Consultation element
 */

require_once '/var/www/html/wp-load.php';
require_once __DIR__ . '/includes/class-autoloader.php';

\ElementorMCP\Autoloader::run();
wp_set_current_user(1);

$tool_manager = new \ElementorMCP\Tools\Tool_Manager();
$tool_manager->auto_discover_tools();

// Get the full element
$element_result = $tool_manager->execute_tool('elementor_get_element', [
    'post_id' => 222,
    'element_id' => '322ae9a'
]);

if ($element_result['success']) {
    $element = $element_result['data']['element'];
    
    echo "=== Free Consultation Element Details ===\n\n";
    echo "Element ID: " . ($element['id'] ?? 'N/A') . "\n";
    echo "Element Type: " . ($element['elType'] ?? 'N/A') . "\n";
    echo "Widget Type: " . ($element['widgetType'] ?? 'N/A') . "\n\n";
    
    echo "=== Settings ===\n";
    if (isset($element['settings'])) {
        foreach ($element['settings'] as $key => $value) {
            if (is_array($value)) {
                echo "$key: " . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo "$key: $value\n";
            }
        }
    }
    
    echo "\n=== Full Element Structure ===\n";
    echo json_encode($element, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} else {
    echo "ERROR: " . $element_result['error']['message'] . "\n";
}

