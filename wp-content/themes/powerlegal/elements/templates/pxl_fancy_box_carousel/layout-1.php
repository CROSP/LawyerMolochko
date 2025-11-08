<?php
$default_settings = [
    'boxs' => [],
];
   
$settings = array_merge($default_settings, $settings);
extract($settings);

$arrows = $widget->get_setting('arrows','false');  
$dots = $widget->get_setting('dots','false');  

$opts = [
    'slide_direction'               => 'horizontal',
    'slide_percolumn'               => '1', 
    'slide_mode'                    => 'slide', 
    'slides_to_show_xxl'            => $widget->get_setting('col_xxl', '1'),
    'slides_to_show'                => $widget->get_setting('col_xl', '1'),
    'slides_to_show_lg'             => $widget->get_setting('col_lg', '1'),
    'slides_to_show_md'             => $widget->get_setting('col_md', '1'),
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
    'speed'                         => $widget->get_setting('speed', '500')
];
  

$widget->add_render_attribute( 'carousel', [
    'class'         => 'pxl-swiper-container',
    'dir'           => is_rtl() ? 'rtl' : 'ltr',
    'data-settings' => wp_json_encode($opts)
]);
?>
<?php if(isset($boxs) && !empty($boxs) && count($boxs)): ?>
    <div class="pxl-swiper-slider pxl-fancy-box-carousel layout-<?php echo esc_attr($settings['layout'])?>">
        <div class="pxl-swiper-slider-wrap pxl-carousel-inner overflow-hidden">
            <div <?php pxl_print_html($widget->get_render_attribute_string( 'carousel' )); ?>>
                <div class="pxl-swiper-wrapper swiper-wrapper">
                <?php foreach ($boxs as  $box): ?>
                    <div class="pxl-swiper-slide swiper-slide">
                        <div class="item-inner">
                            <?php
                            if (! empty($box['selected_icon'] )){
                                ?>
                                <div class="item-icon elementor-animation-wobble-vertical">
                                    <?php
                                    \Elementor\Icons_Manager::render_icon( $box['selected_icon'], [ 'aria-hidden' => 'true' ] );
                                    ?>
                                </div>
                                <?php
                            }

                            if (!empty($box['title_text'])){
                                ?>
                                <h3 class="item-title">
                                    <?php echo pxl_print_html($box['title_text']); ?>
                                </h3>
                                <?php
                            }
                            if (!empty($box['description_text'])){
                                ?>
                                <div class="item-description">
                                    <?php echo esc_html($box['description_text']); ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php if($arrows !== 'false'): ?>
        <div class="pxl-swiper-arrows nav-in-vertical">
            <div class="pxl-swiper-arrow pxl-swiper-arrow-next"><span class="pxl-icon pxli-arrow-next"></span></div>
            <div class="pxl-swiper-arrow pxl-swiper-arrow-prev"><span class="pxl-icon pxli-arrow-prev"></span></div>
        </div>
        <?php endif; ?>
        <?php if($dots !== 'false'): ?>
        <div class="pxl-swiper-dots"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
