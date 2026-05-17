<?php
use Elementor\Icons_Manager;
use Elementor\Utils;
Icons_Manager::enqueue_shim();
$default_settings = [
    'col_xxl' => '3',
    'col_xl' => '3',
    'col_lg' => '2',
    'col_md' => '2',
    'col_sm' => '2',
    'col_xs' => '1',
    'content_list' => []
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$col_xxl = 'col-xxl-'.str_replace('.', '',12 / floatval( $settings['col_xxl']));
$col_xl  = 'col-xl-'.str_replace('.', '',12 / floatval( $settings['col_xl']));
$col_lg  = 'col-lg-'.str_replace('.', '',12 / floatval( $settings['col_lg']));
$col_md  = 'col-md-'.str_replace('.', '',12 / floatval( $settings['col_md']));
$col_sm  = 'col-sm-'.str_replace('.', '',12 / floatval( $settings['col_sm'])); 
$col_xs  = 'col-'.str_replace('.', '',12 / floatval( $settings['col_xs'])); 

$item_class = trim(implode(' ', ['grid-item', $col_xxl, $col_xl, $col_lg, $col_md, $col_sm, $col_xs]));
$grid_sizer = trim(implode(' ', [ $col_xxl, $col_xl, $col_lg, $col_md, $col_sm, $col_xs]));

$animate_cls = '';
if ( !empty( $item_animation ) ) {
    $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
} 
$item_animation_delay = !empty($item_animation_delay) ? $item_animation_delay : '200';

$img_size = !empty( $img_size ) ? $img_size : '600x600';
$layout_mode = $widget->get_setting('layout_mode', 'fitRows');
if(is_admin())
    $grid_class = 'pxl-grid-inner pxl-grid-masonry-adm row relative';
else
    $grid_class = 'pxl-grid-inner pxl-grid-masonry row relative overflow-hidden';

if ( ! $settings['wp_gallery'] ) {
    return;
}
$randGallery = $settings['wp_gallery'];
if ($settings['gallery_rand'] == 'rand'){
    shuffle($randGallery);
}

?>
<div class="pxl-grid pxl-image-gallery images-light-box layout-1" data-layout-mode="<?php echo esc_attr($layout_mode);?>">
    <div class="<?php echo esc_attr($grid_class) ?>">
        <?php foreach ( $randGallery as $key => $value):
            $image = isset($value['id']) ? $value['id'] : '';
            $image_title = "";
            if(!empty($image)) {
                $image_title = get_the_title($image);
                $img = pxl_get_image_by_size( array(
                    'attach_id'  => $image,
                    'thumb_size' => $img_size,
                    'class' => 'no-lazyload',
                ));
                $thumbnail = $img['thumbnail'];
            }
            $increase = $key + 1;
            $data_settings = '';
            if ( !empty( $item_animation ) ) {
                $data_animation =  json_encode([
                    'animation'      => $item_animation,
                    'animation_delay' => ((float)$item_animation_delay * $increase)
                ]);
                $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
            }
            ?>
            <div class="<?php echo esc_attr($item_class.' '.$animate_cls); ?>" <?php pxl_print_html($data_settings); ?>>
                <?php if(!empty($image)) : ?>
                    <div class="item-inner cross-hover">
                        <?php echo wp_kses_post($thumbnail); ?>
                        <div class="up-icon">
                            <a class="light-box" data-elementor-open-lightbox="no" href="<?php echo esc_url(wp_get_attachment_image_url($image, 'full')); ?>" title="<?php echo esc_attr($image_title)?>">
                                <i class="zmdi zmdi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="image-caption">
                        <?php echo wp_get_attachment_caption($image);?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="grid-sizer <?php echo esc_attr($grid_sizer); ?>"></div>
    </div>
</div>
