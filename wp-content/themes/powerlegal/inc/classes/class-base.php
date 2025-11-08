<?php
/**
* @package Powerlegal
*/

if( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if( !class_exists( 'Powerlegal_Base' ) ){
	class Powerlegal_Base {

	 
		public function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			add_action( $hook, array( &$this, $function_to_add ), $priority, $accepted_args );
		}

		 
		public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			add_filter( $tag, array( &$this, $function_to_add ), $priority, $accepted_args );
		}


	}
}
 

// Helper Function ---------------------------------------

if( ! function_exists( 'powerlegal_action' ) ) :
	function powerlegal_action() {

		$args   = func_get_args();

		if( !isset( $args[0] ) || empty( $args[0] ) ) {
			return;
		}

		$action = 'pxltheme_' . $args[0];
		unset( $args[0] );

		do_action_ref_array( $action, $args );
	}
endif;
 
