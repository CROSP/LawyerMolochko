<?php
get_header();
if(is_singular('product')){
    $pxl_sidebar = powerlegal()->get_sidebar_args(['type' => 'product', 'content_col'=> '8']); // type: blog, post, page, shop, product
}else{
    $pxl_sidebar = powerlegal()->get_sidebar_args(['type' => 'shop', 'content_col'=> '8']); // type: blog, post, page, shop, product
}
?>
    <div class="container">
        <div class="row <?php echo esc_attr($pxl_sidebar['wrap_class']) ?>">
            <div id="pxl-content-area" class="<?php echo esc_attr($pxl_sidebar['content_class']) ?>">
                <main id="pxl-content-main" class="pxl-content-main">
                    <?php woocommerce_content(); ?>
                </main>
            </div>

            <?php if ($pxl_sidebar['sidebar_class']) : ?>
                <aside id="pxl-sidebar-area" class="<?php echo esc_attr($pxl_sidebar['sidebar_class']) ?>">
                    <div class="sidebar-sticky">
                        <?php get_sidebar(); ?>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </div>
<?php get_footer();