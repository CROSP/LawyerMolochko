<?php
$template = (int)$widget->get_setting('template',0);
$widget->add_render_attribute('anchor', 'class', 'pxl-anchor side-panel d-flex align-items-center');
$target = '.pxl-hidden-template-'.$template;
if($template > 0 ){
    if ( !has_action( 'pxl_anchor_target_hidden_panel_'.$template) ){
        add_action( 'pxl_anchor_target_hidden_panel_'.$template, 'powerlegal_hook_anchor_hidden_panel' );
    }

}else{
    add_action( 'pxltheme_anchor_target', 'powerlegal_hook_anchor_custom' );
}

$custom_cls = $widget->get_setting('custom_class','');
 
?>
<div class="pxl-anchor-wrap d-flex align-items-center align-content-center <?php echo esc_attr($custom_cls);?>">
	<a href="#pxl-<?php echo esc_attr($template)?>" <?php pxl_print_html($widget->get_render_attribute_string( 'anchor' )); ?> data-target="<?php echo esc_attr($target)?>">
	    <?php 
	    if( $widget->get_setting('icon_type','none') == 'lib'){
	    	echo '<div class="pxl-anchor-icon d-inline-flex">';
	    	\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true', 'class' => '' ], 'span' );
	    	echo '</div>';
	    }
	    if($widget->get_setting('icon_type','none') == 'custom'){
	    	echo '<div class="pxl-icon pxl-anchor-icon custom"><span></span><span></span><span></span></div>';
	    } 
	    if(!empty($widget->get_setting('title',''))){
	    	echo '<span class="anchor-title d-inline-flex">'.$widget->get_setting('title', '').'</span>';
	    } ?>
	</a>
</div>
 