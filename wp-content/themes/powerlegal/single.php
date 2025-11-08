<?php
/**
 * @package Powerlegal
 */
get_header();
$pxl_sidebar = powerlegal()->get_sidebar_args(['type' => 'post', 'content_col' => '8']); // type: blog, post, page, shop, product
?>
    <div class="container">
        <div class="row <?php echo esc_attr($pxl_sidebar['wrap_class']) ?>">
            <div id="pxl-content-area" class="<?php echo esc_attr($pxl_sidebar['content_class']) ?>">
                <main id="pxl-content-main" class="pxl-content-main">
                    <?php while (have_posts()) {
                        the_post();
                        get_template_part('template-parts/content/content-single', get_post_format());
                        if (comments_open() || get_comments_number()) {
                            comments_template();
                        }
                    } ?>
                </main>
            </div>
            <?php if ($pxl_sidebar['sidebar_class']) : ?>
                <div id="pxl-sidebar-area" class="<?php echo esc_attr($pxl_sidebar['sidebar_class']) ?>">
                    <div class="sidebar-sticky">
                        <?php get_sidebar(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php get_footer();
