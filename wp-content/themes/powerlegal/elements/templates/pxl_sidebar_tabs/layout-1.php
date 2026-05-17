<?php
extract($settings);

if(count($sb_tabs_links) > 0){
    ?>
    <div class="pxl-sidebar-tabs">
        <?php foreach ($sb_tabs_links as $key => $link) :
            $link_key = $widget->get_repeater_setting_key( 'sb_link_text', 'sb_tabs_links', $key );
            $widget->add_render_attribute( $link_key, [
                'class' => [ 'anchor-link-item' ],
                'data-target' => '#' .$link['inner_section_ids'],
            ] );
            ?>
            <div <?php pxl_print_html($widget->get_render_attribute_string( $link_key )); ?>>
                <span><?php echo pxl_print_html($link['sb_link_text']); ?></span>
                <i class="zmdi zmdi-long-arrow-right"></i>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>
