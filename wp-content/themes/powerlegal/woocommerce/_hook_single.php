<?php
/**
 * Build Single Product Gallery and Summary layout
 * Add and Remove function hook in woocommerce/templates/content-single-product.php
 * woocommerce_before_single_product_summary, woocommerce_single_product_summary & woocommerce_after_single_product_summary
*/

/** ------------------------------------------------
 * Gallery and Summary layout
---------------------------------------------------*/
/* Remove function hook */
function powerlegal_woocommerce_remove_product_single_function() {
    remove_action( 'woocommerce_single_product_summary' , 'woocommerce_template_single_title', 5 );
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
    remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart', 30);
    remove_action('woocommerce_single_product_summary','woocommerce_template_single_meta', 40);
    remove_action( 'woocommerce_single_product_summary' , 'woocommerce_template_single_rating', 10 );
    remove_action( 'woocommerce_single_product_summary' , 'woocommerce_template_single_price', 10 );
    add_action( 'woocommerce_single_product_summary' , 'woocommerce_template_single_price', 10 );
    add_action( 'woocommerce_single_product_summary' , 'woocommerce_template_single_rating', 10 );

}
add_action( 'init', 'powerlegal_woocommerce_remove_product_single_function' );

/* Custom Gallery Layout, Wrap open Gallery and Summary */
if(!function_exists('powerlegal_woocommerce_before_single_product_summary')){
	add_action('woocommerce_before_single_product_summary','powerlegal_woocommerce_before_single_product_summary', 0);
	function powerlegal_woocommerce_before_single_product_summary(){
		$gallery_layout = isset($_GET['gallery_layout']) ? sanitize_text_field($_GET['gallery_layout']) : powerlegal()->get_page_opt('gallery_layout', 'simple');
		$thumb_position_cls = ($gallery_layout == 'vertical') ? 'right' : 'bottom';
		$col_cls = ($gallery_layout == 'vertical') ? 'col-lg-6' : 'col-md-6';
		$sidebar_pos = powerlegal()->get_theme_opt('product_sidebar_pos', '0');
		$sidebar_pos = isset($_GET['sidebar_pos']) ? sanitize_text_field($_GET['sidebar_pos']) : $sidebar_pos;
		if( $gallery_layout == 'vertical' && $sidebar_pos != '0')
			$col_cls = 'col-xl-6';
		$classes = 'pxl-wc-img-summary pxl-single-product-gallery-summary-wraps row '.$gallery_layout;
		?>
        <div class="<?php echo esc_attr($classes);?>">
            <div class="pxl-single-product-gallery-wraps <?php echo esc_attr($col_cls);?> thumbnail-<?php echo esc_attr($thumb_position_cls);?>">
                <div class="pxl-single-product-gallery-wraps-inner relative">
                    <?php
                    do_action('powerlegal_before_single_product_gallery');
                    do_action('powerlegal_single_product_gallery');
                    do_action('powerlegal_after_single_product_gallery');
                    ?>
                </div>
            </div>
            <div class="pxl-single-product-summary-wrap <?php echo esc_attr($col_cls);?>">
        <?php
	}
}

/* Close summary columns and close gallery-summary wrap */
if(!function_exists('powerlegal_woocommerce_after_single_product_summary')){
	add_action('woocommerce_after_single_product_summary', 'powerlegal_woocommerce_after_single_product_summary', 0);
	function powerlegal_woocommerce_after_single_product_summary(){
			echo '</div>';
		echo '</div>';
	}
}

/* Get back gallery to layout custom */
add_action('powerlegal_single_product_gallery', 'woocommerce_show_product_images', 1);

