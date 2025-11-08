<?php
$default_settings = [
    'el_title' => '',
    'download_description' => '',
    'download' => '',
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$is_new = \Elementor\Icons_Manager::is_migration_allowed();
?>
<?php if(isset($download) && !empty($download) && count($download)): ?>
    <div class="pxl-download e-sidebar-widget">
        <?php if(!empty($el_title)) : ?>
            <h3 class="widget-title"><?php echo esc_attr($el_title); ?></h3>
        <?php endif; ?>
        <?php if(!empty($download_description)) : ?>
            <p class="widget-desc"><?php echo esc_attr($download_description); ?></p>
        <?php endif; ?>
        <?php foreach ($download as $key => $cms_download):
            $link_key = $widget->get_repeater_setting_key( 'file_name', 'download', $key );
            if ( ! empty( $cms_download['link']['url'] ) ) {
                $widget->add_render_attribute( $link_key, 'href', $cms_download['link']['url'] );

                if ( $cms_download['link']['is_external'] ) {
                    $widget->add_render_attribute( $link_key, 'target', '_blank' );
                }

                if ( $cms_download['link']['nofollow'] ) {
                    $widget->add_render_attribute( $link_key, 'rel', 'nofollow' );
                }
            }
            $link_attributes = $widget->get_render_attribute_string( $link_key );
            $has_icon = !empty( $cms_download['file_type_icon'] );
            ?>
            <a class="item-download d-flex" <?php pxl_print_html($link_attributes); ?>>
                <div class="file-name">
                    <?php
                    if ($has_icon){
                        if ($is_new){
                            \Elementor\Icons_Manager::render_icon( $cms_download['file_type_icon'], [ 'aria-hidden' => 'true' ] );
                        }else{
                            ?><i class="<?php echo esc_attr($cms_download['file_type_icon']);?>" aria-hidden="true"></i><?php
                        }
                    }
                    ?>
                    <span class="download-title"><?php echo esc_html($cms_download['file_name']); ?></span>
                </div>
                <?php if(!empty($cms_download['file_size'])) : ?>
                    <span class="file-size">
                        <?php echo esc_attr($cms_download['file_size']); ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>