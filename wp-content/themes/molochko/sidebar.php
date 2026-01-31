<?php
/**
 * Blog sidebar. No Elementor.
 *
 * @package Molochko
 */
if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
dynamic_sidebar( 'sidebar-blog' );
