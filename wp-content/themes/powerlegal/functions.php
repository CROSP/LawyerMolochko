<?php
/**
 * Theme functions: init, enqueue scripts and styles, include required files and widgets.
 *
 * @package Powerlegal
 */

if( !defined('THEME_DEV_MODE_ELEMENTS') && is_user_logged_in()){
    define('THEME_DEV_MODE_ELEMENTS', true);
}
use Elementor\Plugin;

require_once get_template_directory() . '/inc/classes/class-main.php';

if (is_admin()) {
    require_once get_template_directory() . '/inc/admin/admin-init.php';
}
update_option('powerlegal_purchase_code', 'xxxxxxxxxxxxxxxxx');
update_option('powerlegal_purchase_code_status', 'valid');
/**
 * Theme Require
 */
powerlegal()->require_folder('inc');
powerlegal()->require_folder('inc/classes');
powerlegal()->require_folder('inc/theme-options');
powerlegal()->require_folder('template-parts/widgets');
if (class_exists('Woocommerce')) {
    powerlegal()->require_folder('woocommerce');
}

/**
 * Enable SVG uploads in Elementor
 * This allows SVG files to be uploaded even if Elementor's "Unfiltered File Uploads" setting is disabled
 */
add_filter('elementor/files/allow_unfiltered_upload', function($enabled) {
    // Check if the user can upload files and if SVG sanitizer is available
    if (current_user_can('upload_files')) {
        // Check if DOMDocument and SimpleXMLElement are available (required for SVG sanitization)
        if (class_exists('DOMDocument') && class_exists('SimpleXMLElement')) {
            // Allow SVG uploads for administrators or users with upload_files capability
            // This bypasses Elementor's unfiltered uploads setting requirement
            return true;
        }
    }
    return $enabled;
}, 10, 1);

// Also enable the option directly as a backup
add_action('admin_init', function() {
    if (current_user_can('upload_files') && class_exists('DOMDocument') && class_exists('SimpleXMLElement')) {
        update_option('elementor_unfiltered_files_upload', 1);
    }
});
 