<?php
/**
 * @package Powerlegal
 */
?>
<?php
    $id = get_the_ID();
    if(class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->documents->get( $id )->is_built_with_elementor()){
        $post_content_classes = 'single-elementor-content';
    } else {
        $post_content_classes = '';
    }
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('pxl-single-posttype pxl-practice-area'); ?>>
    <div class="pxl-entry-content clearfix">
            <div class="content-inner clearfix <?php echo esc_attr($post_content_classes);?>"><?php
                the_content();
            ?></div>
            <?php
                wp_link_pages( array(
                    'before'      => '<div class="page-links">',
                    'after'       => '</div>',
                    'link_before' => '<span>',
                    'link_after'  => '</span>',
                ) );
            ?>
    </div>
</article> 