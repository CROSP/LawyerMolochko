<?php

use Elementor\Utils;

$default_settings = [
    'title' => '',
    'title_tag' => 'h3',
    'sub_title' => '',
    'sub_title_style' => '',
    'text_align' => '',
    'pxl_icon' => '',
    'text_list' => '',
];
$settings = array_merge($default_settings, $settings);
$hightlight_list = $widget->get_settings('text_list');
$list_array = [];
if(count($hightlight_list) > 0){
    foreach ($hightlight_list as $key => $list) {
        $list_array[] = $list['highlight_text'];
    }
}
$widget->add_render_attribute( 'wrap-heading', 'class', 'pxl-heading-wrap d-flex layout1');
 
$widget->add_render_attribute( 'large-title', 'class', 'heading-title');
if ( $settings['title_animation'] ) {
    $widget->add_render_attribute( 'large-title', 'class', 'pxl-animate pxl-invisible animated-'.$settings['title_animation_duration']);
    $widget->add_render_attribute( 'large-title', 'data-settings',
        json_encode([
            'animation'      => $settings['title_animation'],
            'animation_delay' => $settings['title_animation_delay']
        ])
    );
}
$widget->add_render_attribute( 'sub-title', 'class', 'heading-subtitle');
if ( $settings['sub_title_animation'] ) {
    $widget->add_render_attribute( 'sub-title', 'class', 'pxl-animate pxl-invisible animated-'.$settings['sub_title_animation_duration']);
    $widget->add_render_attribute( 'sub-title', 'data-settings',
        json_encode([
            'animation'      => $settings['sub_title_animation'],
            'animation_delay' => $settings['sub_title_animation_delay']
        ])
    );
}
$inner_tag = 'span';
if ( ! empty( $settings['link']['url'] ) ) {
    $widget->add_render_attribute( 'link', 'href', $settings['link']['url'] );
    $inner_tag = 'a';
    if ( $settings['link']['is_external'] ) {
        $widget->add_render_attribute( 'link', 'target', '_blank' );
    }

    if ( $settings['link']['nofollow'] ) {
        $widget->add_render_attribute( 'link', 'rel', 'nofollow' );
    }

    if ( ! empty( $settings['link']['custom_attributes'] ) ) {
        // Custom URL attributes should come as a string of comma-delimited key|value pairs
        $custom_attributes = Utils::parse_custom_attributes( $settings['link']['custom_attributes'] );
        $widget->add_render_attribute( 'link', $custom_attributes);
    }
}

$link_attributes = $widget->get_render_attribute_string( 'link' );

$text_align = $widget->get_settings( 'text_align', '' );
$sub_title_atbot = $widget->get_settings( 'sub_title_atbot' );
$sub_title_line = $widget->get_settings( 'sub_title_line' );
$sub_line_on = '';
if ($sub_title_line){
    $sub_line_on = 'line-on';
    $widget->add_render_attribute( 'sub-title', 'class', $sub_line_on);
}
extract($settings);

?>
<div <?php pxl_print_html($widget->get_render_attribute_string( 'wrap-heading' )); ?>>
    <div class="pxl-heading-inner <?php echo esc_attr($text_align) ?>">
        <?php if(!empty($sub_title) && empty($sub_title_atbot)) : ?>
            <div <?php pxl_print_html($widget->get_render_attribute_string( 'sub-title' )); ?>>
                <span><?php echo pxl_print_html(nl2br($sub_title)); ?></span>
            </div>
        <?php endif; ?>

        <<?php echo esc_attr($title_tag); ?> <?php pxl_print_html($widget->get_render_attribute_string( 'large-title' )); ?>>
            <<?php echo implode( ' ', [ $inner_tag, $link_attributes ] ); ?>>
                <?php pxl_print_html(nl2br($title)); ?>
            </<?php pxl_print_html($inner_tag); ?>>
            <?php
            if(!empty($list_array)){
                ?>
                <span class="heading-highlight typewrite" data-period="3500" data-type="<?php echo esc_attr(json_encode($list_array)); ?>">
                    <span class="wrap"></span>
                </span>
                <?php
            }
            ?>
        </<?php echo esc_attr($title_tag); ?>>

        <?php if($sub_title_atbot && !empty($sub_title)): ?>
            <div <?php pxl_print_html($widget->get_render_attribute_string( 'sub-title' )); ?>>
                <span><?php pxl_print_html(nl2br($sub_title)); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