/* Add custom CSS class to Gallery */
add_filter('woocommerce_single_product_image_gallery_classes','powerlegal_woocommerce_single_product_image_gallery_classes');
function powerlegal_woocommerce_single_product_image_gallery_classes($class){
	$gallery_layout = isset($_GET['gallery_layout']) ? sanitize_text_field($_GET['gallery_layout']) :  powerlegal()->get_page_opt('gallery_layout', 'simple');
	$thumb_position_cls = ($gallery_layout == 'vertical') ? 'right' : 'bottom';
	$class[] = 'pxl-product-gallery-'.$gallery_layout;
	$class[] = 'pxl-product-gallery-'.$thumb_position_cls;
	unset($class[3]);
	return $class;
}

/* Custom thumbnail carousel in bottom Gallery */
if(!function_exists('powerlegal_wc_single_product_gallery_layout')){
	add_filter('woocommerce_single_product_carousel_options', 'powerlegal_wc_single_product_gallery_layout' );
    function powerlegal_wc_single_product_gallery_layout($options){
        $gallery_layout = isset($_GET['gallery_layout']) ? sanitize_text_field($_GET['gallery_layout']) : powerlegal()->get_page_opt('gallery_layout', 'simple');
        $options['prevText']     = '<span class="flex-prev-icon"></span>';
		$options['nextText']     = '<span class="flex-next-icon"></span>';
        if ( $gallery_layout == 'vertical' || $gallery_layout == 'horizontal' ){
            $options['directionNav'] = true;
            $options['controlNav']   = false;
            $options['sync'] = '.wc-gallery-sync';
        }
	    return $options;
    }
}

/* Add thumbnail product gallery */
if(!function_exists('powerlegal_product_gallery_thumbnail_sync')){
	add_action('powerlegal_single_product_gallery', 'powerlegal_product_gallery_thumbnail_sync', 2);
	function powerlegal_product_gallery_thumbnail_sync($args=[]){
		global $product;
		$gallery_layout = isset($_GET['gallery_layout']) ? sanitize_text_field($_GET['gallery_layout']) : powerlegal()->get_page_opt('gallery_layout', 'simple');
		$thumb_position_cls = ($gallery_layout == 'vertical') ? 'right' : 'bottom';

        $args = wp_parse_args($args, [
            'gallery_layout' => $gallery_layout
        ]);
        $post_thumbnail_id = $product->get_image_id();
    	$attachment_ids = array_merge( (array)$post_thumbnail_id , $product->get_gallery_image_ids() );

        if('simple' === $args['gallery_layout'] || '' ===  $args['gallery_layout'] || 'default' === $args['gallery_layout'] || empty($attachment_ids[0])) return;
        $flex_class = '';

        $thumb_v_w = 118;
        $thumb_v_h = 118;

        $thumb_h_w = 130;
        $thumb_h_h = 130;

        switch ($args['gallery_layout']) {
	        case 'vertical':
				$thumbnail_size = $thumb_v_w.'x'.$thumb_v_h;
				$thumb_w        = $thumb_v_w;
				$thumb_h        = $thumb_v_h;
				$flex_class     = 'flex-vertical';
				$thumb_margin   = 15;
	            break;

	        case 'horizontal':
	            $thumbnail_size = $thumb_h_w.'x'.$thumb_h_h;
	            $thumb_w = $thumb_h_w;
	            $thumb_h = $thumb_h_h;
	            $flex_class = 'flex-horizontal';
	            $thumb_margin   = 15;
	            break;

	    }
	    $gallery_css_class = ['wc-gallery-sync', 'thumbnail-'.$gallery_layout, 'thumbnail-pos-'.$thumb_position_cls];

    ?>
    	<div class="<?php echo trim(implode(' ', $gallery_css_class));?>" data-thumb-w="<?php echo esc_attr($thumb_w);?>" data-thumb-h="<?php echo esc_attr($thumb_h);?>" data-thumb-margin="<?php echo esc_attr($thumb_margin); ?>">
			<div class="wc-gallery-sync-slides flexslider <?php echo esc_attr($flex_class);?>">
	            <?php foreach ( $attachment_ids as $attachment_id ) {
	            	$img = pxl_get_image_by_size( array(
                        'attach_id'  => $attachment_id,
                        'thumb_size' => $thumbnail_size,
                        'class' => 'img-gal',
                    ));
                    $thumbnail = $img['thumbnail'];
	            	?>
	                <div class="wc-gallery-sync-slide flex-control-thumb"><?php echo wp_kses_post($thumbnail);?></div>
	            <?php } ?>
	        </div>
	    </div>
    <?php
	}
}

