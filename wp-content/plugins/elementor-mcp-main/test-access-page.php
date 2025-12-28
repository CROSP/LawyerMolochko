<?php
/**
 * Test script to access homepage and find the Free Consultation container
 * Run with: ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/test-access-page.php
 */

require_once '/var/www/html/wp-load.php';
require_once __DIR__ . '/includes/class-autoloader.php';

\ElementorMCP\Autoloader::run();

// Set current user to admin (user ID 1) for permissions
wp_set_current_user(1);

echo "=== Accessing Homepage (ID: 222) ===\n\n";

// Initialize tool manager
$tool_manager = new \ElementorMCP\Tools\Tool_Manager();
$tool_manager->auto_discover_tools();

// Get the document
echo "1. Getting document data...\n";
$result = $tool_manager->execute_tool('get_document', [
    'post_id' => 222
]);

if (!$result['success']) {
    echo "ERROR: " . $result['error']['message'] . "\n";
    exit(1);
}

$document = $result['data'];
echo "   ✓ Document retrieved\n";
echo "   - Post Title: " . $document['post']['post_title'] . "\n";
echo "   - Elements count: " . count($document['elements']) . "\n\n";

// Search for elements containing "Free Consultation"
echo "2. Searching for 'Free Consultation' elements...\n";
$find_result = $tool_manager->execute_tool('elementor_find_elements', [
    'post_id' => 222,
    'criteria' => [
        'setting_contains' => [
            'title' => 'Free Consultation'
        ]
    ],
    'limit' => 10
]);

if ($find_result['success']) {
    echo "   ✓ Found " . count($find_result['data']['elements']) . " matching elements\n";
    foreach ($find_result['data']['elements'] as $idx => $element) {
        echo "\n   Element #" . ($idx + 1) . ":\n";
        echo "   - ID: " . ($element['id'] ?? 'N/A') . "\n";
        echo "   - Type: " . ($element['elType'] ?? 'N/A') . "\n";
        echo "   - Widget Type: " . ($element['widgetType'] ?? 'N/A') . "\n";
        echo "   - Path: " . ($element['path'] ?? 'N/A') . "\n";
        if (isset($element['settings']['title'])) {
            echo "   - Title: " . $element['settings']['title'] . "\n";
        }
    }
} else {
    echo "   ✗ Search failed: " . $find_result['error']['message'] . "\n";
}

// Also search for fancy-box widgets
echo "\n3. Searching for fancy-box widgets...\n";
$fancy_result = $tool_manager->execute_tool('elementor_find_elements', [
    'post_id' => 222,
    'criteria' => [
        'setting_contains' => [
            'pxl_fancy_box_layout' => 'layout-4'
        ]
    ],
    'limit' => 10
]);

if ($fancy_result['success']) {
    echo "   ✓ Found " . count($fancy_result['data']['elements']) . " fancy-box elements\n";
} else {
    // Try searching by widget type
    echo "   Trying alternative search...\n";
    $alt_result = $tool_manager->execute_tool('elementor_find_elements', [
        'post_id' => 222,
        'criteria' => [
            'widgetType' => 'pxl-fancy-box'
        ],
        'limit' => 10
    ]);
    
    if ($alt_result['success']) {
        echo "   ✓ Found " . count($alt_result['data']['elements']) . " pxl-fancy-box widgets\n";
        foreach ($alt_result['data']['elements'] as $idx => $element) {
            echo "\n   Widget #" . ($idx + 1) . ":\n";
            echo "   - ID: " . ($element['id'] ?? 'N/A') . "\n";
            echo "   - Path: " . ($element['path'] ?? 'N/A') . "\n";
            if (isset($element['settings'])) {
                echo "   - Settings keys: " . implode(', ', array_keys($element['settings'])) . "\n";
            }
        }
    }
}

// Get a specific element if we found one
if (isset($find_result['data']['elements'][0])) {
    $element_id = $find_result['data']['elements'][0]['id'] ?? null;
    if ($element_id) {
        echo "\n4. Getting full element data for ID: $element_id\n";
        $element_result = $tool_manager->execute_tool('elementor_get_element', [
            'post_id' => 222,
            'element_id' => $element_id
        ]);
        
        if ($element_result['success']) {
            echo "   ✓ Element retrieved\n";
            $element_data = $element_result['data']['element'];
            echo "   - Full element structure available\n";
            if (isset($element_data['settings'])) {
                echo "   - Settings count: " . count($element_data['settings']) . "\n";
            }
        }
    }
}

echo "\n=== Complete ===\n";

