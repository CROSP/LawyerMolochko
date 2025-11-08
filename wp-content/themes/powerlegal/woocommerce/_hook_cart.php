<?php
// Cart Coupon
if(!function_exists('powerlegal_woocommerce_cart_actions')){
	add_action('woocommerce_cart_actions','powerlegal_woocommerce_cart_actions', 0);
	function powerlegal_woocommerce_cart_actions(){
	?>
		<div class="pxl-cart-acctions row justify-content-between g-30">
			<?php if ( wc_coupons_enabled() ) { ?>
				<div class="coupon pxl-coupon col-12 col-md-auto">
					<div class="pxl-coupon-wrap row g-10">
						<div class="col">
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'powerlegal' ); ?>" />
						</div>
						<div class="col-auto">
							<button type="submit" class="button pxl-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'powerlegal' ); ?>"><span><?php esc_html_e( 'Apply coupon', 'powerlegal' ); ?></span></button>
						</div>
						<div class="col-12 empty-none"><?php do_action( 'woocommerce_cart_coupon' ); ?></div>
					</div>
				</div>
			<?php } ?>
			<div class="pxl-btns-continue-update col-12 col-md-auto">
				<div class="row g-10 justify-content-between justify-content-md-end">
					<div class="col-12 col-sm-auto">
						<a class="btn pxl-continue-shop" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
							<span><?php esc_html_e( 'Continue Shopping', 'powerlegal' ); ?></span>
						</a>
					</div>
					<div class="col-12 col-sm-auto">
						<button type="submit" class="btn pxl-update-cart" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'powerlegal' ); ?>"><span><?php esc_html_e( 'Update cart', 'powerlegal' ); ?></span></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

// Continue Shopping Button
if(!function_exists('powerlegal_woocommerce_return_to_shop')){
	//add_action('woocommerce_cart_actions', 'powerlegal_woocommerce_return_to_shop', 2);
	function powerlegal_woocommerce_return_to_shop(){ ?>
		<div class="text-end pt-10">
			<a class="btn pxl-continue-shop" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Continue Shopping', 'powerlegal' ); ?>
			</a>
		</div>
	<?php
	}
}

if ( ! function_exists( 'woocommerce_button_proceed_to_checkout' ) ) {
	/**
	 * Output the proceed to checkout button.
	 */
	function woocommerce_button_proceed_to_checkout() {
		?>
		<div class="text-end pt-10">
			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn-second pxl-btn">
				<span><?php esc_html_e( 'Proceed to checkout', 'powerlegal' ); ?></span>
			</a>
		</div>
		<?php
	}
}

// Cross Sell
// Move Cross Sell to Last
remove_action('woocommerce_cart_collaterals','woocommerce_cross_sell_display');
if(powerlegal()->get_theme_opt('cart_cross_sell', '1') === '1'){
	add_action('woocommerce_after_cart','woocommerce_cross_sell_display', 0);
}
//filter:  woocommerce_cross_sells_columns
add_filter( 'woocommerce_cross_sells_columns', 'powerlegal_woocommerce_cross_sells_columns');
function powerlegal_woocommerce_cross_sells_columns( $columns ) {
	$columns = powerlegal()->get_theme_opt('cart_cross_sell_column', '4');
	return $columns;
}
// filter: woocommerce_cross_sells_total
add_filter( 'woocommerce_cross_sells_total', 'powerlegal_woocommerce_cross_sells_total');
function powerlegal_woocommerce_cross_sells_total( $totals ) {
	$totals = powerlegal()->get_theme_opt('cart_cross_sell_total', '4');;
	return $totals;
}

add_action( 'woocommerce_cart_is_empty', 'powerlegal_custom_cart_is_empty' );
function powerlegal_custom_cart_is_empty(){
	?>
	<div class="pxl-cart-empty-wrap text-center">
		<img class="img-bag" src="<?php echo esc_url(get_template_directory_uri().'/assets/images/bag-large.png')?>">
		<h2 class="pxl-heading"><?php echo esc_html__('Your cart is currently empty.','powerlegal') ?></h2>
		<p class="desc"><?php echo esc_html__( 'You may check out all the available products and buy some in the shop.', 'powerlegal' ) ?></p>
	</div>
	<?php 
}

/* mini cart */
if ( ! function_exists( 'powerlegal_widget_shopping_cart_button_view_cart' ) ) {
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
	add_action( 'woocommerce_widget_shopping_cart_buttons', 'powerlegal_widget_shopping_cart_button_view_cart', 10 );
	function powerlegal_widget_shopping_cart_button_view_cart(){
		echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="pxl-btn button wc-forward"><span>' . esc_html__( 'View cart', 'powerlegal' ) . '</span></a>';
	}
}
if ( ! function_exists( 'powerlegal_widget_shopping_cart_proceed_to_checkout' ) ) {
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
	add_action( 'woocommerce_widget_shopping_cart_buttons', 'powerlegal_widget_shopping_cart_proceed_to_checkout', 20 );
	function powerlegal_widget_shopping_cart_proceed_to_checkout(){
		echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="pxl-btn button checkout wc-forward"><span>' . esc_html__( 'Checkout', 'powerlegal' ) . '</span></a>';
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'powerlegal_woocommerce_sidebar_cart_count_number_header' );
function powerlegal_woocommerce_sidebar_cart_count_number_header( $fragments ) {
    ob_start();
    ?>
    <span class="pxl-cart-count"><?php echo sprintf (_n( '%d', '%d', WC()->cart->cart_contents_count, 'powerlegal' ), WC()->cart->cart_contents_count ); ?></span>
    <?php

    $fragments['span.pxl-cart-count'] = ob_get_clean();

    return $fragments;
}