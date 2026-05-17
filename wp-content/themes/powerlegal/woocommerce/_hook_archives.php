<?php
/**
 * Shop Columns.
 */
add_filter( 'loop_shop_columns', 'powerlegal_loop_shop_columns', 20 );
function powerlegal_loop_shop_columns() {
    $columns = isset($_GET['col']) ? sanitize_text_field($_GET['col']) : powerlegal()->get_theme_opt('products_columns', 3);
    return $columns;
}

/* Number of products per page (shop page) */
add_filter( 'loop_shop_per_page', 'powerlegal_loop_shop_per_page', 20 );
function powerlegal_loop_shop_per_page( $limit ) {
    $limit = powerlegal()->get_theme_opt('product_per_page', 12);
    return $limit;
}

/* Modify image width theme support. */
add_filter('woocommerce_get_image_size_gallery_thumbnail', function ($size) {
    $size['width'] = 768;
    $size['height'] = 920;
    $size['crop'] = 1;
    return $size;
});

add_filter('single_product_archive_thumbnail_size', 'powerlegal_woocommerce_product_size');
function powerlegal_woocommerce_product_size($size){
    $size = 'full';
    return $size;
}

/* Remove page title on archive page */
add_filter('woocommerce_show_page_title', function(){ return false;});

/* Replace text On-sale */
add_filter('woocommerce_sale_flash', 'powerlegal_custom_sale_text', 10, 3);
function powerlegal_custom_sale_text($text, $post, $_product)
{
    $regular_price = get_post_meta( get_the_ID(), '_regular_price', true);
    $sale_price = get_post_meta( get_the_ID(), '_sale_price', true);

    $product_sale = '';
    if(!empty($sale_price)) {
        $product_sale = intval( ( (intval($regular_price) - intval($sale_price)) / intval($regular_price) ) * 100);
        return '<span class="onsale">'.esc_html__('Sale', 'powerlegal').' '.$product_sale.'%</span>';
    }
}

/* Add and Remove function hook in woocommerce/templates/content-product.php */
function powerlegal_woocommerce_remove_loop_function() {
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
    remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
    remove_action('woocommerce_before_shop_loop_item_title','woocommerce_show_product_loop_sale_flash', 10);
    remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
}
add_action( 'init', 'powerlegal_woocommerce_remove_loop_function' );

/* Custom Top table: catalog order and result count */
if(!function_exists('powerlegal_woocommerce_catalog_result')){
    // remove
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    // add back
    add_action('woocommerce_before_shop_loop','powerlegal_woocommerce_catalog_result', 20);
    add_action('powerlegal_woocommerce_catalog_ordering', 'woocommerce_catalog_ordering');
    add_action('powerlegal_woocommerce_result_count', 'woocommerce_result_count');
    function powerlegal_woocommerce_catalog_result(){
        $columns = isset($_GET['col']) ? sanitize_text_field($_GET['col']) : powerlegal()->get_theme_opt('products_columns', '2');
        $display_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : powerlegal()->get_theme_opt('shop_display_type', 'grid');
        $active_grid = 'active';
        $active_list = '';
        if( $display_type == 'list' ){
            $active_list = $display_type == 'list' ? 'active' : '';
            $active_grid = '';
        }
        ?>
        <div class="pxl-shop-topbar-wrap row justify-content-between align-items-center g-30">
            <div class="pxl-view-layout-wrap col-12 col-sm-auto order-md-3">
                <ul class="pxl-view-layout d-flex align-items-center">
                    <li class="lbl"><?php echo esc_html__( 'View','powerlegal' ) ?></li>
                    <li class="view-icon view-grid <?php echo esc_attr($active_grid) ?>"><a href="javascript:void(0);" class="pxl-ttip tt-top-left" data-cls="products columns-<?php echo esc_attr($columns);?>" data-col="grid"><span class="tt-txt"><?php echo esc_html__('View Grid','powerlegal') ?></span><span class="pxli-grid"></span></a></li>
                    <li class="view-icon view-list <?php echo esc_attr($active_list) ?>"><a href="javascript:void(0);" class="pxl-ttip tt-top-left" data-cls="products shop-view-list" data-col="list"><span class="tt-txt"><?php echo esc_html__('View List','powerlegal') ?></span><span class="pxli-list"></span></a></li>
                </ul>
            </div>
            <div class="col-12 col-sm-auto order-md-2">
                <?php do_action('powerlegal_woocommerce_catalog_ordering'); ?>
            </div>
            <div class="col text-heading number-result">
                <?php do_action('powerlegal_woocommerce_result_count'); ?>
            </div>
        </div>
        <?php
    }
}

