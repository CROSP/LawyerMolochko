<?php 
$default_settings = [
    'logo' => '',
    'logo_max_height' => '',
    'logo_link' => '' 
];
 

$settings = array_merge($default_settings, $settings);
extract($settings);

$thumbnail    = '';
 
if(!empty($logo['id'])){
    $img  = pxl_get_image_by_size( array(
        'attach_id'  => $logo['id'],
        'thumb_size' => 'full',
    ) );
    $thumbnail    = $img['thumbnail'];
}
if ( ! empty( $logo_link['url'] ) ) {
    $widget->add_render_attribute( 'logo_link', 'href', $logo_link['url'] );

    if ( $logo_link['is_external'] ) {
        $widget->add_render_attribute( 'logo_link', 'target', '_blank' );
    }

    if ( $logo_link['nofollow'] ) {
        $widget->add_render_attribute( 'logo_link', 'rel', 'nofollow' );
    }
}

if(!empty($logo['id'])) : ?>
    <div class="pxl-logo d-flex align-items-center">
        <?php if ( ! empty( $logo_link['url'] ) ) { ?><a <?php pxl_print_html($widget->get_render_attribute_string( 'logo_link' )); ?>><?php } ?>
            <?php if ( ! empty( $logo['url'] ) ) { echo wp_kses_post($thumbnail); } ?>
        <?php if ( ! empty( $logo_link['url'] ) ) { ?></a><?php } ?>
    </div>
<?php endif; ?>