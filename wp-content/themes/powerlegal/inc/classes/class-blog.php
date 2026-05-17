<?php
if (!class_exists('Powerlegal_Blog')) {
    class Powerlegal_Blog
    {
        public function get_post_feature(){
            if ( !has_post_thumbnail()) return;
            $post_feature_image_type = powerlegal()->get_theme_opt('post_feature_image_type','cropped');
 
            if($post_feature_image_type == 'full'){ 
                $thumbnail_size = 'full'; 
            }else{
                $thumbnail_size = 'size-post-single';
            }
            echo '<div class="post-image post-featured '.$post_feature_image_type.'">';  
                the_post_thumbnail($thumbnail_size);
            echo '</div>';
        }

        public function get_archive_meta($post_id = 0) {
            $archive_category = powerlegal()->get_theme_opt( 'archive_category', true );
            $post_comments_on = powerlegal()->get_theme_opt('post_comments_on', true);
            $archive_author = powerlegal()->get_theme_opt( 'archive_author', true );
            if($archive_author || $archive_category || $post_comments_on) : ?>
                <div class="post-metas">
                    <div class="meta-inner d-flex align-items-center hover-underline">
                        <?php if($archive_author) : ?>
                            <span class="post-author col-auto d-flex"><span><?php echo esc_html__('By', 'powerlegal'); ?> <?php the_author_posts_link(); ?></span></span>
                        <?php endif; ?>
                        <?php if($archive_category && has_category('', $post_id)) : ?>
                            <span class="post-category col-auto d-flex"><span><?php the_terms( $post_id, 'category', '', ', ', '' ); ?></span></span>
                        <?php endif; ?>
                        <?php if($post_comments_on) : ?>
                            <span class="post-comments col-auto d-flex">
                                <a href="<?php echo get_comments_link($post_id); ?>">
                                    <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1 Comment', 'powerlegal'), esc_html__(' % Comments', 'powerlegal'), $post_id); ?></span>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; 
        }

        public function get_excerpt( $length = 55 ){
            $pxl_the_excerpt = get_the_excerpt();
            if(!empty($pxl_the_excerpt)) {
                echo esc_html($pxl_the_excerpt);
            } else {
                echo wp_kses_post($this->get_excerpt_more( $length ));
            }
        }

        public function get_excerpt_more( $length = 55, $post = null ) {
            $post = get_post( $post );

            if ( empty( $post ) || 0 >= $length ) {
                return '';
            }

            if ( post_password_required( $post ) ) {
                return esc_html__( 'Post password required.', 'powerlegal' );
            }

            $content = apply_filters( 'the_content', strip_shortcodes( $post->post_content ) );
            $content = str_replace( ']]>', ']]&gt;', $content );

            $excerpt_more = apply_filters( 'powerlegal_excerpt_more', '&hellip;' );
            $excerpt      = wp_trim_words( $content, $length, $excerpt_more );

            return $excerpt;
        }

        public function get_post_metas(){
            $post_author_on = powerlegal()->get_theme_opt('post_author_on', true);
            $post_date_on = powerlegal()->get_theme_opt('post_date_on', true);
            $post_comments_on = powerlegal()->get_theme_opt('post_comments_on', true);
            $post_categories_on = powerlegal()->get_theme_opt('post_categories_on', true);
            if ($post_author_on || $post_date_on || $post_categories_on || $post_comments_on) : ?>
                <div class="post-metas">
                    <div class="meta-inner d-flex align-items-center">
                        <?php if($post_author_on) : ?>
                            <span class="post-author d-flex align-items-center">
                                <span class="author-avatar">
                                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 'thumbnail' ); ?>
                                </span>
                                <span><?php echo esc_html__('By', 'powerlegal'); ?>&nbsp;<?php the_author_posts_link(); ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if($post_date_on) : ?>
                            <span class="post-date d-flex align-items-center">
                                <i class="zmdi zmdi-calendar-note"></i>
                                <span><?php echo get_the_date(); ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if($post_comments_on) : ?>
                            <span class="post-comments d-flex align-items-center">
                                <i class="zmdi zmdi-comment-text"></i>
                                <a href="<?php comments_link(); ?>">
                                    <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1 Comment', 'powerlegal'), esc_html__(' %', 'powerlegal')); ?></span>
                                </a>
                            </span>
                        <?php endif; ?>
                        <?php if($post_categories_on && has_category()) : ?>
                            <span class="post-category d-flex align-items-center">
                                <i class="zmdi zmdi-folder"></i>
                                <span><?php the_terms(get_the_ID(), 'category', '', ', '); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif;
        }

        public function powerlegal_set_post_views( $postID ) {
            $countKey = 'post_views_count';
            $count    = get_post_meta( $postID, $countKey, true );
            if ( $count == '' ) {
                $count = 0;
                delete_post_meta( $postID, $countKey );
                add_post_meta( $postID, $countKey, '0' );
            } else {
                $count ++;
                update_post_meta( $postID, $countKey, $count );
            }
        }

        public function get_post_tags(){
            $post_tag = powerlegal()->get_theme_opt( 'post_tag', true );
            if($post_tag != '1') return;
            $tags_list = get_the_tag_list( '<span class="label d-none">'.esc_attr__('Tags:', 'powerlegal'). '</span>', ' ' );
            if ( $tags_list ){
                echo '<div class="post-tags d-flex">';
                printf('%2$s', '', $tags_list);
                echo '</div>';
            }
        }

        public function get_post_share($post_id = 0) {
            $post_social_share = powerlegal()->get_theme_opt( 'post_social_share', false );
            $share_icons = powerlegal()->get_theme_opt( 'post_social_share_icon', [] );
            if($post_social_share != '1') return;
            $post = get_post($post_id);
            ?>
            <div class="post-shares d-flex align-items-center">
                <span class="label"><?php echo esc_html__('Share:', 'powerlegal'); ?></span>
                <div class="social-share">
                    <div class="d-flex">
                        <?php if(in_array('facebook', $share_icons)): ?>
                        <div class="social-item">
                            <a class="pxl-icon icon-facebook pxli-facebook-f" title="<?php echo esc_attr__('Facebook', 'powerlegal'); ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_the_permalink($post_id)); ?>"></a>
                        </div>
                        <?php endif; ?>
                        <?php if(in_array('twitter', $share_icons)): ?>
                        <div class="social-item">
                            <a class="pxl-icon icon-twitter pxli-twitter" title="<?php echo esc_attr__('Twitter', 'powerlegal'); ?>" target="_blank" href="https://twitter.com/intent/tweet?original_referer=<?php echo urldecode(home_url('/')); ?>&url=<?php echo urlencode(get_the_permalink($post_id)); ?>&text=<?php the_title();?>%20"></a>
                        </div>
                        <?php endif; ?>
                        <?php if(in_array('linkedin', $share_icons)): ?>
                        <div class="social-item">
                            <a class="pxl-icon icon-linkedin pxli-linkedin-in" title="<?php echo esc_attr__('Linkedin', 'powerlegal'); ?>" target="_blank" href="https://www.linkedin.com/cws/share?url=<?php echo urlencode(get_the_permalink($post_id));?>"></a>
                        </div>
                        <?php endif; ?>
                        <?php if(in_array('pinterest', $share_icons)): ?>
                            <div class="social-item">
                                <a class="pxl-icon icon-pinterest pxli-pinterest-p" title="<?php echo esc_attr__('Pinterest', 'powerlegal'); ?>" target="_blank" href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_the_post_thumbnail_url($post_id, 'full')); ?>&media=&description=<?php echo urlencode(the_title_attribute(array('echo' => false, 'post' => $post))); ?>"></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        public function get_post_nav() {
            $post_navigation = powerlegal()->get_theme_opt( 'post_navigation', false );
            if($post_navigation != '1') return;
            global $post;

            $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
            $next     = get_adjacent_post( false, '', false );

            if ( ! $next && ! $previous )
                return;
            ?>
            <?php
            $next_post = get_next_post();
            $previous_post = get_previous_post();
            if(empty($previous_post) && empty($next_post)) return;

            ?>
            <div class="single-next-prev-nav row gx-0 justify-content-between align-items-center">
                <?php if(!empty($previous_post)): 
                    $prev_img_id = get_post_thumbnail_id($previous_post->ID);
                    $prev_img_url = wp_get_attachment_image_src($prev_img_id, 'thumbnail');

                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $prev_img_id,
                        'thumb_size' => '60x52',
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                    ?>
                    <div class="nav-next-prev prev col relative text-start">
                        <div class="nav-inner">
                            <?php previous_post_link('%link',''); ?>
                            <div class="nav-label-wrap d-flex align-items-center">
                                <span class="nav-icon pxli-angle-double-left"></span>
                                <span class="nav-label"><?php echo esc_html__('Previous Post', 'powerlegal'); ?></span>
                            </div>
                            <div class="nav-title-wrap d-flex align-items-center d-none d-sm-flex">
                                <div class="col-auto nav-img"><?php echo wp_kses_post( $thumbnail ) ?></div>
                                <div class="col"><div class="nav-title"><?php echo get_the_title($previous_post->ID); ?></div></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if(!empty($next_post)) : 
                    $next_img_id = get_post_thumbnail_id($next_post->ID);
                    $next_img_url = wp_get_attachment_image_src($next_img_id, 'thumbnail');

                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $next_img_id,
                        'thumb_size' => '60x52',
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                    ?>
                    <div class="nav-next-prev next col relative text-end">
                        <div class="nav-inner">
                            <?php next_post_link('%link',''); ?>
                            <div class="nav-label-wrap d-flex align-items-center justify-content-end">
                                <span class="nav-label"><?php echo esc_html__('Newer Post', 'powerlegal'); ?></span>
                                <span class="nav-icon pxli-angle-double-right"></span>
                            </div>
                            <div class="nav-title-wrap d-flex align-items-center d-none d-sm-flex">
                                <div class="col"><div class="nav-title"><?php echo get_the_title($next_post->ID); ?></div></div>
                                <div class="col-auto nav-img"><?php echo wp_kses_post( $thumbnail ) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div> 
            <?php  
        }

        public function get_related_post(){
            $post_related_on = powerlegal()->get_theme_opt( 'post_related_on', false );

            if($post_related_on) {
                global $post;
                $current_id = $post->ID;
                $posttags = get_the_category($post->ID);
                if (empty($posttags)) return;

                $tags = array();

                foreach ($posttags as $tag) {

                    $tags[] = $tag->term_id;
                }
                $post_number = '6';
                $query_similar = new WP_Query(array('posts_per_page' => $post_number, 'post_type' => 'post', 'post_status' => 'publish', 'category__in' => $tags));
                if (count($query_similar->posts) > 1) {
                    wp_enqueue_script( 'swiper' );
                    wp_enqueue_script( 'powerlegal-swiper' );
                    $opts = [
                        'slide_direction'               => 'horizontal',
                        'slide_percolumn'               => '1', 
                        'slide_mode'                    => 'slide', 
                        'slides_to_show'                => 3, 
                        'slides_to_show_lg'             => 3, 
                        'slides_to_show_md'             => 2, 
                        'slides_to_show_sm'             => 2, 
                        'slides_to_show_xs'             => 1, 
                        'slides_to_scroll'              => 1, 
                        'slides_gutter'                 => 30, 
                        'arrow'                         => false,
                        'dots'                          => true,
                        'dots_style'                    => 'bullets'
                    ];
                    $data_settings = wp_json_encode($opts);
                    $dir           = is_rtl() ? 'rtl' : 'ltr';
                    ?>
                    <div class="pxl-related-post">
                        <h3 class="widget-title"><?php echo esc_html__('Related Posts', 'powerlegal'); ?></h3>
                        <div class="class" data-settings="<?php echo esc_attr($data_settings) ?>" data-rtl="<?php echo esc_attr($dir) ?>">
                            <div class="pxl-related-post-inner pxl-swiper-wrapper swiper-wrapper">
                            <?php foreach ($query_similar->posts as $post):
                                $thumbnail_url = '';
                                if (has_post_thumbnail(get_the_ID()) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)) :
                                    $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'powerlegal-blog-small', false);
                                endif;
                                if ($post->ID !== $current_id) : ?>
                                    <div class="pxl-swiper-slide swiper-slide grid-item">
                                        <div class="grid-item-inner">
                                            <?php if (has_post_thumbnail()) { ?>
                                                <div class="item-featured">
                                                    <a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url($thumbnail_url[0]); ?>" /></a>
                                                </div>
                                            <?php } ?>
                                            <h3 class="item-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h3>
                                        </div>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php }
            }

            wp_reset_postdata();
        }
    }
 
}