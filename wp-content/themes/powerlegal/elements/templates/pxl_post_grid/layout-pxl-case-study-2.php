<?php
$html_id = pxl_get_element_id($settings);
$tax = ['pxl-case-study-category'];
$select_post_by = $widget->get_setting('select_post_by', 'term_selected');
$source = $post_ids = [];

if($select_post_by === 'post_selected'){
    $post_ids = $widget->get_setting('source_'.$settings['post_type'].'_post_ids', '');
}else{
    $source  = $widget->get_setting('source_'.$settings['post_type'], '');
}
 
$orderby = $widget->get_setting('orderby', 'date');
$order = $widget->get_setting('order', 'desc');
$limit = $widget->get_setting('limit', 6);

extract(pxl_get_posts_of_grid(
    'pxl-case-study',
    ['source' => $source, 'orderby' => $orderby, 'order' => $order, 'limit' => $limit, 'post_ids' => $post_ids], 
    $tax
));
 
$post_type            = $widget->get_setting('post_type','pxl-case-study');
$layout               = $widget->get_setting('layout_'.$post_type, 'pxl-case-study-2');
$layout_mode          = $widget->get_setting('layout_mode', 'fitRows');
$filter               = $widget->get_setting('filter', 'false');
$filter_default_title = $widget->get_setting('filter_default_title', 'All');

$pagination_type      = $widget->get_setting('pagination_type', 'pagination');
  
$load_more = array(
    'tax'                 => $tax,
    'post_type'           => $post_type,   
    'layout'              => $layout,
    'select_post_by'      => $select_post_by,
    'layout_mode'         => $layout_mode,
    'filter'              => $filter,
    'startPage'           => $paged,
    'maxPages'            => $max,
    'total'               => $total,
    'perpage'             => $limit,
    'nextLink'            => $next_link,
    'source'              => $source,
    'post_ids'            => $post_ids,
    'orderby'             => $orderby,
    'order'               => $order,
    'limit'               => $limit,
    'item_animation'          => $widget->get_setting('item_animation', ''),  
    'item_animation_duration' => $widget->get_setting('item_animation_duration', 'normal'),  
    'item_animation_delay'    => $widget->get_setting('item_animation_delay', '100'),
    'col_xs'                  => $widget->get_setting('col_xs', '1'),
    'col_sm'                  => $widget->get_setting('col_sm', '1'),
    'col_md'                  => $widget->get_setting('col_md', '2'), 
    'col_lg'                  => $widget->get_setting('col_lg', '2'),
    'col_xl'                  => $widget->get_setting('col_xl', '3'),
    'col_xxl'                 => $widget->get_setting('col_xxl', '3'),
    'img_size'            => $widget->get_setting('img_size','full'),
    'grid_custom_columns' => $widget->get_setting('grid_custom_columns'),
    'gap'                 => $widget->get_setting('gap', '30'),
    'gap_extra'           => $widget->get_setting('gap_extra', '20'),
    'show_category'       => $widget->get_setting('show_category'),
    'show_excerpt'        => $widget->get_setting('show_excerpt'),
    'num_words'           => $widget->get_setting('num_words', 15),
    'show_button'         => $widget->get_setting('show_button'),
    'button_text'         => $widget->get_setting('button_text', 'READ MORE'),
);

$widget->add_render_attribute( 'wrapper', [
    'id'               => $html_id,
    'class'            => trim('pxl-grid pxl-case-study-grid layout-'.$layout),
    'data-layout-mode' => $layout_mode,
    'data-start-page'  => $paged,
    'data-max-pages'   => $max,
    'data-total'       => $total,
    'data-perpage'     => $limit,
    'data-next-link'   => $next_link
]);
 
if(is_admin())
    $grid_class = 'pxl-grid-inner pxl-grid-masonry-adm row relative animation-time';
else
    $grid_class = 'pxl-grid-inner pxl-grid-masonry row relative overflow-hidden animation-time';

$widget->add_render_attribute( 'grid', 'class', $grid_class);
 
if( count($posts) <= 0){
    echo '<div class="pxl-no-post-grid">'.esc_html__( 'No Post Found', 'powerlegal' ). '</div>';
    return;
}
?>

<div <?php pxl_print_html($widget->get_render_attribute_string( 'wrapper' )) ?>>
    <div class="pxl-grid-overlay"></div>
    <?php if ($select_post_by === 'term_selected' && $filter == "true"): ?>
        <div class="grid-filter-wrap d-flex <?php echo esc_html($settings['filter_alignment']);?>">
            <span class="filter-item active" data-filter="*"><?php echo esc_html($filter_default_title); ?></span>
            <?php foreach ($categories as $category): ?>
                <?php $category_arr = explode('|', $category); ?>
                <?php $term = get_term_by('slug',$category_arr[0], $category_arr[1]); ?>

                <span class="filter-item" data-filter="<?php echo esc_attr('.' . $term->slug); ?>">
                    <?php echo esc_html($term->name); ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div <?php pxl_print_html($widget->get_render_attribute_string('grid')); ?>> 
        <?php powerlegal_get_post_grid($posts, $load_more); ?>
    </div>
    

    <?php if ($pagination_type == 'pagination') { ?>
        <div class="pxl-grid-pagination d-flex" data-loadmore="<?php echo esc_attr(json_encode($load_more)); ?>" data-query="<?php echo esc_attr(json_encode($args)); ?>">
            <?php powerlegal()->page->get_pagination($query, true); ?>
        </div>
    <?php } ?>
    <?php if (!empty($next_link) && $pagination_type == 'loadmore'): 
        $icon_pos = ( !empty($settings['loadmore_icon']) && !empty($settings['icon_align'])) ? $settings['icon_align'] : ''; 
        ?>
        <div class="pxl-load-more d-flex" data-loadmore="<?php echo esc_attr(json_encode($load_more)); ?>">
            <span class="btn btn-grid-loadmore <?php echo esc_attr($icon_pos)?>">
                <?php 
                if(!empty($settings['loadmore_icon']))   
                    \Elementor\Icons_Manager::render_icon( $settings['loadmore_icon'], [ 'aria-hidden' => 'true', 'class' => 'btn-icon '.$icon_pos ], 'span' ); 
                ?>
                <span class="btn-text"><?php echo esc_html($settings['loadmore_text']); ?></span>
                <span class="pxl-btn-icon pxli-spinner"></span>
            </span>
        </div>
    <?php endif; ?>
</div>