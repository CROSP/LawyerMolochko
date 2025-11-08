<?php
use Elementor\Controls_Manager;
use Elementor\Embed;
use Elementor\Group_Control_Image_Size;

add_action( 'wp_ajax_powerlegal_get_pagination_html', 'powerlegal_get_pagination_html' );
add_action( 'wp_ajax_nopriv_powerlegal_get_pagination_html', 'powerlegal_get_pagination_html' );
function powerlegal_get_pagination_html(){
    try{
        if(!isset($_POST['query_vars'])){
            throw new Exception(__('Something went wrong while requesting. Please try again!', 'powerlegal'));
        }
        $query = new WP_Query($_POST['query_vars']);
        ob_start();
        powerlegal()->page->get_pagination( $query,  true );
        $html = ob_get_clean();
        wp_send_json(
            array(
                'status' => true,
                'message' => esc_attr__('Load Successfully!', 'powerlegal'),
                'data' => array(
                    'html' => $html,
                    'query_vars' => $_POST['query_vars'],
                    'post' => $query->have_posts()
                ),
            )
        );
    }
    catch (Exception $e){
        wp_send_json(array('status' => false, 'message' => $e->getMessage()));
    }
    die;
}
add_action( 'wp_ajax_powerlegal_get_filter_html', 'powerlegal_get_filter_html' );
add_action( 'wp_ajax_nopriv_powerlegal_get_filter_html', 'powerlegal_get_filter_html' );
function powerlegal_get_filter_html(){
    try{
        if(!isset($_POST['settings'])){
            throw new Exception(__('Something went wrong while requesting. Please try again!', 'powerlegal'));
        }
        $settings = $_POST['settings'];
        $loadmore_filter = $_POST['loadmore_filter'];
        if($loadmore_filter == '1'){
            set_query_var('paged', 1);
            $limit = isset($settings['limit'])?$settings['limit']:'6';
            $limitx = (int)$limit * (int)$settings['paged'];
        }else{
            set_query_var('paged', $settings['paged']);
            $limitx = isset($settings['limit'])?$settings['limit']:'6';
        }
        extract(pxl_get_posts_of_grid($settings['post_type'], [
            'source' => isset($settings['source'])?$settings['source']:'',
            'orderby' => isset($settings['orderby'])?$settings['orderby']:'date',
            'order' => isset($settings['order'])?$settings['order']:'desc',
            'limit' => $limitx,
            'post_ids' => isset($settings['post_ids'])?$settings['post_ids']: [],
        ],
            $settings['tax']
        ));
        $settings['filter_default_title'] = !empty($settings['filter_default_title']) ? $settings['filter_default_title'] : esc_html__( 'All', 'powerlegal' );
        ob_start();

        ?>
        <span class="filter-item active" data-filter="*">
                <?php if($settings['show_cat_count'] == '1'): ?>
                    <span><?php echo count($posts); ?></span>
                <?php endif; ?>
            <?php echo esc_html($settings['filter_default_title']) ?>
            </span>
        <?php foreach ($categories as $category): ?>
            <?php $category_arr = explode('|', $category); ?>
            <?php $term = get_term_by('slug',$category_arr[0], $category_arr[1]); ?>
            <?php
            $num = 0;
            foreach ($posts as $key => $post){
                $this_terms = get_the_terms( $post->ID, $settings['tax'][0] );
                $term_list = [];
                foreach ($this_terms as $t) {
                    $term_list[] = $t->slug;
                }
                if(in_array($term->slug,$term_list))
                    $num++;
            }
            if($num > 0):
                ?>
                <span class="filter-item" data-filter="<?php echo esc_attr('.' . $term->slug); ?>">  
                    <?php if($settings['show_cat_count'] == '1'): ?>
                        <span><?php echo esc_html($num); ?></span>
                    <?php endif; ?>
                    <?php echo esc_html($term->name); ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php
        $html = ob_get_clean();
        wp_send_json(
            array(
                'status' => true,
                'message' => esc_attr__('Load Successfully!', 'powerlegal'),
                'data' => array(
                    'html' => $html,
                    'paged' => $settings['paged'],
                    'posts' => $posts,
                    'max' => $max,
                ),
            )
        );
    }
    catch (Exception $e){
        wp_send_json(array('status' => false, 'message' => $e->getMessage()));
    }
    die;
}

