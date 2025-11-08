<?php
/**
 * @package Powerlegal
 */
get_header();

$pxl_sidebar = powerlegal()->get_sidebar_args(['type' => 'page', 'content_col'=> '8']); // type: blog, post, page, shop, product

if(class_exists('\Elementor\Plugin')){
    $id = get_the_ID();
    if ( is_singular() && \Elementor\Plugin::$instance->documents->get( $id )->is_built_with_elementor()) {
        $classes = 'elementor-container pxl-page-content';
    } else {
        $classes = 'container';
    }
} else {
    $classes = 'container';
}

if ($pxl_sidebar['sidebar_class']) $classes = 'container';

?>
<div class="<?php echo esc_attr($classes);?> pxl-content-container">
    <div class="row <?php echo esc_attr($pxl_sidebar['wrap_class']) ?>">
        <div id="pxl-content-area" class="<?php echo esc_attr($pxl_sidebar['content_class']) ?>">
            <main id="pxl-content-main" class="pxl-content-main">
                <?php while ( have_posts() ) {
                    the_post();
                    get_template_part( 'template-parts/content/content', 'page' );
                    if ( comments_open() || get_comments_number() ) {
                        comments_template();
                    }
                } ?>
            </main>
        </div>
        <?php if ($pxl_sidebar['sidebar_class']) : ?>
            <div id="pxl-sidebar-area" class="<?php echo esc_attr($pxl_sidebar['sidebar_class']) ?>">
                <div class="sidebar-sticky">
                    <?php dynamic_sidebar( 'sidebar-page' ); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php get_footer();