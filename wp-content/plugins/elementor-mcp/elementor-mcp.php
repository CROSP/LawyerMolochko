<?php
/**
 * Plugin Name: Elementor MCP
 * Description: Model Context Protocol integration for Elementor
 * Version: 1.0.0
 * Author: Elementor MCP Team
 * Text Domain: elementor-mcp
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ELEMENTOR_MCP_VERSION', '1.0.0');
define('ELEMENTOR_MCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ELEMENTOR_MCP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Initialize the plugin
add_action('init', 'elementor_mcp_init');

function elementor_mcp_init() {
    // Register REST API endpoint
    add_action('rest_api_init', 'elementor_mcp_register_routes');
    
    // Check if Elementor is active
    if (!did_action('elementor/loaded')) {
        add_action('admin_notices', 'elementor_mcp_elementor_not_active_notice');
        return;
    }
}

function elementor_mcp_register_routes() {
    register_rest_route('elementor-mcp/v1', '/rpc', [
        'methods' => 'POST',
        'callback' => 'elementor_mcp_rpc_handler',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);
    
    register_rest_route('elementor-mcp/v1', '/elements', [
        'methods' => 'GET',
        'callback' => 'elementor_mcp_get_elements',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ]);
}

function elementor_mcp_rpc_handler($request) {
    $params = $request->get_params();
    
    // Handle MCP JSON-RPC requests
    if (isset($params['method'])) {
        switch ($params['method']) {
            case 'get_page_structure':
                return elementor_mcp_get_page_structure($params);
            case 'update_element':
                return elementor_mcp_update_element($params);
            case 'create_element':
                return elementor_mcp_create_element($params);
            default:
                return new WP_Error('unknown_method', 'Unknown method', ['status' => 400]);
        }
    }
    
    return new WP_Error('invalid_request', 'Invalid request', ['status' => 400]);
}

function elementor_mcp_get_page_structure($params) {
    $post_id = isset($params['post_id']) ? intval($params['post_id']) : get_the_ID();
    
    if (!$post_id) {
        return new WP_Error('no_post_id', 'Post ID required', ['status' => 400]);
    }
    
    if (!class_exists('Elementor\Plugin')) {
        return new WP_Error('elementor_not_active', 'Elementor is not active', ['status' => 503]);
    }
    
    $document = Elementor\Plugin::$instance->documents->get($post_id);
    
    if (!$document) {
        return new WP_Error('document_not_found', 'Document not found', ['status' => 404]);
    }
    
    $elements = $document->get_elements_data();
    
    return [
        'success' => true,
        'data' => [
            'post_id' => $post_id,
            'elements' => elementor_mcp_format_elements($elements),
        ],
    ];
}

function elementor_mcp_format_elements($elements) {
    $formatted = [];
    
    foreach ($elements as $element) {
        $item = [
            'id' => $element['id'],
            'type' => $element['elType'],
            'widgetType' => isset($element['widgetType']) ? $element['widgetType'] : '',
            'settings' => isset($element['settings']) ? $element['settings'] : [],
        ];
        
        if (isset($element['elements']) && !empty($element['elements'])) {
            $item['children'] = elementor_mcp_format_elements($element['elements']);
        }
        
        $formatted[] = $item;
    }
    
    return $formatted;
}

function elementor_mcp_get_elements($request) {
    $elements = [];
    
    // Get all Elementor widgets
    if (class_exists('Elementor\Plugin')) {
        $widget_manager = Elementor\Plugin::$instance->widgets_manager;
        $widgets = $widget_manager->get_widget_types();
        
        foreach ($widgets as $widget) {
            $elements[] = [
                'name' => $widget->get_name(),
                'title' => $widget->get_title(),
                'icon' => $widget->get_icon(),
                'categories' => $widget->get_categories(),
            ];
        }
    }
    
    return [
        'success' => true,
        'data' => $elements,
    ];
}

function elementor_mcp_elementor_not_active_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Elementor MCP requires Elementor to be installed and activated.', 'elementor-mcp'); ?></p>
    </div>
    <?php
}

// Add admin menu
add_action('admin_menu', 'elementor_mcp_admin_menu');
function elementor_mcp_admin_menu() {
    add_options_page(
        'Elementor MCP Settings',
        'Elementor MCP',
        'manage_options',
        'elementor-mcp',
        'elementor_mcp_settings_page'
    );
}

function elementor_mcp_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Elementor MCP Settings', 'elementor-mcp'); ?></h1>
        <p><?php _e('Elementor Model Context Protocol is active and ready to use.', 'elementor-mcp'); ?></p>
        <p><?php _e('Configure your Claude Desktop to connect to this WordPress site for Elementor integration.', 'elementor-mcp'); ?></p>
    </div>
    <?php
}
?>
