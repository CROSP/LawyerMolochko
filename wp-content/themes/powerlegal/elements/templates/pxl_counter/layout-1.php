<?php
$widget->add_render_attribute( 'counter', [
    'class' => 'pxl-counter-number-value',
    'data-startnumber' => $widget->get_setting('starting_number', 0),
    'data-endnumber' => $settings['ending_number'],
] );

if ( ! empty( $settings['thousand_separator'] ) ) {
    $delimiter = empty( $settings['thousand_separator_char'] ) ? '' : $settings['thousand_separator_char'];
    $widget->add_render_attribute( 'counter', 'data-delimiter', $delimiter );
}
 
?>
<div class="pxl-counter layout1">
    <div class="counter-inner">
        <div class="counter-content">
            <div class="counter-number">
                <?php if(!empty($settings['prefix'])) : ?>
                    <span class="counter-number-prefix"><?php echo pxl_print_html($settings['prefix']); ?></span>
                <?php endif; ?>
                <span <?php pxl_print_html($widget->get_render_attribute_string( 'counter' )); ?>><?php echo esc_html($settings['starting_number']); ?></span>
                <?php if(!empty($settings['suffix'])) : ?>
                    <span class="counter-number-suffix"><?php echo pxl_print_html($settings['suffix']); ?></span>
                <?php endif; ?>
            </div>
            <?php if ( $settings['title'] ) : ?>
                <div class="counter-title"><?php pxl_print_html($settings['title']); ?></div>
            <?php endif; ?>
        </div>
         
    </div>
</div>