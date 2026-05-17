<?php
$widget->add_render_attribute('cart', 'class', 'pxl-cart side-panel d-flex align-items-center');
$target = '.pxl-side-cart';
?>
<div class="pxl-cart-wrap d-flex align-items-center align-content-center">
    <div <?php pxl_print_html($widget->get_render_attribute_string( 'cart' )); ?> data-target="<?php echo esc_attr($target)?>">
        <?php
        if(!is_admin())
            $count = WC()->cart->get_cart_contents_count();
        if( $widget->get_setting('icon_type','none') == 'lib'){
            echo '<div class="pxl-cart-icon d-inline-flex">';
                if(!is_admin()): ?>
                    <span class="pxl-cart-count cart_total"><?php echo esc_attr($count) ?></span>
                <?php endif;
            \Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true', 'class' => '' ], 'span' );
            echo '</div>';
        }
        ?>
    </div>
</div>
 