<?php
use Elementor\Utils;

$default_settings = [
    'selected_image' => '',
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$thumbnail    = '';
if(!empty($selected_image['id'])){
    $img  = pxl_get_image_by_size( array(
        'attach_id'  => $selected_image['id'],
        'thumb_size' => 'full',
    ) );
    $thumbnail    = $img['thumbnail'];
}
$link_type = $settings['link_type'];
if(($link_type == 'url') && !empty( $settings['link']['url'])){
    $widget->add_render_attribute( 'text_link', 'href', $settings['link']['url'] );
    if ( $settings['link']['is_external'] ) {
        $widget->add_render_attribute( 'text_link', 'target', '_blank' );
    }
    if ( $settings['link']['nofollow'] ) {
        $widget->add_render_attribute( 'text_link', 'rel', 'nofollow' );
    }
    if ( ! empty( $settings['link']['custom_attributes'] ) ) {
        // Custom URL attributes should come as a string of comma-delimited key|value pairs
        $custom_attributes = Utils::parse_custom_attributes( $settings['link']['custom_attributes'] );
        $widget->add_render_attribute( 'link', $custom_attributes);
    }
}
if ($link_type == 'page') {
    $page_url = get_permalink($settings['page_link']);
    $widget->add_render_attribute( 'text_link', 'href', $page_url );
}

if(!empty($selected_image['id'])) : ?>
    <div class="pxl-image-landing layout-1">
        <div class="image-wrap">
            <a <?php pxl_print_html($widget->get_render_attribute_string( 'text_link' )); ?>>
                <?php echo wp_kses_post($thumbnail); ?>
            </a>
        </div>
        <div class="image-title">
            <a <?php pxl_print_html($widget->get_render_attribute_string( 'text_link' )); ?>>
                <?php echo esc_html($settings['title_text']); ?>
            </a>
        </div>
    </div>
<?php endif; ?>