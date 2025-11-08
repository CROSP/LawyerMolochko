<?php
/**
 * Template part for displaying posts in loop
 *
 * @package Powerlegal
 */

if(has_post_thumbnail()){
    $content_inner_cls = 'single-post-inner has-post-thumbnail';
    $meta_class    = ''; 
} else {
    $content_inner_cls = 'single-post-inner  no-post-thumbnail';
    $meta_class = '';
}
 
if(class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->documents->get( $id )->is_built_with_elementor()){
    $post_content_classes = 'single-elementor-content';
} else {
    $post_content_classes = '';
}

?>
<article id="post-<?php the_ID(); ?>" <?php post_class('pxl-single-post'); ?>>
    <div class="<?php echo esc_attr($content_inner_cls);?>">
        <?php
        if (has_post_thumbnail()) {
            powerlegal()->blog->get_post_feature();
        }
        ?>
        <?php powerlegal()->blog->get_post_metas(); ?>
        <div class="post-content overflow-hidden">
            <div class="content-inner clearfix <?php echo esc_attr($post_content_classes);?>"><?php
                the_content();
            ?></div>
            <div class="<?php echo trim(implode(' ', ['navigation page-links clearfix empty-none'])); ?>"><?php 
                wp_link_pages(); 
            ?></div>
        </div>
        <?php
        $post_tag = powerlegal()->get_theme_opt( 'post_tag', true );
        $post_social_share = powerlegal()->get_theme_opt( 'post_social_share', false );
        if ($post_tag == '1' || $post_social_share == '1'){
            ?>
            <div class="post-tags-share d-flex">
                <?php
                if ($post_tag == '1'){
                    ?><div class="post-tags-wrap col-sm-6"><?php powerlegal()->blog->get_post_tags(); ?></div><?php
                }
                if ($post_social_share == '1'){
                    ?><div class="post-share-wrap col-sm-6"><?php powerlegal()->blog->get_post_share(); ?></div><?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
  
    <?php powerlegal()->blog->get_post_nav(); ?>
</article>