add_action( 'wp_ajax_powerlegal_load_more_product_grid', 'powerlegal_load_more_product_grid' );
add_action( 'wp_ajax_nopriv_powerlegal_load_more_product_grid', 'powerlegal_load_more_product_grid' );
function powerlegal_load_more_product_grid(){
    try{
        if(!isset($_POST['settings'])){
            throw new Exception(__('Something went wrong while requesting. Please try again!', 'powerlegal'));
        }
        $settings = $_POST['settings'];
        set_query_var('paged', $settings['paged']);
        $query_type         = isset($settings['query_type']) ? $settings['query_type'] : 'recent_product';
        $post_per_page      = isset($settings['limit']) ? $settings['limit'] : 8;
        $product_ids        = isset($settings['product_ids']) ? $settings['product_ids'] : '';
        $categories         = isset($settings['categories']) ? $settings['categories'] : '';
        $param_args         = isset($settings['param_args']) ? $settings['param_args'] : [];

        $col_xxl = isset($settings['col_xxl']) ? 'col-xxl-'.str_replace('.', '',12 / floatval($settings['col_xxl'])) : '';
        $col_xl = isset($settings['col_xl']) ? 'col-xl-'.str_replace('.', '',12 / floatval( $settings['col_xl'])) : '';
        $col_lg = isset($settings['col_lg']) ? 'col-lg-'.str_replace('.', '',12 / floatval( $settings['col_lg'])) : '';
        $col_md = isset($settings['col_md']) ? 'col-md-'.str_replace('.', '',12 / floatval( $settings['col_md'])) : '';
        $col_sm = isset($settings['col_sm']) ? 'col-sm-'.str_replace('.', '',12 / floatval( $settings['col_sm'])) : '';
        $col_xs = isset($settings['col_xs']) ? 'col-'.str_replace('.', '',12 / floatval( $settings['col_xs'])) : '';

        $item_class = trim(implode(' ', ['grid-item', $col_xxl, $col_xl, $col_lg, $col_md, $col_sm, $col_xs]));

        $loop = powerlegal_woocommerce_query($query_type,$post_per_page,$product_ids,$categories,$param_args);
        extract($loop);

        $data_animation = [];
        $animate_cls = '';
        $data_settings = '';
        if ( !empty( $settings['item_animation'] ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$settings['item_animation_duration'];
            $data_animation['animation'] = $settings['item_animation'];
            $data_animation['animation_delay'] = $settings['item_animation_delay'];
        }
        if($posts->have_posts()){
            ob_start();
            $d = 0;
            while ($posts->have_posts()) {
                $posts->the_post();
                global $product;
                $d++;
                $term_list = array();
                $term_of_post = wp_get_post_terms($product->get_ID(), 'product_cat');
                foreach ($term_of_post as $term) {
                    $term_list[] = $term->slug;
                }
                $filter_class = implode(' ', $term_list);

                if ( !empty( $data_animation ) ) {
                    $data_animation['animation_delay'] = ((float)$settings['item_animation_delay'] * $d);
                    $data_animations = json_encode($data_animation);
                    $data_settings = 'data-settings="'.esc_attr($data_animations).'"';
                }

                ?>
                <div class="<?php echo trim(implode(' ', [$item_class, $filter_class, $animate_cls])); ?>" <?php pxl_print_html($data_settings); ?>>
                    <?php
                    do_action( 'woocommerce_before_shop_loop_item' );
                    do_action( 'woocommerce_before_shop_loop_item_title' );
                    do_action( 'woocommerce_shop_loop_item_title' );
                    do_action( 'woocommerce_after_shop_loop_item_title' );
                    do_action( 'woocommerce_after_shop_loop_item' );
                    ?>

                </div>
                <?php
            }
            if($settings['layout_mode'] == 'masonry')
                echo '<div class="grid-sizer '.$item_class.'"></div>';
            $html = ob_get_clean();
            wp_send_json(
                array(
                    'status' => true,
                    'message' => esc_html__('Load Post Grid Successfully!', 'powerlegal'),
                    'data' => array(
                        'html'  => $html,
                        'paged' => $settings['paged'],
                        'posts' => $posts,
                        'max' => $max,
                    ),
                )
            );
        }else{
            wp_send_json(
                array(
                    'status' => false,
                    'message' => esc_html__('Load Post Grid No More!', 'powerlegal')
                )
            );
        }
    }
    catch (Exception $e){
        wp_send_json(array('status' => false, 'message' => $e->getMessage()));
    }
    die;
}

add_action( 'wp_ajax_powerlegal_load_more_post_grid', 'powerlegal_load_more_post_grid' );
add_action( 'wp_ajax_nopriv_powerlegal_load_more_post_grid', 'powerlegal_load_more_post_grid' );
function powerlegal_load_more_post_grid(){
    try{
        if(!isset($_POST['settings'])){
            throw new Exception(__('Something went wrong while requesting. Please try again!', 'powerlegal'));
        }
        $settings = $_POST['settings'];
        set_query_var('paged', $settings['paged']);
        extract(pxl_get_posts_of_grid($settings['post_type'], [
            'source' => isset($settings['source'])?$settings['source']:'',
            'orderby' => isset($settings['orderby'])?$settings['orderby']:'date',
            'order' => isset($settings['order'])?$settings['order']:'desc',
            'limit' => isset($settings['limit'])?$settings['limit']:'6',
            'post_ids' => isset($settings['post_ids'])?$settings['post_ids']: [],
        ],
            $settings['tax']
        ));
        ob_start();

        powerlegal_get_post_grid($posts, $settings);
        $html = ob_get_clean();
        wp_send_json(
            array(
                'status' => true,
                'message' => esc_attr__('Load Successfully!', 'powerlegal'),
                'data' => array(
                    'html' => $html,
                    'paged' => $settings['paged'],
                    'posts' => $posts,
                    'max' => $max,
                ),
            )
        );
    }
    catch (Exception $e){
        wp_send_json(array('status' => false, 'message' => $e->getMessage()));
    }
    die;
}

if(!function_exists('powerlegal_get_post_grid')){
    function powerlegal_get_post_grid($posts = [], $settings = []){
        if (empty($posts) || !is_array($posts) || empty($settings) || !is_array($settings)) {
            return;
        }
        extract($settings);

        $col_xxl = 'col-xxl-'.str_replace('.', '',12 / floatval( $settings['col_xxl']));
        $col_xl  = 'col-xl-'.str_replace('.', '',12 / floatval( $settings['col_xl']));
        $col_lg  = 'col-lg-'.str_replace('.', '',12 / floatval( $settings['col_lg']));
        $col_md  = 'col-md-'.str_replace('.', '',12 / floatval( $settings['col_md']));
        $col_sm  = 'col-sm-'.str_replace('.', '',12 / floatval( $settings['col_sm']));
        $col_xs  = 'col-'.str_replace('.', '',12 / floatval( $settings['col_xs']));

        $item_class = trim(implode(' ', ['grid-item', $col_xxl, $col_xl, $col_lg, $col_md, $col_sm, $col_xs]));

        $args_m = [];
        $settings['thumbnail'] = '';
        if($layout_mode == 'masonry') {
            foreach ($posts as $key => $post){
                if( !empty($grid_custom_columns[$key]) ){
                    $item_cls = $item_class;
                    $image_size = $img_size;

                    $col_xxl_c = 'col-xxl-'.str_replace('.', '',12 / floatval($grid_custom_columns[$key]['col_xxl_c']));
                    $col_xl_c = 'col-xl-'.str_replace('.', '',12 / floatval( $grid_custom_columns[$key]['col_xl_c']));
                    $col_lg_c = 'col-lg-'.str_replace('.', '',12 / floatval( $grid_custom_columns[$key]['col_lg_c']));
                    $col_md_c = 'col-md-'.str_replace('.', '',12 / floatval( $grid_custom_columns[$key]['col_md_c']));
                    $col_sm_c = 'col-sm-'.str_replace('.', '',12 / floatval( $grid_custom_columns[$key]['col_sm_c']));
                    $col_xs_c = 'col-xs-'.str_replace('.', '',12 / floatval( $grid_custom_columns[$key]['col_xs_c']));

                    $item_cls = trim(implode(' ', ['grid-item', $col_xxl_c, $col_xl_c, $col_lg_c, $col_md_c, $col_sm_c, $col_xs_c]));

                    if( !empty($grid_custom_columns[$key]['img_size_c']) )
                        $image_size = $grid_custom_columns[$key]['img_size_c'];

                    if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
                        $img_id = get_post_thumbnail_id($post->ID);
                        if($img_id){
                            $img = pxl_get_image_by_size( array(
                                'attach_id'  => $img_id,
                                'thumb_size' => $image_size,
                                'class' => 'no-lazyload',
                            ));
                            $thumbnail = $img['thumbnail'];
                        }else{
                            $thumbnail = get_the_post_thumbnail($post->ID, $image_size);
                        }
                    }

                    $increase = $key + 1;
                    $data_settings = '';
                    if ( !empty( $grid_custom_columns[$key]['item_c_animation'] ) ) {

                        $item_cls.= ' pxl-animate pxl-invisible animated-'.$grid_custom_columns[$key]['item_c_animation_duration'];
                        $item_c_animation_delay = !empty($grid_custom_columns[$key]['item_c_animation_delay']) ? $grid_custom_columns[$key]['item_c_animation_delay'] : '150';
                        $data_animation =  json_encode([
                            'animation'      => $grid_custom_columns[$key]['item_c_animation'],
                            'animation_delay' => ((float)$item_c_animation_delay * $increase)
                        ]);
                        $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
                    }


                    $args_m[$key] = ['item_class' => $item_cls, 'thumbnail' => htmlspecialchars($thumbnail), 'data_setting' => $data_settings ];
                }else{
                    $args_m[$key] = [];
                }
            }
        }
        $settings['item_class'] = $item_class;

        switch ($layout) {
            case 'post-list-1':
                powerlegal_get_post_list_layout1($posts, $settings, $args_m);
                break;
            case 'post-list-2':
                powerlegal_get_post_list_layout2($posts, $settings, $args_m);
                break;
            case 'post-1':
                powerlegal_get_post_grid_layout1($posts, $settings, $args_m);
                break;
            case 'pxl-case-study-list-1':
                powerlegal_get_pxl_case_study_list_layout1($posts, $settings, $args_m);
                break;
            case 'pxl-case-study-1':
                powerlegal_get_post_grid_pxl_case_study1($posts, $settings, $args_m);
                break;
            case 'pxl-case-study-2':
                powerlegal_get_post_grid_pxl_case_study2($posts, $settings, $args_m);
                break;
            case 'pxl-practice-area-1':
                powerlegal_get_post_grid_pxl_practice_area1($posts, $settings, $args_m);
                break;
            case 'pxl-practice-area-2':
                powerlegal_get_post_grid_pxl_practice_area2($posts, $settings, $args_m);
                break;
            default:
                return false;
                break;
        }
        if($layout_mode == 'masonry')
            echo '<div class="grid-sizer '.$item_class.'"></div>';

    }
}

