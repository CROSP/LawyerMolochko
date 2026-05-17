<?php
$default_settings = [
    'clients' => [],
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$arrows = $widget->get_setting('arrows','false');  
$dots = $widget->get_setting('dots','false');  

$opts = [
    'slide_direction'               => 'horizontal',
    'slide_percolumn'               => '1', 
    'slide_mode'                    => 'slide', 
    'slides_to_show_xxl'            => $widget->get_setting('col_xxl', '3'),
    'slides_to_show'                => $widget->get_setting('col_xl', '3'),
    'slides_to_show_lg'             => $widget->get_setting('col_lg', '2'),
    'slides_to_show_md'             => $widget->get_setting('col_md', '2'),
    'slides_to_show_sm'             => $widget->get_setting('col_sm', '1'),
    'slides_to_show_xs'             => $widget->get_setting('col_xs', '1'), 
    'slides_to_scroll'              => $widget->get_setting('slides_to_scroll', '1'), 
    'slides_gutter'                 => 0,
    'arrow'                         => $arrows,
    'dots'                          => $dots,
    'dots_style'                    => 'bullets',
    'autoplay'                      => $widget->get_setting('autoplay', 'false'),
    'pause_on_hover'                => $widget->get_setting('pause_on_hover', 'true'),
    'pause_on_interaction'          => 'true',
    'delay'                         => $widget->get_setting('autoplay_speed', '5000'),
    'loop'                          => $widget->get_setting('infinite','false'),
    'speed'                         => $widget->get_setting('speed', '500'),
    'centered_slides'               => true,
    'centered_slides_bounds'        => true,
];
  

$widget->add_render_attribute( 'carousel', [
    'class'         => 'pxl-swiper-container',
    'dir'           => is_rtl() ? 'rtl' : 'ltr',
    'data-settings' => wp_json_encode($opts)
]);
?>

<?php if(isset($clients) && !empty($clients) && count($clients)): ?>
    <div class="pxl-swiper-slider pxl-video-slider layout-<?php echo esc_attr($settings['layout'])?>">
        <div class="pxl-swiper-slider-wrap pxl-carousel-inner overflow-hidden">
            <div <?php pxl_print_html($widget->get_render_attribute_string( 'carousel' )); ?>>
                <div class="pxl-swiper-wrapper swiper-wrapper">
                    <?php foreach ($clients as $key => $value):
                        $name             = isset($value['name']) ? $value['name'] : '';
                        $position             = isset($value['position']) ? $value['position'] : '';
                        $client_img       = isset($value['client_img']) ? $value['client_img'] : [];
                        $video_link       = isset($value['video_link']) ? $value['video_link'] : [];
                        $video_text             = isset($value['video_text']) ? $value['video_text'] : '';
                        $thumbnail1 = '';
                        if(!empty($client_img['id'])) {
                            $img = pxl_get_image_by_size( array(
                                'attach_id'  => $client_img['id'],
                                'thumb_size' => 'full',
                                'class' => 'no-lazyload',
                            ));
                            $thumbnail1 = $img['thumbnail'];
                        }
                        ?>
                        <div class="pxl-swiper-slide swiper-slide">
                            <div class="item-inner relative">
                                <div class="item-info">
                                    <?php
                                    if (!empty($name)){
                                        ?><h4 class="item-name"><?php echo esc_html($name); ?></h4><?php
                                    }
                                    if (!empty($position)){
                                        ?><div class="item-position"><?php echo esc_html($position); ?></div><?php
                                    }
                                    ?>
                                </div>
                                <div class="item-image-wrap">
                                    <?php if(!empty($thumbnail1)) : ?>
                                        <div class="item-image">
                                            <?php echo wp_kses_post($thumbnail1);  ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pxl-video-popup">
                                        <div class="video-content-inner">
                                            <a class="video-play-button" href="<?php echo esc_url($video_link['url']);?>">
                                                <i class="zmdi zmdi-play"></i>
                                            </a>
                                            <?php
                                            if (!empty($video_text)){
                                                ?><div class="button-text color-white"><?php echo esc_html($video_text); ?></div><?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if($arrows !== 'false'): ?>
            <div class="pxl-swiper-arrow pxl-swiper-arrow-next"><span class="pxl-icon pxli-arrow-next"></span></div>
            <div class="pxl-swiper-arrow pxl-swiper-arrow-prev"><span class="pxl-icon pxli-arrow-prev"></span></div>
        <?php endif; ?>
        <?php if($dots !== 'false'): ?>
            <div class="pxl-swiper-dots"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
  