/* Loop Start */
add_filter( 'woocommerce_product_loop_start', 'powerlegal_product_loop_start' );
function powerlegal_product_loop_start(){
	$display_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : powerlegal()->get_theme_opt('shop_display_type', 'grid');
	if( $display_type == 'list')
		return '<ul class="products shop-view-list">';
	else
		return '<ul class="products columns-'. esc_attr( wc_get_loop_prop( 'columns' ) ) .'">';
}


/* Show Product in Loop */
if(!function_exists('powerlegal_woocommerce_product_loop_item')){
    add_filter( 'woocommerce_after_shop_loop_item', 'powerlegal_woocommerce_product_loop_item' );
    function powerlegal_woocommerce_product_loop_item() {
        global $product;
        ?>
        <div class="pxl-shop-item-wrap">
            <div class="pxl-products-thumb relative">
                <div class="image-wrap scale-hover-x">
                    <?php woocommerce_template_loop_product_thumbnail(); ?>
                </div>
                <?php
                if ( $product->is_featured() ) {
                    $feature_text = get_post_meta($product->get_id(),'product_feature_text', true);
                    if (empty($feature_text)){
                        $feature_text = "NEW";
                    }
                    ?>
                    <span class="pxl-featured"><?php echo esc_html($feature_text); ?></span>
                    <?php
                }
                woocommerce_show_product_loop_sale_flash();
                ?>
                <div class="pxl-add-to-cart">
                    <?php woocommerce_template_loop_add_to_cart(); ?>
                </div>
                <?php
                ?>
            </div>
            <div class="pxl-products-content">
                <div class="pxl-products-content-wrap">
                    <div class="pxl-products-content-inner">
                        <div class="top-content-inner d-md-flex gx-30 justify-content-between">
                            <?php
                            woocommerce_template_loop_price();
                            if( class_exists( 'WPCleverWoosc' ) || class_exists( 'WPCleverWoosq' ) || class_exists( 'WPCleverWoosw' )){
                                echo '<div class="pxl-shop-woosmart-wrap">';
                                do_action( 'woosw_button_position_archive_woosmart' );
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <h3 class="pxl-product-title">
                            <a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a>
                        </h3>
                        <?php woocommerce_template_loop_rating(); ?>
                    </div>
                </div>
            </div>
            <div class="pxl-products-content-list-view d-none">
                <?php woocommerce_template_loop_price(); ?>
                <h3 class="pxl-product-title">
                    <a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a>
                </h3>
                <div class="list-view-rating">
                    <?php woocommerce_template_loop_rating(); ?>
                    <?php
                    if( class_exists( 'WPCleverWoosc' ) || class_exists( 'WPCleverWoosq' ) || class_exists( 'WPCleverWoosw' )){
                        echo '<div class="pxl-shop-woosmart-wrap">';
                        do_action( 'woosw_button_position_archive_woosmart' );
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="pxl-loop-product-excerpt">
                    <?php the_excerpt(); ?>
                </div>
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>
    <?php }
}

/* Cart Button */
add_filter('woocommerce_loop_add_to_cart_link', 'powerlegal_woocommerce_loop_add_to_cart_link', 10, 3);
function powerlegal_woocommerce_loop_add_to_cart_link($button, $product, $args){
    return sprintf(
        '<a href="%s" data-quantity="%s" class="pxl-btn %s" %s><span class="pxl-btn-text">%s</span>%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
        esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
        isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
        esc_html( $product->add_to_cart_text() ),
        '<span class="pxl-icon pxli-shopping-cart"></span>'
    );
}

/* Paginate links */
add_filter('woocommerce_pagination_args', 'powerlegal_woocommerce_pagination_args');
function powerlegal_woocommerce_pagination_args($default){
    $default = array_merge($default, [
        'prev_text' => '<span class="pxli-angle-left"></span>',
        'next_text' => '<span class="pxli-angle-right"></span>',
        'type'      => 'plain',
    ]);
    return $default;
}

