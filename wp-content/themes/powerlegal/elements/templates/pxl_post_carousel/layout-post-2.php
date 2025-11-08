<?php
extract($settings);

$tax = ['category'];
$select_post_by = $widget->get_setting('select_post_by', 'term_selected');
$source = $post_ids = [];

if($select_post_by === 'post_selected'){
    $post_ids = $widget->get_setting('source_'.$settings['post_type'].'_post_ids', '');
}else{
    $source  = $widget->get_setting('source_'.$settings['post_type'], '');
}

$orderby = $widget->get_setting('orderby', 'date');
$order = $widget->get_setting('order', 'desc');
$limit = $widget->get_setting('limit', -1);

$settings['layout']    = $settings['layout_'.$settings['post_type']];

extract(pxl_get_posts_of_grid(
    'post',
    ['source' => $source, 'orderby' => $orderby, 'order' => $order, 'limit' => $limit, 'post_ids' => $post_ids],
    $tax
));

$arrows = $widget->get_setting('arrows','false');
$dots = $widget->get_setting('dots','false');

$opts = [
    'slide_direction'               => 'horizontal',
    'slide_percolumn'               => '1',
    'slide_mode'                    => 'slide',
    'slides_to_show_xxl'            => $widget->get_setting('col_xxl', '4'),
    'slides_to_show'                => $widget->get_setting('col_xl', '4'),
    'slides_to_show_lg'             => $widget->get_setting('col_lg', '3'),
    'slides_to_show_md'             => $widget->get_setting('col_md', '3'),
    'slides_to_show_sm'             => $widget->get_setting('col_sm', '2'),
    'slides_to_show_xs'             => $widget->get_setting('col_xs', '1'),
    'slides_to_scroll'              => $widget->get_setting('slides_to_scroll', '1'),
    'slides_gutter'                 => (int)$gutter,
    'center_slide'                  => $widget->get_setting('center_slide', 'false'),
    'arrow'                         => $arrows,
    'dots'                          => $dots,
    'dots_style'                    => 'bullets',
    'autoplay'                      => $widget->get_setting('autoplay', 'false'),
    'pause_on_hover'                => $widget->get_setting('pause_on_hover', 'true'),
    'pause_on_interaction'          => 'true',
    'delay'                         => $widget->get_setting('autoplay_speed', '5000'),
    'loop'                          => $widget->get_setting('infinite','false'),
    'speed'                         => $widget->get_setting('speed', '500')
];


$img_size = !empty( $img_size ) ? $img_size : '992x546';

$data_settings = $item_anm_cls = '';
if ( !empty( $item_animation) ) {

    $item_anm_cls= ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
    $item_animation_delay = !empty($item_animation_delay) ? $item_animation_delay : '150';
    $data_animations = [
        'animation' => $item_animation,
        'animation_delay' => (float)$item_animation_delay
    ];
}

$widget->add_render_attribute( 'carousel', [
    'class'         => 'pxl-swiper-container',
    'dir'           => is_rtl() ? 'rtl' : 'ltr',
    'data-settings' => wp_json_encode($opts)
]);

$button_text = !empty($button_text) ? $button_text : esc_html__('Read more', 'powerlegal');
$arrows_on_hover_cls = $arrows_on_hover == 'true' ? 'arrow-on-hover' : '';

