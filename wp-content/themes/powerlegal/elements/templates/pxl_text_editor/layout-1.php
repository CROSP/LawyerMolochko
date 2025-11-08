<?php
$default_settings = [
    'text_color' => ''
];

$settings = array_merge($default_settings, $settings);
extract($settings);
  
$editor_content = $widget->get_settings_for_display( 'text_editor' );
$editor_content = $widget->parse_text_editor( $editor_content );

$dropcap = $widget->get_setting('dropcap','false');

$widget->add_render_attribute( 'text_editor_wrap', 'id', pxl_get_element_id($settings));
$widget->add_render_attribute( 'text_editor_wrap', 'class', ['pxl-text-editor-wrap d-flex']);



$widget->add_render_attribute( 'text_editor', 'class', [ 'pxl-text-editor', 'elementor-clearfix' ] );
$widget->add_inline_editing_attributes( 'text_editor', 'advanced' );
 

?>
<div <?php pxl_print_html($widget->get_render_attribute_string( 'text_editor_wrap' )); ?> >
	<div <?php echo ''.$widget->get_render_attribute_string( 'text_editor' ); ?>>
        <?php if($dropcap == 'true') {
            ?>
            <span class="pxl-dropcap"><?php pxl_print_html($editor_content);?></span>
            <?php
        }else {
             pxl_print_html($editor_content);
        } ?>
	</div>
</div>