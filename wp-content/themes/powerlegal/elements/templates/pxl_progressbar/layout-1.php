<?php
$html_id = pxl_get_element_id($settings);
if(isset($settings['progressbar_list']) && !empty($settings['progressbar_list'])): 
    $animate_cls = '';
    $data_animations = [];
    if ( !empty( $settings['item_animation'] ) ) {
        $animate_cls = 'pxl-animate pxl-invisible animated-'.$settings['item_animation_duration'];
        $item_animation_delay = !empty( $settings['item_animation_delay'] ) ? $settings['item_animation_delay'] : '200';
        $data_animations = ['animation' => $settings['item_animation'], 'animation_delay' => $item_animation_delay];
    }
     
    ?>
    <div id="<?php echo esc_attr($html_id) ?>" class="pxl-progressbar">
    <?php 
    foreach ($settings['progressbar_list'] as $key => $progressbar):
        $progress_bar_key = $widget->get_repeater_setting_key( 'progress_bar', 'progressbar_list', $key );
        $widget->add_render_attribute( $progress_bar_key, [
            'class'         => 'pxl-progress-bar',
            'role'          => 'progressbar',
            'aria-valuemin' => '0',
            'aria-valuemax' => '100',
            'data-valuetransitiongoal' => $progressbar['percent']['size'],
        ] );

        $increase = $key + 1; 
        $data_settings = '';
        if ( !empty( $data_animations ) ) {
            $data_animations['animation_delay'] = ((float)$item_animation_delay * $increase);
            $data_settings = 'data-settings="'.esc_attr(json_encode($data_animations)).'"';
        }
        $item_cls = ['progress-item', 'elementor-repeater-item-'.$progressbar['_id'], $animate_cls ];

        ?>
        <div class="<?php echo implode(' ', $item_cls) ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="progress-title-wrap row gx-10 justify-content-between">
                <?php
                if ( ! empty( $progressbar['title'] ) ) { ?>
                    <div class="progress-title col"><?php echo esc_html($progressbar['title']); ?></div>
                <?php } ?>
                <span class="progress-percentage col-auto"><?php echo esc_html($progressbar['percent']['size']); ?>%</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bound"></div>
                <div <?php pxl_print_html($widget->get_render_attribute_string( $progress_bar_key )); ?>></div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>