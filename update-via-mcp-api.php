<?php
/**
 * Update Elementor Element via MCP REST API
 * Simulates MCP call using WordPress REST API
 */

require_once(__DIR__ . '/wp-load.php');

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

$page_id = 222;
$element_id = '322ae9a';
$new_description = 'Отримайте безкоштовну консультацію від досвідчених юристів. Ми захищаємо ваші інтереси у кримінальних справах, ДТП, військових питаннях, сімейних та трудових спорах. Прозорість, чесність та ефективність — наші принципи роботи.';

echo "Updating via MCP REST API...\n\n";

// Simulate MCP update_element call
$request = new WP_REST_Request('POST', '/elementor-mcp/v1/rpc');
$request->set_body_params([
    'method' => 'update_element',
    'params' => [
        'post_id' => $page_id,
        'element_id' => $element_id,
        'settings' => [
            'description' => $new_description
        ]
    ]
]);

// Check if the REST route exists
$routes = rest_get_server()->get_routes();
if (isset($routes['/elementor-mcp/v1/rpc'])) {
    echo "✅ MCP REST API route found!\n";
    
    // Make the request
    $response = rest_do_request($request);
    
    if (is_wp_error($response)) {
        echo "❌ Error: " . $response->get_error_message() . "\n";
    } else {
        $data = $response->get_data();
        echo "✅ Response: " . print_r($data, true) . "\n";
    }
} else {
    echo "⚠️  MCP REST API route not found. Using direct Elementor API...\n";
    
    // Fallback to direct Elementor API
    if (!class_exists('\Elementor\Plugin')) {
        die('Elementor not active');
    }
    
    $document = \Elementor\Plugin::$instance->documents->get($page_id);
    if (!$document) {
        die("Document not found");
    }
    
    $elements = $document->get_elements_data();
    
    function updateElement(&$elements, $target_id, $new_desc) {
        foreach ($elements as &$element) {
            if (isset($element['id']) && $element['id'] === $target_id) {
                $element['settings']['description'] = $new_desc;
                return true;
            }
            if (isset($element['elements'])) {
                if (updateElement($element['elements'], $target_id, $new_desc)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    if (updateElement($elements, $element_id, $new_description)) {
        $document->save(['elements' => $elements]);
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        echo "✅ Updated via Elementor API!\n";
    }
}

echo "\nDone!\n";



