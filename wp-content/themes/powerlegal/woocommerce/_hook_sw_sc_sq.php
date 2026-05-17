<?php
add_filter( 'woosw_button_position_archive', function() {
    return 'woosmart';
} );
add_filter( 'woosw_button_position_single', function() {
    return 'woosmart';
} );

add_filter( 'woosw_button_html', 'powerlegal_change_wishlist_button_html', 10, 3 );
function powerlegal_change_wishlist_button_html($output, $id, $attrs){

	$key = isset( $_COOKIE['woosw_key'] ) ? $_COOKIE['woosw_key'] : '#';
	$added_products = [];
	if ( get_option( 'woosw_list_' . $key ) ) {
		$added_products = get_option( 'woosw_list_' . $key );
	}
	$class = 'woosw-btn woosw-btn-' . esc_attr( $attrs['id'] );
	$text = esc_html__( 'Add to wishlist', 'powerlegal' );
	if ( array_key_exists( $id, $added_products ) ) {
		$class .= ' woosw-added';
		$text = esc_html__( 'Browse wishlist', 'powerlegal' );
	}
	if ( get_option( 'woosw_button_class', '' ) !== '' ) {
		$class .= ' ' . esc_attr( get_option( 'woosw_button_class' ) );
	}

	$output = '<button class="woosmart-btn ' . esc_attr( $class ) . '" data-id="' . esc_attr( $id ) . '"><span>'.$text.'</span></button>';

	return $output;
}
 
 


