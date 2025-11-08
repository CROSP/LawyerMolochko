<?php
/**
 * @package Powerlegal
 */

$archive_readmore = powerlegal()->get_theme_opt('archive_readmore', '0');
$archive_readmore_text = powerlegal()->get_theme_opt('archive_readmore_text', esc_html__('Read more', 'powerlegal'));
$featured_video = get_post_meta( get_the_ID(), 'featured-video-url', true );
$audio_url = get_post_meta( get_the_ID(), 'featured-audio-url', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('pxl-archive-post'); ?>>
    
    <?php if (has_post_thumbnail()) {
        $archive_date = powerlegal()->get_theme_opt( 'archive_date', true );
        ?>
        <div class="post-featured">
            <?php
            if (has_post_format('quote')){
                $quote_text = get_post_meta( get_the_ID(), 'featured-quote-text', true );
                $quote_cite = get_post_meta( get_the_ID(), 'featured-quote-cite', true );
                ?>
                <div class="format-wrap">
                    <div class="quote-inner">
                        <div class="quote-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2000 2000"><title>PXL quote</title><path d="M897.25,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.51,55.66-2.91,2-5.78,4.11-8.63,6.23-13.95,8.37-41.27,17.3-54.91-28.87C313.35,839.08,357.21,393.26,817,228.43V123.36c0,1.21-46.27,13-49,13.85a1091.25,1091.25,0,0,0-126.19,49.36C512.33,246.38,393,329.9,297,435.73,155.35,591.9,68.71,796.84,59.14,1007.7,51.82,1168.82,94,1333.87,210,1451c65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C846.78,1441.86,897.25,1335.88,897.25,1223.93Z"/><path class="cls-1" d="M1903.18,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.5,55.66-2.92,2-5.79,4.11-8.64,6.23-14,8.37-41.27,17.3-54.91-28.87-18.8-63.62,25.06-509.44,484.85-674.27V123.36c0,1.21-46.26,13-49,13.85a1090.39,1090.39,0,0,0-126.18,49.36c-129.45,59.81-248.8,143.33-344.79,249.16-141.64,156.17-228.28,361.11-237.85,572-7.32,161.12,34.82,326.17,150.9,443.25,65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C1852.71,1441.86,1903.18,1335.88,1903.18,1223.93Z"/></svg>
                        </div>
                        <a class="quote-text" href="<?php echo esc_url( get_permalink()); ?>"><?php echo esc_html($quote_text);?></a>
                        <p class="quote-cite">
                            <?php echo esc_html($quote_cite);?>
                        </p>
                        <?php powerlegal()->blog->get_archive_meta($post->ID); ?>
                    </div>
                </div>
                <?php
            }elseif (has_post_format('link')){
                $link_url = get_post_meta( get_the_ID(), 'featured-link-url', true );
                $link_text = get_post_meta( get_the_ID(), 'featured-link-text', true );
                ?>
                <div class="format-wrap">
                    <div class="link-inner">
                        <div class="link-icon">
                            <a href="<?php echo esc_url( $link_url); ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 162.65 162.66"><path d="M151.76,10.89a37.29,37.29,0,0,0-52.67,0h0l-23,23L86.72,44.48l23-23h0A22.25,22.25,0,0,1,141.16,53L106.67,87.45a22.26,22.26,0,0,1-31.46,0l-10.6,10.6a37.24,37.24,0,0,0,52.67,0l34.48-34.48A37.3,37.3,0,0,0,151.76,10.89Z" transform="translate(0 0)"/><path d="M53,141.16h0A22.25,22.25,0,0,1,21.5,109.69L56,75.21a22.28,22.28,0,0,1,31.46,0L98.05,64.6a37.3,37.3,0,0,0-52.68,0L10.89,99.09a37.25,37.25,0,0,0,52.68,52.68h0l23-23L75.94,118.17Z" transform="translate(0 0)"/></svg></a>
                        </div>
                        <a class="link-text" target="_blank" href="<?php echo esc_url( $link_url); ?>"><?php echo esc_html($link_text);?></a>
                    </div>
                    <?php powerlegal()->blog->get_archive_meta($post->ID); ?>
                </div>
                <?php
            }elseif (has_post_format('video')){
                if (has_post_thumbnail()) {
                    ?>
                    <div class="post-image">
                        <a href="<?php echo esc_url( get_permalink()); ?>"><?php the_post_thumbnail('full'); ?></a>
                    </div>
                    <?php
                    if (!empty($featured_video)){
                        ?>
                        <div class="pxl-video-popup">
                            <div class="content-inner">
                                <a class="video-play-button video-circle" href="<?php echo esc_url($featured_video); ?>">
                                    <i class="zmdi zmdi-play"></i>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                    if($archive_date) : ?>
                        <div class="post-date">
                            <div class="day-month">
                                <span class="date-day"><?php echo get_the_date('d', $post->ID)."."; ?></span>
                                <span class="date-month"><?php echo get_the_date('m', $post->ID); ?></span>
                            </div>
                            <span class="date-year"><?php echo get_the_date('Y', $post->ID); ?></span>
                        </div>
                    <?php endif;
                }
            }elseif ( !empty($audio_url) && has_post_format('audio')) {
                global $wp_embed;
                pxl_print_html($wp_embed->run_shortcode('[embed]' . $audio_url . '[/embed]'));
            }else{
                ?>
                <div class="post-image">
                    <a href="<?php echo esc_url( get_permalink()); ?>"><?php the_post_thumbnail('full'); ?></a>
                </div>
                <?php if($archive_date) : ?>
                    <div class="post-date">
                        <div class="day-month">
                            <span class="date-day"><?php echo get_the_date('d', $post->ID)."."; ?></span>
                            <span class="date-month"><?php echo get_the_date('m', $post->ID); ?></span>
                        </div>
                        <span class="date-year"><?php echo get_the_date('Y', $post->ID); ?></span>
                    </div>
                <?php endif; ?>
                <?php
            }
            ?>
        </div>
    <?php } ?>
    <?php
    if (!has_post_format('link') && !has_post_format('quote')){
        ?>
        <div class="post-content">
            <?php powerlegal()->blog->get_archive_meta(); ?>
            <h2 class="post-title">
                <a href="<?php echo esc_url( get_permalink()); ?>" title="<?php the_title_attribute(); ?>">
                    <?php if(is_sticky()) { ?>
                        <i class="pxli-thumbtack"></i>
                    <?php } ?>
                    <?php the_title(); ?>
                </a>
            </h2>
            <div class="post-excerpt">
                <?php
                powerlegal()->blog->get_excerpt();
                wp_link_pages( array(
                    'before'      => '<div class="page-links">',
                    'after'       => '</div>',
                    'link_before' => '<span>',
                    'link_after'  => '</span>',
                ) );
                ?>
            </div>
            <?php if( $archive_readmore == '1'): ?>
                <div class="post-readmore pxl-button-wrapper">
                    <a class="btn" href="<?php echo esc_url( get_permalink()); ?>">
                        <span><?php pxl_print_html($archive_readmore_text); ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>
</article>