?>
<?php if(!empty($posts) && count($posts)): ?>

    <div class="pxl-swiper-slider pxl-post-carousel layout-<?php echo esc_attr($settings['layout']);?> center-mode-<?php echo esc_attr($opts['center_slide']);?>">
        <?php if ($select_post_by === 'term_selected' && $filter == "true"): ?>
            <div class="swiper-filter-wrap">
                <?php if(!empty($filter_default_title)): ?>
                    <span class="filter-item active" data-filter-target="all"><?php echo esc_html($filter_default_title); ?></span>
                <?php endif; ?>
                <?php foreach ($categories as $category):
                    $category_arr = explode('|', $category);
                    $term = get_term_by('slug',$category_arr[0], $category_arr[1]);
                    ?>
                    <span class="filter-item" data-filter-target="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="pxl-swiper-slider-wrap pxl-carousel-inner overflow-hidden">
            <div <?php pxl_print_html($widget->get_render_attribute_string( 'carousel' )); ?>>
                <div class="pxl-swiper-wrapper swiper-wrapper">
                    <?php
                    $i = 0;
                    foreach ($posts as $post):
                        $i = $i + 50;
                        $thumbnail = '';
                        if (has_post_thumbnail($post->ID)){
                            $img = pxl_get_image_by_size( array(
                                'post_id'  => $post->ID ,
                                'thumb_size' => $img_size,
                                'class' => 'no-lazyload',
                            ));
                            $thumbnail = $img['thumbnail'];
                        }
                        $filter_class = '';
                        if ($select_post_by === 'term_selected' && $filter == "true")
                            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));

                        $author = get_user_by('id', $post->post_author);

                        $data_animations['animation_delay'] = ((float)$item_animation_delay + $i);
                        $data_animation =  json_encode($data_animations);
                        $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
                        ?>
                        <div class="pxl-swiper-slide swiper-slide" data-filter="<?php echo esc_attr($filter_class) ?>">
                            <div class="item-inner relative">
                                <?php if (isset( $thumbnail )): ?>
                                    <div class="post-featured">
                                        <div class="post-image">
                                            <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                                <?php echo wp_kses_post($thumbnail); ?>
                                            </a>
                                        </div>
                                        <?php if( $show_date == 'true' ) : ?>
                                            <div class="post-date">
                                                <div class="day-month">
                                                    <span class="date-day"><?php echo get_the_date('d', $post->ID)."."; ?></span>
                                                    <span class="date-month"><?php echo get_the_date('m', $post->ID); ?></span>
                                                </div>
                                                <span class="date-year"><?php echo get_the_date('Y', $post->ID); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-content <?php echo esc_attr($item_anm_cls) ?>" <?php pxl_print_html($data_settings); ?>>
                                            <h3 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h3>
                                            <?php
                                            if ($show_author == 'true' || $show_comment == 'true' || $show_category == 'true'){
                                                ?>
                                                <div class="post-metas">
                                                    <div class="meta-inner d-flex align-items-center hover-underline">
                                                        <?php if($show_author == 'true') : ?>
                                                            <span class="post-author d-flex align-items-center">
                                                            <span class="author-avatar">
                                                                <?php echo get_avatar( get_the_author_meta( 'ID', $post->post_author ), 'thumbnail' ); ?>
                                                            </span>
                                                            <span><?php echo esc_html__('By', 'powerlegal'); ?>&nbsp;<?php the_author_posts_link(); ?></span>
                                                        </span>
                                                        <?php endif; ?>
                                                        <?php if($show_comment == 'true') : ?>
                                                            <span class="post-comments d-flex align-items-center">
                                                            <i class="zmdi zmdi-comment-text"></i>
                                                            <a href="<?php echo get_comments_link($post->ID); ?>">
                                                                <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal')); ?></span>
                                                            </a>
                                                        </span>
                                                        <?php endif; ?>
                                                        <?php if($show_category == 'true') : ?>
                                                            <span class="post-category d-flex align-items-center">
                                                            <i class="zmdi zmdi-folder"></i>
                                                            <span><?php the_terms( $post->ID, 'category', '', ' / ', '' ); ?></span>
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if($arrows !== 'false'): ?>
            <div class="pxl-swiper-arrows nav-in-vertical <?php echo esc_attr($arrows_on_hover_cls) ?>">
                <div class="pxl-swiper-arrow pxl-swiper-arrow-prev"><span class="pxl-icon pxli-angle-left"></span></div>
                <div class="pxl-swiper-arrow pxl-swiper-arrow-next"><span class="pxl-icon pxli-angle-right"></span></div>
            </div>
        <?php endif; ?>
        <?php if($dots !== 'false'): ?>
            <div class="pxl-swiper-dots"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>