/** ------------------------------------------------
 * Single Product Custom Summary
---------------------------------------------------*/

/* Custom Meta */
add_action('woocommerce_single_product_summary','powerlegal_wc_template_single_meta',21);
if(!function_exists('powerlegal_wc_template_single_meta')){
	add_action('woocommerce_template_single_excerpt','powerlegal_wc_template_single_meta',99);
	function powerlegal_wc_template_single_meta(){
		global $product;
		echo '<div class="product_meta">';

		do_action( 'woocommerce_product_meta_start' );

		if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) {
			$sku = $product->get_sku() ? $product->get_sku() : esc_html__( 'N/A', 'powerlegal' );
			echo '<span class="sku_wrapper"><span class="lbl">'.esc_html__( 'SKU:', 'powerlegal' ).'</span> <span class="sku">'.$sku.'</span></span>';
		}

		echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in"><span class="lbl">' . _n( 'Category: ', 'Categories:', count( $product->get_category_ids() ), 'powerlegal' ) . '</span>', '</span>' );

		echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as"><span class="lbl">' . _n( 'Tag: ', 'Tags: ', count( $product->get_tag_ids() ), 'powerlegal' ) . '</span>', '</span>' );

		do_action( 'woocommerce_product_meta_end' );

		echo '</div>';

	}
}

/* Add to cart */
if(!function_exists('powerlegal_single_add_to_cart')){
	add_action('woocommerce_single_product_summary','powerlegal_single_add_to_cart', 30);
	function powerlegal_single_add_to_cart(){
		global $product;
		switch ($product->get_type()) {
			case 'variable':
				powerlegal_variable_add_to_cart();
				break;
			case 'external':
				powerlegal_external_add_to_cart(); 
				break;
			case 'grouped':
				powerlegal_grouped_add_to_cart(); 
				break;
			default:
				powerlegal_simple_add_to_cart(); 
				break;
		}
	}
}

function powerlegal_variable_add_to_cart(){
	global $product;
 	
 	$product_variation_style = isset($_GET['variation-style']) ? sanitize_text_field($_GET['variation-style']) : powerlegal()->get_theme_opt('product_variation_style','dropdown');
	wp_enqueue_script( 'wc-add-to-cart-variation' );
 	
	$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
 
	$available_variations = $get_variations ? $product->get_available_variations() : false;
	$attributes = $product->get_variation_attributes();
	$attribute_keys  = array_keys( $attributes );
	$variations_json = wp_json_encode( $available_variations );
	$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
	do_action( 'woocommerce_before_add_to_cart_form' );
	?>
		<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo esc_attr($variations_attr); ?>">
			<?php do_action( 'woocommerce_before_variations_form' ); ?>

			<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
				<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', esc_html__( 'This product is currently out of stock and unavailable.', 'powerlegal' ) ) ); ?></p>
			<?php else : ?>
				<div class="pxl-variation-quantity-wrap style-<?php echo esc_attr($product_variation_style) ?>">
					<div class="variations">
						<?php foreach ( $attributes as $attribute_name => $options ) : ?>
							<div class="pxl-variation-row row">
								<div class="label col-12"><span for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><span class="lbl"><?php echo wc_attribute_label( $attribute_name );  ?>: </span><span><?php echo esc_html__( 'Choose An Option', 'powerlegal' ) ?></span></span></div>
								<div class="value col-12">
									<?php
										wc_dropdown_variation_attribute_options(
											array(
												'options'   => $options,
												'attribute' => $attribute_name,
												'product'   => $product,
											)
										);
										echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'powerlegal' ) . '</a>' ) ) : '';
									?>
								</div>
							</div>
						<?php endforeach; ?>
						
					</div>

					<div class="single_variation_wrap">
						 
						<div class="woocommerce-variation-add-to-cart variations_button">
							<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
							<?php
							do_action( 'woocommerce_before_add_to_cart_quantity' );
							woocommerce_quantity_input(
								array(
									'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
									'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
									'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), 
								)
							);

							do_action( 'woocommerce_after_add_to_cart_quantity' );
							?>

							<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
							<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
							<input type="hidden" name="variation_id" class="variation_id" value="0" />
						</div>
							 
					</div>
				</div>
				<div class="pxl-variation-results">
					<?php 
						do_action( 'woocommerce_before_single_variation' );
						echo '<div class="woocommerce-variation single_variation"></div>';//do_action( 'woocommerce_single_variation' );
						do_action( 'woocommerce_after_single_variation' );
					?>
				</div>
				<div class="pxl-addtocart-btn-wrap">
					<div class="pxl-atc-btn">
						<button type="submit" class="pxl-btn single_add_to_cart_button button alt"><span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span></button>
					</div>

					<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'woocommerce_after_variations_form' ); ?>
		</form>
	<?php 
	do_action( 'woocommerce_after_add_to_cart_form' );
}

