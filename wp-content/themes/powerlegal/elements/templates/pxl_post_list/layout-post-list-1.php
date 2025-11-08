<?php
$html_id = pxl_get_element_id($settings);
$tax = ['category'];
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
    'post', 
    ['source' => $source, 'orderby' => $orderby, 'order' => $order, 'limit' => $limit, 'post_ids' => $post_ids], 
    $tax
));
 
$post_type            = $widget->get_setting('post_type','post');
$layout               = $widget->get_setting('layout_'.$post_type, 'post-list-1');
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
    'item_animation_delay'    => $widget->get_setting('item_animation_delay', '150'),  
    'col_xs'                  => '1',
    'col_sm'                  => '1',
    'col_md'                  => '1',
    'col_lg'                  => '1',
    'col_xl'                  => '1',
    'col_xxl'                 => '1',
    'img_size'            => $widget->get_setting('img_size','full'),
    'show_date'           => $widget->get_setting('show_date'),
    'show_category'       => $widget->get_setting('show_category'),
    'show_author'         => $widget->get_setting('show_author'),
    'show_comment'        => $widget->get_setting('show_comment'),
    'show_excerpt'        => $widget->get_setting('show_excerpt'),
    'num_words'           => $widget->get_setting('num_words', 15),
    'show_button'         => $widget->get_setting('show_button'),
    'button_text'         => $widget->get_setting('button_text'),
);

$widget->add_render_attribute( 'wrapper', [
    'id'               => $html_id,
    'class'            => trim('pxl-grid pxl-post-list layout-'.$layout),
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
    <?php if ($select_post_by === 'term_selected' && $filter == "true"): ?>
        <div class="grid-filter-wrap d-flex">
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