function powerlegal_get_post_list_layout1($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
            $img_id = get_post_thumbnail_id($post->ID);
            if($img_id){
                $img = pxl_get_image_by_size( array(
                    'attach_id'  => $img_id,
                    'thumb_size' => $img_size,
                    'class' => 'no-lazyload',
                ));
                $thumbnail = $img['thumbnail'];
            }else{
                $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
            }
        }
        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));
        $button_text = !empty($button_text) ? $button_text : esc_html__('Read more', 'powerlegal');
        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }
        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];
        $author = get_user_by('id', $post->post_author);
        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '. $filter_class); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner">
                <div class="row gx-lg-30 align-items-lg-center">
                    <?php
                    if (has_post_format('quote', $post->ID)){
                        $quote_text = get_post_meta( $post->ID, 'featured-quote-text', true );
                        $quote_cite = get_post_meta( $post->ID, 'featured-quote-cite', true );
                        ?>
                        <div class="pxl-archive-post format-quote">
                            <div class="format-wrap">
                                <div class="quote-inner">
                                    <div class="quote-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2000 2000"><title>PXL quote</title><path d="M897.25,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.51,55.66-2.91,2-5.78,4.11-8.63,6.23-13.95,8.37-41.27,17.3-54.91-28.87C313.35,839.08,357.21,393.26,817,228.43V123.36c0,1.21-46.27,13-49,13.85a1091.25,1091.25,0,0,0-126.19,49.36C512.33,246.38,393,329.9,297,435.73,155.35,591.9,68.71,796.84,59.14,1007.7,51.82,1168.82,94,1333.87,210,1451c65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C846.78,1441.86,897.25,1335.88,897.25,1223.93Z"/><path class="cls-1" d="M1903.18,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.5,55.66-2.92,2-5.79,4.11-8.64,6.23-14,8.37-41.27,17.3-54.91-28.87-18.8-63.62,25.06-509.44,484.85-674.27V123.36c0,1.21-46.26,13-49,13.85a1090.39,1090.39,0,0,0-126.18,49.36c-129.45,59.81-248.8,143.33-344.79,249.16-141.64,156.17-228.28,361.11-237.85,572-7.32,161.12,34.82,326.17,150.9,443.25,65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C1852.71,1441.86,1903.18,1335.88,1903.18,1223.93Z"/></svg>
                                    </div>
                                    <a class="quote-text" href="<?php echo esc_url( get_permalink()); ?>"><?php echo esc_html($quote_text);?></a>
                                    <p class="quote-cite">
                                        <?php echo esc_html($quote_cite);?>
                                    </p>
                                    <?php
                                    if($show_author == 'true' || $show_category == 'true' || $show_comment == 'true') : ?>
                                        <div class="post-metas">
                                            <div class="meta-inner d-flex align-items-center hover-underline">
                                                <?php if($show_author == 'true') : ?>
                                                    <span class="post-author col-auto d-flex">
                                                        <span><?php echo esc_html__('By', 'powerlegal')."&nbsp;"; ?> </span>
                                                        <a href="<?php echo esc_url(get_author_posts_url($post->post_author, $author->user_nicename)); ?>"><?php echo esc_html($author->display_name); ?></a>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if($show_category == 'true') : ?>
                                                    <span class="post-category d-flex"><span><?php the_terms( $post->ID, 'category', '', ', ', '' ); ?></span></span>
                                                <?php endif; ?>
                                                <?php if($show_comment == 'true') : ?>
                                                    <span class="post-comments d-flex">
                                                        <a href="<?php echo get_comments_link($post->ID); ?>">
                                                            <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal'), $post->ID); ?></span>
                                                        </a>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }elseif (has_post_format('link', $post->ID)){
                        $link_url = get_post_meta( $post->ID, 'featured-link-url', true );
                        $link_text = get_post_meta( $post->ID, 'featured-link-text', true );
                        ?>
                        <div class="pxl-archive-post format-link">
                            <div class="format-wrap">
                                <div class="link-inner">
                                    <div class="link-icon">
                                        <a href="<?php echo esc_url( $link_url); ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 162.65 162.66"><path d="M151.76,10.89a37.29,37.29,0,0,0-52.67,0h0l-23,23L86.72,44.48l23-23h0A22.25,22.25,0,0,1,141.16,53L106.67,87.45a22.26,22.26,0,0,1-31.46,0l-10.6,10.6a37.24,37.24,0,0,0,52.67,0l34.48-34.48A37.3,37.3,0,0,0,151.76,10.89Z" transform="translate(0 0)"/><path d="M53,141.16h0A22.25,22.25,0,0,1,21.5,109.69L56,75.21a22.28,22.28,0,0,1,31.46,0L98.05,64.6a37.3,37.3,0,0,0-52.68,0L10.89,99.09a37.25,37.25,0,0,0,52.68,52.68h0l23-23L75.94,118.17Z" transform="translate(0 0)"/></svg></a>
                                    </div>
                                    <a class="link-text" target="_blank" href="<?php echo esc_url( $link_url); ?>"><?php echo esc_html($link_text);?></a>
                                </div>
                                <?php
                                if($show_author == 'true' || $show_category == 'true' || $show_comment == 'true') : ?>
                                    <div class="post-metas">
                                        <div class="meta-inner d-flex align-items-center hover-underline">
                                            <?php if($show_author == 'true') : ?>
                                                <span class="post-author col-auto d-flex">
                                                    <span><?php echo esc_html__('By ', 'powerlegal')."&nbsp;"; ?> </span>
                                                    <a href="<?php echo esc_url(get_author_posts_url($post->post_author, $author->user_nicename)); ?>"><?php echo esc_html($author->display_name); ?></a>
                                                </span>
                                            <?php endif; ?>
                                            <?php if($show_category == 'true') : ?>
                                                <span class="post-category d-flex"><span><?php the_terms( $post->ID, 'category', '', ', ', '' ); ?></span></span>
                                            <?php endif; ?>
                                            <?php if($show_comment == 'true') : ?>
                                                <span class="post-comments d-flex">
                                                    <a href="<?php echo get_comments_link($post->ID); ?>">
                                                        <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal'), $post->ID); ?></span>
                                                    </a>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif;
                                ?>
                            </div>
                        </div>
                        <?php
                    }else{
                        $featured_video = get_post_meta( $post->ID, 'featured-video-url', true );
                        $audio_url = get_post_meta( $post->ID, 'featured-audio-url', true );
                        if (isset( $thumbnail )){
                            ?>
                            <div class="item-featured col-lg-6">
                                <div class="post-image <?php if (empty($featured_video) && empty($audio_url)){ echo esc_attr('scale-hover');}?>">
                                    <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                                    <?php
                                    if($show_date == 'true') : ?>
                                        <div class="post-date">
                                            <?php echo get_the_date('d.m.Y', $post->ID); ?>
                                        </div>
                                    <?php endif;
                                    if(has_post_format('video', $post->ID) && !empty($featured_video)) : ?>
                                        <div class="pxl-video-popup">
                                            <div class="content-inner">
                                                <a class="video-play-button video-default" href="<?php echo esc_url($featured_video); ?>">
                                                    <i class="zmdi zmdi-play"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif;
                                    if(has_post_format('audio', $post->ID) && !empty($audio_url)) : ?>
                                        <a class="audio-play-button" href="<?php echo esc_url($audio_url); ?>" target="_blank">
                                            <i class="zmdi zmdi-volume-up"></i>
                                        </a>
                                    <?php endif;
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="item-content col-lg-6">
                            <?php
                            if ($show_category == 'true'){
                                ?>
                                <div class="pxl-archive-post">
                                    <div class="post-metas">
                                        <div class="meta-inner d-flex align-items-center">
                                            <span class="post-category d-flex hover-underline"><span><?php the_terms( $post->ID, 'category', '', ' / ', '' ); ?></span></span>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <h3 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h3>
                            <?php if($show_excerpt == 'true'): ?>
                                <div class="item-excerpt">
                                    <?php
                                    if(!empty($post->post_excerpt)){
                                        echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                    } else{
                                        $content = strip_shortcodes( $post->post_content );
                                        $content = apply_filters( 'the_content', $content );
                                        $content = str_replace(']]>', ']]&gt;', $content);
                                        echo wp_trim_words( $content, $num_words, null );
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php
                            if ($show_author == 'true' || $show_comment == 'true'){
                                ?>
                                <div class="post-metas">
                                    <div class="meta-inner d-flex align-items-center">
                                        <?php if($show_author == 'true') : ?>
                                            <span class="post-author d-flex align-items-center">
                                                <span class="author-avatar">
                                                    <?php echo get_avatar( get_the_author_meta( 'ID', $post->post_author ), 'thumbnail' ); ?>
                                                </span>
                                                <span><?php echo esc_html__('By', 'powerlegal'); ?>&nbsp;<?php the_author_posts_link(); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if($show_comment == 'true') : ?>
                                            <span class="post-comments d-flex align-items-center">
                                                <i class="zmdi zmdi-comment-text"></i>
                                                <a href="<?php echo get_comments_link($post->ID); ?>">
                                                    <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal'), $post->ID); ?></span>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_list_layout2($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
            $img_id = get_post_thumbnail_id($post->ID);
            if($img_id){
                $img = pxl_get_image_by_size( array(
                    'attach_id'  => $img_id,
                    'thumb_size' => $img_size,
                    'class' => 'no-lazyload',
                ));
                $thumbnail = $img['thumbnail'];
            }else{
                $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
            }
        }
        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));
        $button_text = !empty($button_text) ? $button_text : esc_html__('Read more', 'powerlegal');
        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }
        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];
        $author = get_user_by('id', $post->post_author);
        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '. $filter_class); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner">
                <?php
                if (has_post_format('quote', $post->ID)){
                    $quote_text = get_post_meta( $post->ID, 'featured-quote-text', true );
                    $quote_cite = get_post_meta( $post->ID, 'featured-quote-cite', true );
                    ?>
                    <div class="format-quote">
                        <div class="format-wrap">
                            <div class="fm-overlay"></div>
                            <div class="format-inner">
                                <div class="format-icon quote-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2000 2000"><title>PXL quote</title><path d="M897.25,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.51,55.66-2.91,2-5.78,4.11-8.63,6.23-13.95,8.37-41.27,17.3-54.91-28.87C313.35,839.08,357.21,393.26,817,228.43V123.36c0,1.21-46.27,13-49,13.85a1091.25,1091.25,0,0,0-126.19,49.36C512.33,246.38,393,329.9,297,435.73,155.35,591.9,68.71,796.84,59.14,1007.7,51.82,1168.82,94,1333.87,210,1451c65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C846.78,1441.86,897.25,1335.88,897.25,1223.93Z"/><path class="cls-1" d="M1903.18,1223.93c0-195.64-146-354.25-326-354.25-64.61,0-124.83,20.43-175.5,55.66-2.92,2-5.79,4.11-8.64,6.23-14,8.37-41.27,17.3-54.91-28.87-18.8-63.62,25.06-509.44,484.85-674.27V123.36c0,1.21-46.26,13-49,13.85a1090.39,1090.39,0,0,0-126.18,49.36c-129.45,59.81-248.8,143.33-344.79,249.16-141.64,156.17-228.28,361.11-237.85,572-7.32,161.12,34.82,326.17,150.9,443.25,65.33,65.9,160.32,124.88,254,137.17,95.83,12.57,197.94-9.2,277.69-64.56q8.52-5.91,16.67-12.32C1852.71,1441.86,1903.18,1335.88,1903.18,1223.93Z"/></svg>
                                </div>
                                <a class="format-text quote-text" href="<?php echo esc_url( get_permalink()); ?>"><?php echo esc_html($quote_text);?></a>
                                <p class="quote-cite">
                                    <?php echo esc_html($quote_cite);?>
                                </p>
                                <?php
                                if($show_author == 'true' || $show_category == 'true' || $show_comment == 'true') : ?>
                                    <div class="post-metas">
                                        <div class="meta-inner d-flex align-items-center hover-underline">
                                            <?php if($show_author == 'true') : ?>
                                                <span class="post-author col-auto d-flex">
                                                        <span><?php echo esc_html__('By', 'powerlegal')."&nbsp;"; ?> </span>
                                                        <a href="<?php echo esc_url(get_author_posts_url($post->post_author, $author->user_nicename)); ?>"><?php echo esc_html($author->display_name); ?></a>
                                                    </span>
                                            <?php endif; ?>
                                            <?php if($show_category == 'true') : ?>
                                                <span class="post-category d-flex"><span><?php the_terms( $post->ID, 'category', '', ', ', '' ); ?></span></span>
                                            <?php endif; ?>
                                            <?php if($show_comment == 'true') : ?>
                                                <span class="post-comments col-auto d-flex">
                                                        <a href="<?php echo get_comments_link($post->ID); ?>">
                                                            <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal'), $post->ID); ?></span>
                                                        </a>
                                                    </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }elseif (has_post_format('link', $post->ID)){
                    $link_url = get_post_meta( $post->ID, 'featured-link-url', true );
                    $link_text = get_post_meta( $post->ID, 'featured-link-text', true );
                    ?>
                    <div class="format-link">
                        <div class="format-wrap">
                            <div class="fm-overlay"></div>
                            <div class="format-inner">
                                <div class="format-icon link-icon">
                                    <a href="<?php echo esc_url( $link_url); ?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 162.65 162.66"><path d="M151.76,10.89a37.29,37.29,0,0,0-52.67,0h0l-23,23L86.72,44.48l23-23h0A22.25,22.25,0,0,1,141.16,53L106.67,87.45a22.26,22.26,0,0,1-31.46,0l-10.6,10.6a37.24,37.24,0,0,0,52.67,0l34.48-34.48A37.3,37.3,0,0,0,151.76,10.89Z" transform="translate(0 0)"/><path d="M53,141.16h0A22.25,22.25,0,0,1,21.5,109.69L56,75.21a22.28,22.28,0,0,1,31.46,0L98.05,64.6a37.3,37.3,0,0,0-52.68,0L10.89,99.09a37.25,37.25,0,0,0,52.68,52.68h0l23-23L75.94,118.17Z" transform="translate(0 0)"/></svg></a>
                                </div>
                                <a class="format-text link-text" target="_blank" href="<?php echo esc_url( $link_url); ?>"><?php echo esc_html($link_text);?></a>
                                <?php
                                if($show_author == 'true' || $show_category == 'true' || $show_comment == 'true') : ?>
                                    <div class="post-metas">
                                        <div class="meta-inner d-flex align-items-center hover-underline">
                                            <?php if($show_author == 'true') : ?>
                                                <span class="post-author col-auto d-flex">
                                                    <span><?php echo esc_html__('By ', 'powerlegal')."&nbsp;"; ?> </span>
                                                    <a href="<?php echo esc_url(get_author_posts_url($post->post_author, $author->user_nicename)); ?>"><?php echo esc_html($author->display_name); ?></a>
                                                </span>
                                            <?php endif; ?>
                                            <?php if($show_category == 'true') : ?>
                                                <span class="post-category d-flex"><span><?php the_terms( $post->ID, 'category', '', ', ', '' ); ?></span></span>
                                            <?php endif; ?>
                                            <?php if($show_comment == 'true') : ?>
                                                <span class="post-comments d-flex">
                                                    <a href="<?php echo get_comments_link($post->ID); ?>">
                                                        <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' %', 'powerlegal'), $post->ID); ?></span>
                                                    </a>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }else{
                    $featured_video = get_post_meta( $post->ID, 'featured-video-url', true );
                    $audio_url = get_post_meta( $post->ID, 'featured-audio-url', true );
                    if (isset( $thumbnail )){
                        ?>
                        <div class="item-featured">
                            <div class="post-image">
                                <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                                <?php
                                if($show_date == 'true') : ?>
                                    <div class="post-date">
                                        <div class="day-month">
                                            <span class="date-day"><?php echo get_the_date('d', $post->ID)."."; ?></span>
                                            <span class="date-month"><?php echo get_the_date('m', $post->ID); ?></span>
                                        </div>
                                        <span class="date-year"><?php echo get_the_date('Y', $post->ID); ?></span>
                                    </div>
                                <?php endif;
                                if(has_post_format('video', $post->ID) && !empty($featured_video)) : ?>
                                    <div class="pxl-video-popup">
                                        <div class="content-inner">
                                            <a class="video-play-button video-default" href="<?php echo esc_url($featured_video); ?>">
                                                <i class="zmdi zmdi-play"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif;
                                if(has_post_format('audio', $post->ID) && !empty($audio_url)) : ?>
                                    <a class="audio-play-button" href="<?php echo esc_url($audio_url); ?>" target="_blank">
                                        <i class="zmdi zmdi-volume-up"></i>
                                    </a>
                                <?php endif;
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="item-content row gx-30">
                        <div class="content-text col-sm-7 col-xl-8">
                            <h3 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h3>
                            <?php if($show_excerpt == 'true'): ?>
                                <div class="item-excerpt">
                                    <?php
                                    if(!empty($post->post_excerpt)){
                                        echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                    } else{
                                        $content = strip_shortcodes( $post->post_content );
                                        $content = apply_filters( 'the_content', $content );
                                        $content = str_replace(']]>', ']]&gt;', $content);
                                        echo wp_trim_words( $content, $num_words, null );
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="content-meta col-sm-5 col-xl-4">
                            <?php
                            if ($show_author == 'true' || $show_comment == 'true' || $show_category == 'true'){
                                ?>
                                <div class="box-metas">
                                    <div class="meta-inner">
                                        <?php if($show_author == 'true') : ?>
                                            <div class="post-author d-flex align-items-center">
                                                <span><?php echo esc_html('POST:', 'powerlegal');?></span>
                                                <span>
                                                    <?php echo esc_html__('By', 'powerlegal'); ?>&nbsp;<?php the_author_posts_link(); ?>
                                                </span>
                                                <span class="author-avatar">
                                                    <?php echo get_avatar( get_the_author_meta( 'ID', $post->post_author ), 'thumbnail' ); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($show_comment == 'true') : ?>
                                            <div class="post-comments d-flex align-items-center">
                                                <span><?php echo esc_html('COMMENTS:', 'powerlegal');?></span>
                                                <a href="<?php echo get_comments_link($post->ID); ?>">
                                                    <span><?php comments_number(esc_html__('No Comments', 'powerlegal'), esc_html__(' 1', 'powerlegal'), esc_html__(' % Comments', 'powerlegal'), $post->ID); ?></span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($show_category == 'true') : ?>
                                            <div class="post-categories d-flex align-items-center hover-underline">
                                                <span><?php echo esc_html('CATEGORIES:', 'powerlegal');?></span>
                                                <span class="post-category d-flex"><span><?php the_terms( $post->ID, 'category', '', ', ', '' ); ?></span></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <?php if($show_button == 'true') : ?>
                                <div class="item-readmore pxl-button-wrapper">
                                    <a class="btn btn-fullwidth" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                        <span><?php echo pxl_print_html($button_text);?></span>
                                        <i class="zmdi zmdi-long-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_grid_layout1($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if(!empty($args_m[$key]['thumbnail'])){
            $thumbnail = wp_specialchars_decode($args_m[$key]['thumbnail']);
        }else{
            if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
                $img_id = get_post_thumbnail_id($post->ID);
                if($img_id){
                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $img_id,
                        'thumb_size' => $img_size,
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                }else{
                    $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
                }
            }
        }

        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));


        $author = get_user_by('id', $post->post_author);
        $author_avatar = get_avatar( $post->post_author, 40, '', $author->display_name, array( 'class' => 'circle' ) );
        $comment_count = get_comments_number($post->ID);
        $button_text = !empty($button_text) ? $button_text : esc_html__('Read more', 'powerlegal');
        $date_formart = get_option('date_format');

        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }

        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];

        ?>
        <div class="<?php echo esc_attr($str_item_class. ' '. $animate_cls . ' '. $filter_class); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner">
                <?php if (isset( $thumbnail )): ?>
                    <div class="item-featured">
                        <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                    </div>
                <?php endif; ?>
                <div class="item-content">
                    <?php if($show_date == 'true' || $show_author == 'true' ) : ?>
                        <div class="item-post-meta">
                            <div class="meta-inner d-flex align-items-center">
                                <?php if( $show_author == 'true' ) : ?>
                                    <span class="post-author meta-item"><span class="avatar"><?php echo pxl_print_html($author_avatar) ?></span><a href="<?php echo esc_url(get_author_posts_url($post->post_author, $author->user_nicename)); ?>"><?php echo esc_html($author->display_name); ?></a></span>
                                <?php endif; ?>
                                <?php if( $show_date == 'true' ) : ?>
                                    <span class="post-date meta-item col-auto"><span class="pxl-icon pxli-calendar-alt-regular"></span><?php echo get_the_date($date_formart, $post->ID); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <h3 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h3>
                    <?php if($show_excerpt == 'true'): ?>
                        <div class="item-excerpt">
                            <?php
                                if(!empty($post->post_excerpt)){
                                    echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                } else{
                                    $content = strip_shortcodes( $post->post_content );
                                    $content = apply_filters( 'the_content', $content );
                                    $content = str_replace(']]>', ']]&gt;', $content);
                                    echo wp_trim_words( $content, $num_words, null );
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if($show_button == 'true') : ?>
                        <div class="item-readmore">
                            <a class="btn" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                <span><?php echo pxl_print_html($button_text);?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_pxl_case_study_list_layout1($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
            $img_id = get_post_thumbnail_id($post->ID);
            if($img_id){
                $img = pxl_get_image_by_size( array(
                    'attach_id'  => $img_id,
                    'thumb_size' => $img_size,
                    'class' => 'no-lazyload',
                ));
                $thumbnail = $img['thumbnail'];
            }else{
                $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
            }
        }
        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));
        $button_text = !empty($button_text) ? $button_text : esc_html__('Read more', 'powerlegal');
        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }
        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];
        $author = get_user_by('id', $post->post_author);
        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '. $filter_class); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner">
                <div class="row gx-lg-50 align-items-lg-center">
                    <?php
                    if (isset( $thumbnail )){
                        ?>
                        <div class="item-featured col-lg-6">
                            <div class="post-image scale-hover">
                                <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="item-content col-lg-6">
                        <?php
                        if ($show_tag == 'true'){
                            ?>
                            <div class="post-metas">
                                <div class="meta-inner d-flex align-items-center">
                                    <span class="post-tag">
                                        <?php the_terms( $post->ID, 'pxl-case-study-tag', '', ',&nbsp', '' ); ?>
                                    </span>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <h3 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h3>
                        <?php if($show_excerpt == 'true'): ?>
                            <div class="item-excerpt">
                                <?php
                                if(!empty($post->post_excerpt)){
                                    echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                } else{
                                    $content = strip_shortcodes( $post->post_content );
                                    $content = apply_filters( 'the_content', $content );
                                    $content = str_replace(']]>', ']]&gt;', $content);
                                    echo wp_trim_words( $content, $num_words, null );
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if($show_button == 'true') : ?>
                            <div class="item-readmore pxl-button-wrapper">
                                <a class="btn" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                    <span><?php echo pxl_print_html($button_text);?></span>
                                    <i class="zmdi zmdi-long-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    ?>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_grid_pxl_case_study1($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if(!empty($args_m[$key]['thumbnail'])){
            $thumbnail =  wp_specialchars_decode( $args_m[$key]['thumbnail']);
        }else{
            if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
                $img_id = get_post_thumbnail_id($post->ID);
                if($img_id){
                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $img_id,
                        'thumb_size' => $img_size,
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                }else{
                    $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
                }
            }
        }

        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));

        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }

        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];

        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '.$filter_class ); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner square-box">
                <div class="inner-box">
                    <?php if (isset( $thumbnail )): ?>
                        <div class="item-featured scale-hover-x">
                            <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                        </div>
                    <?php endif; ?>
                    <div class="item-content">
                        <div class="content-inner">
                            <h4 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h4>
                            <?php if($show_excerpt == 'true'): ?>
                                <div class="item-excerpt">
                                    <?php
                                    if(!empty($post->post_excerpt)){
                                        echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                    } else{
                                        $content = strip_shortcodes( $post->post_content );
                                        $content = apply_filters( 'the_content', $content );
                                        $content = str_replace(']]>', ']]&gt;', $content);
                                        echo wp_trim_words( $content, $num_words, null );
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if($show_button == 'true') : ?>
                                <div class="item-readmore pxl-button-wrapper">
                                    <a class="btn-more" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                        <span><?php echo pxl_print_html($button_text);?></span>
                                        <i class="zmdi zmdi-long-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_grid_pxl_case_study2($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):

        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;

        if(!empty($args_m[$key]['thumbnail'])){
            $thumbnail =  wp_specialchars_decode( $args_m[$key]['thumbnail']);
        }else{
            if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
                $img_id = get_post_thumbnail_id($post->ID);
                if($img_id){
                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $img_id,
                        'thumb_size' => $img_size,
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                }else{
                    $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
                }
            }
        }

        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));

        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }

        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];

        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '.$filter_class ); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner cross-hover">
                <?php if (isset( $thumbnail )): ?>
                    <div class="item-featured">
                        <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                    </div>
                <?php endif; ?>
                <div class="item-content">
                    <div class="content-inner">
                        <h4 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h4>
                        <?php
                        if ($show_category == 'true'){
                            ?>
                            <div class="item-category">
                                <?php the_terms( $post->ID, 'pxl-case-study-category', '', ',&nbsp', '' ); ?>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if($show_excerpt == 'true'): ?>
                            <div class="item-excerpt">
                                <?php
                                if(!empty($post->post_excerpt)){
                                    echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                } else{
                                    $content = strip_shortcodes( $post->post_content );
                                    $content = apply_filters( 'the_content', $content );
                                    $content = str_replace(']]>', ']]&gt;', $content);
                                    echo wp_trim_words( $content, $num_words, null );
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if($show_button == 'true') : ?>
                            <div class="item-readmore pxl-button-wrapper">
                                <a class="btn-icon" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                    <i class="zmdi zmdi-long-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_grid_pxl_practice_area1($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):
        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;
        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));

        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }
        $icon_type = get_post_meta($post->ID, 'area_icon_type',true);
        $area_icon = get_post_meta($post->ID, 'area_icon',true);
        $area_img = get_post_meta($post->ID, 'area_img',true);
        $area_img_alt = get_post_meta( $area_img['id'], '_wp_attachment_image_alt', true);
        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];
        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '.$filter_class ); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner cross-hover">
                <div class="item-content">
                    <div class="content-inner">
                        <h4 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h4>
                        <?php
                        if (!empty($area_img['url']) && $icon_type == 'image'){
                            ?>
                            <div class="area-icon-wrap">
                                <img class="area-icon" src="<?php echo esc_url($area_img['url']); ?>" alt="<?php echo esc_attr($area_img_alt); ?>">
                            </div>
                            <?php
                        }
                        if (!empty($area_img['url']) && $icon_type == 'icon'){
                            ?>
                            <div class="area-icon-wrap">
                                <div class="area-icon"><i class="<?php echo esc_attr($area_icon); ?>"></i></div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if($show_excerpt == 'true'): ?>
                            <div class="item-excerpt">
                                <?php
                                if(!empty($post->post_excerpt)){
                                    echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                } else{
                                    $content = strip_shortcodes( $post->post_content );
                                    $content = apply_filters( 'the_content', $content );
                                    $content = str_replace(']]>', ']]&gt;', $content);
                                    echo wp_trim_words( $content, $num_words, null );
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if($show_button == 'true') : ?>
                            <div class="item-readmore pxl-button-wrapper">
                                <a class="btn-more" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                    <span><?php echo pxl_print_html($button_text);?></span>
                                    <i class="zmdi zmdi-long-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}

function powerlegal_get_post_grid_pxl_practice_area2($posts = [], $settings = [], $args_m = []){
    extract($settings);
    foreach ($posts as $key => $post):
        $str_item_class = !empty($args_m[$key]['item_class']) ? $args_m[$key]['item_class'] : $item_class;
        if(!empty($args_m[$key]['thumbnail'])){
            $thumbnail =  wp_specialchars_decode( $args_m[$key]['thumbnail']);
        }else{
            if (has_post_thumbnail($post->ID) && wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), false)){
                $img_id = get_post_thumbnail_id($post->ID);
                if($img_id){
                    $img = pxl_get_image_by_size( array(
                        'attach_id'  => $img_id,
                        'thumb_size' => $img_size,
                        'class' => 'no-lazyload',
                    ));
                    $thumbnail = $img['thumbnail'];
                }else{
                    $thumbnail = get_the_post_thumbnail($post->ID, $img_size);
                }
            }
        }
        $filter_class = '';
        if ($select_post_by === 'term_selected' && $filter == "true")
            $filter_class = pxl_get_term_of_post_to_class($post->ID, array_unique($tax));

        $increase = $key + 1;
        $data_settings = '';
        $animate_cls = '';
        if ( !empty( $item_animation ) ) {
            $animate_cls = ' pxl-animate pxl-invisible animated-'.$item_animation_duration;
            $data_animation =  json_encode([
                'animation'      => $item_animation,
                'animation_delay' => ((float)$item_animation_delay * $increase)
            ]);
            $data_settings = 'data-settings="'.esc_attr($data_animation).'"';
        }
        $icon_type = get_post_meta($post->ID, 'area_icon_type',true);
        $area_icon = get_post_meta($post->ID, 'area_icon',true);
        $area_img = get_post_meta($post->ID, 'area_img',true);
        $area_img_alt = get_post_meta( $area_img['id'], '_wp_attachment_image_alt', true);
        if(!empty($args_m[$key]['data_setting']))
            $data_settings = $args_m[$key]['data_setting'];
        ?>
        <div class="<?php echo esc_attr($str_item_class. ' ' .$animate_cls. ' '.$filter_class ); ?>" <?php pxl_print_html($data_settings); ?>>
            <div class="grid-item-inner">
                <?php if (isset( $thumbnail )): ?>
                    <div class="item-featured">
                        <a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo wp_kses_post($thumbnail); ?></a>
                        <?php
                        if (!empty($area_img['url']) && $icon_type == 'image'){
                            ?>
                            <div class="area-icon-wrap">
                                <img class="area-icon" src="<?php echo esc_url($area_img['url']); ?>" alt="<?php echo esc_attr($area_img_alt); ?>">
                            </div>
                            <?php
                        }
                        if (!empty($area_img['url']) && $icon_type == 'icon'){
                            ?>
                            <div class="area-icon-wrap">
                                <div class="area-icon"><i class="<?php echo esc_attr($area_icon); ?>"></i></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div class="item-content">
                    <div class="content-inner">
                        <h4 class="item-title"><a href="<?php echo esc_url(get_permalink( $post->ID )); ?>"><?php echo esc_attr(get_the_title($post->ID)); ?></a></h4>
                        <?php if($show_excerpt == 'true'): ?>
                            <div class="item-excerpt">
                                <?php
                                if(!empty($post->post_excerpt)){
                                    echo wp_trim_words( $post->post_excerpt, $num_words, null );
                                } else{
                                    $content = strip_shortcodes( $post->post_content );
                                    $content = apply_filters( 'the_content', $content );
                                    $content = str_replace(']]>', ']]&gt;', $content);
                                    echo wp_trim_words( $content, $num_words, null );
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <?php if($show_button == 'true') : ?>
                            <div class="item-readmore pxl-button-wrapper">
                                <a class="btn-more" href="<?php echo esc_url(get_permalink( $post->ID )); ?>">
                                    <span><?php echo pxl_print_html($button_text);?></span>
                                    <i class="zmdi zmdi-long-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endforeach;
}