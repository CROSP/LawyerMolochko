<?php
use Elementor\Utils;
$default_settings = [
    'boxs' => [],
];
   
$settings = array_merge($default_settings, $settings);
extract($settings);
?>
<?php if(isset($boxs) && !empty($boxs) && count($boxs)): ?>
    <div class="pxl-fancy-box-accordion layout-<?php echo esc_attr($settings['layout'])?>">
        <div class="box-items">
            <?php
            foreach ($settings['boxs'] as $index => $item){
                // Link Repeater Key
                $link_key = $widget->get_repeater_setting_key( 'link', 'boxs', $index );
                if ( ! empty( $item['link']['url'] ) ) {
                    $widget->add_render_attribute( $link_key, 'href', $item['link']['url'] );
                    if ( $item['link']['is_external'] ) {
                        $widget->add_render_attribute( $link_key, 'target', '_blank' );
                    }
                    if ( $item['link']['nofollow'] ) {
                        $widget->add_render_attribute( $link_key, 'rel', 'nofollow' );
                    }
                    if ( ! empty( $settings['link']['custom_attributes'] ) ) {
                        // Custom URL attributes should come as a string of comma-delimited key|value pairs
                        $custom_attributes = Utils::parse_custom_attributes( $settings['link']['custom_attributes'] );
                        $widget->add_render_attribute( 'link', $custom_attributes);
                    }
                }
                $link_attributes = $widget->get_render_attribute_string( $link_key );
                // Item Repeater Key
                $item_key = $widget->get_repeater_setting_key( 'title_text', 'boxs', $index );
                $widget->add_render_attribute( $item_key, 'class', [
                    'box-item',
                    'elementor-repeater-item-' . $item['_id'],
                ] );
                $class_attributes = $widget->get_render_attribute_string( $item_key );
                $button_text = 'Read More';
                if (!empty($item['button_text'])){
                    $button_text = $item['button_text'];
                }
                ?>
                <div <?php pxl_print_html($class_attributes); ?>>
                    <div class="box-image">
                        <div class="image-background" style="background-image: url(<?php echo esc_url($item['box_image']['url']); ?>);">
                        </div>
                        <div class="image-text">
                            <?php
                            if (! empty($item['selected_icon'] )){
                                ?>
                                <div class="item-icon">
                                    <?php
                                    \Elementor\Icons_Manager::render_icon( $item['selected_icon'], [ 'aria-hidden' => 'true' ] );
                                    ?>
                                </div>
                                <?php
                            }
                            if (!empty($item['title_text'])){
                                ?>
                                <h3 class="item-title">
                                    <?php pxl_print_html($item['title_text']); ?>
                                </h3>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="title-wrap d-flex align-items-center">
                            <?php
                            if (! empty($item['selected_icon'] )){
                                ?>
                                <div class="item-icon elementor-animation-wobble-vertical">
                                    <?php
                                    \Elementor\Icons_Manager::render_icon( $item['selected_icon'], [ 'aria-hidden' => 'true' ] );
                                    ?>
                                </div>
                                <?php
                            }
                            if (!empty($item['title_text'])){
                                ?>
                                <h3 class="item-title">
                                    <?php pxl_print_html($item['title_text']); ?>
                                </h3>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        if (!empty($item['description_text'])){
                            ?>
                            <div class="content-description font-smooth">
                                <?php echo esc_html($item['description_text']); ?>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="content-button">
                            <a class="btn-more" <?php pxl_print_html($link_attributes); ?>>
                                <?php echo esc_html($button_text); ?>
                                <i class="zmdi zmdi-long-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
<?php endif; ?>
