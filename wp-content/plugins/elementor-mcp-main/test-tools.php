<?php
/**
 * Test script to verify Elementor MCP tools are available
 * Run with: ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/test-tools.php
 */

require_once '/var/www/html/wp-load.php';
require_once __DIR__ . '/includes/class-autoloader.php';

\ElementorMCP\Autoloader::run();

echo "=== Elementor MCP Tools Test ===\n\n";

// Initialize tool manager
$tool_manager = new \ElementorMCP\Tools\Tool_Manager();

// Auto-discover tools
$tool_manager->auto_discover_tools();

// Get all tools
$tools = $tool_manager->get_all_tools();

echo "Total tools registered: " . count($tools) . "\n\n";
echo "Available tools:\n";
echo str_repeat("-", 50) . "\n";

foreach ($tools as $name => $tool) {
    $description = $tool->get_description();
    echo sprintf("  %-30s %s\n", $name, substr($description, 0, 50));
}

echo "\n" . str_repeat("=", 50) . "\n";

// Test a simple tool
if (isset($tools['get_elementor_info'])) {
    echo "\nTesting 'get_elementor_info' tool...\n";
    $result = $tool_manager->execute_tool('get_elementor_info', [
        'include_widgets' => true,
        'include_settings' => false,
    ]);
    
    if ($result['success']) {
        echo "✓ Tool executed successfully\n";
        echo "Elementor Version: " . ($result['data']['version'] ?? 'N/A') . "\n";
        echo "Widget Count: " . (isset($result['data']['widgets']) ? count($result['data']['widgets']) : 0) . "\n";
    } else {
        echo "✗ Tool execution failed: " . ($result['error']['message'] ?? 'Unknown error') . "\n";
    }
}

echo "\n=== Test Complete ===\n";

