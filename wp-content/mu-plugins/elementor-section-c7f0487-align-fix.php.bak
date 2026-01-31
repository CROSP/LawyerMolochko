<?php
/**
 * Plugin Name: Elementor Section c7f0487 – align-items fix
 * Description: Forces .elementor-container to align-items: flex-start for section c7f0487 (fancy boxes row).
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'elementor/frontend/after_enqueue_styles', function () {
	$css = '
		.elementor-element-c7f0487 > .elementor-container {
			align-items: flex-start !important;
		}
	';
	wp_add_inline_style( 'elementor-frontend', $css );
}, 20 );
