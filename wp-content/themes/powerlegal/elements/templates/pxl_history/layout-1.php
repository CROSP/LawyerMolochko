<?php
$default_settings = [
    'history_items' => '',
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$html_id = pxl_get_element_id($settings);
$history = $widget->get_settings('history_items');
if(!empty($history)) : ?>
<div id="<?php echo esc_attr($html_id); ?>" class="pxl-history">
    <div class="history-title-wrap">
        <?php if(!empty($settings['history_year'])): ?>
            <div class="history-year">
                <span><?php echo esc_html($settings['history_year']);?></span>
            </div>
        <?php endif; ?>
        <?php if(!empty($settings['history_title'])): ?>
            <h3 class="history-title">
                <?php echo esc_html($settings['history_title']);?>
            </h3>
        <?php endif; ?>
    </div>
    <div class="template-wrap">
        <?php foreach ($history as $key => $item):
            if(!empty($item['content_template'])){
                $history_content = Elementor\Plugin::$instance->frontend->get_builder_content_for_display( (int)$item['content_template']);
                ?>
                <div class="template-item">
                    <?php pxl_print_html($history_content); ?>
                    <span class="dot-pulse"></span>
                </div>
                <?php
            }
        endforeach;
        ?>
    </div>
</div>
<?php endif; ?>