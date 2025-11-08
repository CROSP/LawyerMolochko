<?php

if( !defined( 'ABSPATH' ) )
	exit; 

class Powerlegal_Admin_Templates extends Powerlegal_Base{

	public function __construct() {
		$this->add_action( 'admin_menu', 'register_page', 20 );
	}
 
	public function register_page() {
		add_submenu_page(
			'pxlart',
		    esc_html__( 'Templates', 'powerlegal' ),
		    esc_html__( 'Templates', 'powerlegal' ),
		    'manage_options',
		    'edit.php?post_type=pxl-template',
		    false
		);
	}
}
new Powerlegal_Admin_Templates;
