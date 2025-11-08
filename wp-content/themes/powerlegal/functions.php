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
 