function powerlegal_external_add_to_cart(){
	global $product;

	if ( ! $product->add_to_cart_url() ) {
		return;
	}
 
	$product_url = $product->add_to_cart_url();
	$button_text = $product->single_add_to_cart_text();
	do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart external" action="<?php echo esc_url( $product_url ); ?>" method="get">
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
		<div class="pxl-addtocart-btn-wrap">
			<div class="pxl-atc-btn">
				<button type="submit" class="pxl-btn single_add_to_cart_button button alt"><span><?php echo esc_html( $button_text ); ?></span></button>
			</div>
		</div>
		 
		<?php wc_query_string_form_fields( $product_url ); ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
	<?php 
}

function powerlegal_grouped_add_to_cart(){
	global $product, $post;
	$products = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
	if ( $products ) {
		$grouped_product = $product;
		$grouped_products = $products;
		do_action( 'woocommerce_before_add_to_cart_form' ); ?>
		<form class="cart grouped_form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
			<div class="woocommerce-grouped-product-list group_table">
					<?php
					$quantites_required      = false;
					$previous_post           = $post;
					$grouped_product_columns = apply_filters(
						'woocommerce_grouped_product_columns',
						array(
							'quantity',
							'label',
							'price',
						),
						$product
					);
					$show_add_to_cart_button = false;

					do_action( 'woocommerce_grouped_product_list_before', $grouped_product_columns, $quantites_required, $product );

					foreach ( $grouped_products as $grouped_product_child ) {
						$post_object        = get_post( $grouped_product_child->get_id() );
						$quantites_required = $quantites_required || ( $grouped_product_child->is_purchasable() && ! $grouped_product_child->has_options() );
						$post               = $post_object;  
						setup_postdata( $post );

						if ( $grouped_product_child->is_in_stock() ) {
							$show_add_to_cart_button = true;
						}

						echo '<div id="product-' . esc_attr( $grouped_product_child->get_id() ) . '" class="woocommerce-grouped-product-list-item d-flex gx-15 ' . esc_attr( implode( ' ', wc_get_product_class( '', $grouped_product_child ) ) ) . '">';
 
						foreach ( $grouped_product_columns as $column_id ) {
							do_action( 'woocommerce_grouped_product_list_before_' . $column_id, $grouped_product_child );

							switch ( $column_id ) {
								case 'quantity':
									ob_start();

									if ( ! $grouped_product_child->is_purchasable() || $grouped_product_child->has_options() || ! $grouped_product_child->is_in_stock() ) {
										woocommerce_template_loop_add_to_cart();
									} elseif ( $grouped_product_child->is_sold_individually() ) {
										echo '<input type="checkbox" name="' . esc_attr( 'quantity[' . $grouped_product_child->get_id() . ']' ) . '" value="1" class="wc-grouped-product-add-to-cart-checkbox" />';
									} else {
										do_action( 'woocommerce_before_add_to_cart_quantity' );

										woocommerce_quantity_input(
											array(
												'input_name'  => 'quantity[' . $grouped_product_child->get_id() . ']',
												'input_value' => isset( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ? wc_stock_amount( wc_clean( sanitize_text_field( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ) ) : '',  
												'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 0, $grouped_product_child ),
												'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $grouped_product_child->get_max_purchase_quantity(), $grouped_product_child ),
												'placeholder' => '0',
											)
										);

										do_action( 'woocommerce_after_add_to_cart_quantity' );
									}

									$value = ob_get_clean();
									break;
								case 'label':
									$value  = '<label for="product-' . esc_attr( $grouped_product_child->get_id() ) . '">';
									$value .= $grouped_product_child->is_visible() ? '<a href="' . esc_url( apply_filters( 'woocommerce_grouped_product_list_link', $grouped_product_child->get_permalink(), $grouped_product_child->get_id() ) ) . '">' . $grouped_product_child->get_name() . '</a>' : $grouped_product_child->get_name();
									$value .= '</label>';
									break;
								case 'price':
									$value = $grouped_product_child->get_price_html() . wc_get_stock_html( $grouped_product_child );
									break;
								default:
									$value = '';
									break;
							}

							echo '<div class="woocommerce-grouped-product-list-item__' . esc_attr( $column_id ) . '">' . apply_filters( 'woocommerce_grouped_product_list_column_' . $column_id, $value, $grouped_product_child ) . '</div>';  
							do_action( 'woocommerce_grouped_product_list_after_' . $column_id, $grouped_product_child );
						}

						echo '</div>';
					}
					$post = $previous_post;  
					setup_postdata( $post );

					do_action( 'woocommerce_grouped_product_list_after', $grouped_product_columns, $quantites_required, $product );
					?>
			</div>

			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

			<?php if ( $quantites_required && $show_add_to_cart_button ) : ?>

				<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
				<div class="pxl-addtocart-btn-wrap">
					<div class="pxl-atc-btn">
						<button type="submit" class="pxl-btn single_add_to_cart_button button alt"><span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span></button>
					</div>
				</div>
				<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

			<?php endif; ?>
		</form>
		<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
		<?php 
	}
}

function powerlegal_simple_add_to_cart(){
	global $product;
        if ( ! $product->is_purchasable() ) {
            return;
        }
        echo wc_get_stock_html( $product ); // WPCS: XSS ok.
        if ( $product->is_in_stock() ) : ?>

            <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
            <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
                <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
                <?php
                do_action( 'woocommerce_before_add_to_cart_quantity' );
                woocommerce_quantity_input(
                    array(
                        'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                        'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                        'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                    )
                );
                do_action( 'woocommerce_after_add_to_cart_quantity' );
                ?>
                <div class="pxl-addtocart-btn-wrap">
                    <div class="pxl-atc-btn">
                        <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="pxl-btn single_add_to_cart_button button alt"><span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span></button>
                    </div>
                </div>
            </form>
            <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
        <?php endif;
}

/* Quantity Label */
add_filter('woocommerce_before_quantity_input_field', function(){
    echo '<div class="lbl">'.esc_html__( 'Quantity','powerlegal' ).'</div>';
});

/* Stock & Wishlist */
if(!function_exists('powerlegal_single_add_stock_wishlist')){
	add_action('woocommerce_single_product_summary', 'powerlegal_single_add_stock_wishlist', 48);
	function powerlegal_single_add_stock_wishlist(){
		global $product;
		$product_wishlist = powerlegal()->get_theme_opt('product_wishlist','0');
		?>
		<div class="stock-wishlist">
            <?php
            if($product->is_in_stock()){
                ?>
                 <div class="stock in-stock">
                    <span class="fa fa-check-circle"></span>
                    <?php echo esc_html__('In Stock','powerlegal');?>
                 </div>
                <?php
            } else {
                ?>
                 <div class="stock out-of-stock">
                    <span class="fa fa-window-close"></span>
                    <?php echo esc_html__('Out of Stock','powerlegal');?>
                 </div>
                <?php
            }
            if( $product_wishlist == '1'){
                ?>
                <div class="pxl-shop-woosmart-wrap">
                    <?php do_action( 'woosw_button_position_single_woosmart' ); ?>
                </div>
                <?php
            }
            ?>
        </div>
		<?php
	}
}

/* Social Sharing */
if(!function_exists('powerlegal_woocommerce_template_single_sharing')){
	add_action('woocommerce_share', 'powerlegal_woocommerce_template_single_sharing');
	function powerlegal_woocommerce_template_single_sharing(){
		$product_social_share_on = powerlegal()->get_theme_opt('product_social_share_on', '0');
		if($product_social_share_on != '1') return;
		powerlegal()->blog->get_post_share();
	}
}

/* Custom Details Tab */
if(!function_exists('powerlegal_woocommerce_rename_tabs')){
	add_filter( 'woocommerce_product_tabs', 'powerlegal_woocommerce_rename_tabs', 98 );
	function powerlegal_woocommerce_rename_tabs( $tabs ) {
		if(!empty($tabs['additional_information']['title']))
			$tabs['additional_information']['title'] = esc_html__( 'Product details','powerlegal' );	// Rename the additional information tab

		return $tabs;
	}
}

/* Change added to cart message */
if(!function_exists('powerlegal_wc_add_to_cart_message_html')){
	add_filter('wc_add_to_cart_message_html', 'powerlegal_wc_add_to_cart_message_html', 10, 3);
	function powerlegal_wc_add_to_cart_message_html($message, $products, $show_qty){
		$titles = array();
		$count  = 0;

		if ( ! is_array( $products ) ) {
			$products = array( $products => 1 );
			$show_qty = false;
		}

		if ( ! $show_qty ) {
			$products = array_fill_keys( array_keys( $products ), 1 );
		}

		foreach ( $products as $product_id => $qty ) {
			/* translators: %s: product name */
			$titles[] = apply_filters( 'woocommerce_add_to_cart_qty_html', ( $qty > 1 ? absint( $qty ) . ' &times; ' : '' ), $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'powerlegal' ), strip_tags( get_the_title( $product_id ) ) ), $product_id );
			$count   += $qty;
		}

		$titles = array_filter( $titles );
		/* translators: %s: product name */
		$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'powerlegal' ), wc_format_list_of_items( $titles ) );

		// Output success messages.
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			$return_to = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink( 'shop' ) );
			$message   = sprintf( '<span class="pxl-added-to-cart-msg">%s</span> <a href="%s" tabindex="1" class="btn btn-secondary">%s</a>', esc_html( $added_text ), esc_url( $return_to ), esc_html__( 'Continue shopping', 'powerlegal' ) );
		} else {
			$message = sprintf( '<span class="pxl-added-to-cart-msg">%s</span> <a href="%s" tabindex="1" class="btn btn-secondary">%s</a>',esc_html( $added_text ), esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'powerlegal' ) );
		}
		return $message;
	}
}

/* Relate Products */
if(powerlegal()->get_theme_opt('product_related', '1') === '0' ){
	remove_action('woocommerce_after_single_product_summary','woocommerce_output_related_products', 20);
}
add_filter( 'woocommerce_output_related_products_args', 'powerlegal_woocommerce_output_related_products_args',20 );
function powerlegal_woocommerce_output_related_products_args( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns'] = 3;
	return $args;
}




