<?php
$default_settings = [
    'single_info_items' => '',
    'el_title' => '',
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$html_id = pxl_get_element_id($settings);
$info_items = $widget->get_settings('single_info_items');
$is_new = \Elementor\Icons_Manager::is_migration_allowed();
if(!empty($info_items)) : ?>
    <div id="<?php echo esc_attr($html_id); ?>" class="pxl-single-info e-sidebar-widget">
        <?php if(!empty($el_title)) : ?>
            <h3 class="widget-title"><?php echo esc_attr($el_title); ?></h3>
        <?php endif; ?>
        <?php foreach ($info_items as $key => $value):
            $info_label = isset($value['info_label']) ? $value['info_label'] : '';
            $info_text = isset($value['info_text']) ? $value['info_text'] : '';
            $has_icon = !empty( $value['info_icon'] );
            ?>
            <div class="info-item d-flex">
                <div class="inner-icon">
                    <?php
                    if ($has_icon){
                        if ($is_new){
                            \Elementor\Icons_Manager::render_icon( $value['info_icon'], [ 'aria-hidden' => 'true' ] );
                        }else{
                            ?><i class="<?php echo esc_attr($value['info_icon']);?>" aria-hidden="true"></i><?php
                        }
                    }
                    ?>
                </div>
                <div class="inner-text">
                    <span class="label">
                        <?php echo esc_html($info_label); ?>
                    </span>
                    <span class="info-text"><?php echo esc_html($info_text); ?></span>
                </div>
            </div>
        <?php
        endforeach;?>
        <div class="info-item d-flex">
            <div class="inner-icon">
                <?php
                if ($has_icon){
                    if ($is_new){
                        \Elementor\Icons_Manager::render_icon( $settings['share_icon'], [ 'aria-hidden' => 'true' ] );
                    }else{
                        ?><i class="<?php echo esc_attr($value['share_icon']);?>" aria-hidden="true"></i><?php
                    }
                }
                ?>
            </div>
            <div class="inner-text">
                <span class="info-text"><?php powerlegal()->blog->get_post_share(); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>
