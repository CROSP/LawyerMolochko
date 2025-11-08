<?php
if (!class_exists('Powerlegal_Page_Title')) {
    class Powerlegal_Page_Title
    {
        public function get_page_title(){
            $pt_mode = powerlegal()->get_opt('pt_mode');
            if( $pt_mode == 'none' ) return;

            $ptitle_layout = (int)powerlegal()->get_opt('ptitle_layout');
            $titles = $this->get_title();

            $breadcrumb_on = powerlegal()->get_opt( 'breadcrumb_on', false );
            $breadcrumb = new Powerlegal_Breadcrumb();
            $entries = $breadcrumb->get_entries();
 
            $brc_divider = '<span class="br-divider pxli pxli-long-arrow-right rtl-flip"></span>';

            if(class_exists('WooCommerce')) {
                if (is_shop() || is_singular('product')) {
                    $shop_id = get_option('woocommerce_shop_page_id', '');
                    $shop_ptitle_layout = get_post_meta($shop_id, 'ptitle_layout', true);
                    $shop_pt_mode = get_post_meta($shop_id, 'pt_mode', true);
                    if (!empty($shop_ptitle_layout)){
                        $ptitle_layout = $shop_ptitle_layout;
                        $pt_mode = $shop_pt_mode;
                    }
                    $bg_image = "";
                    if (!empty(get_post_meta($shop_id, 'ptitle_bg', true)['background-image'])){
                        $bg_image = get_post_meta($shop_id, 'ptitle_bg', true)['background-image'];
                    }
                }
            }
            if ($pt_mode == 'bd' && $ptitle_layout > 0 && class_exists('Pxltheme_Core') && is_callable( 'Elementor\Plugin::instance' )) {
                ?>
                <div id="pxl-pagetitle" style="<?php if(!empty($bg_image)){?>background-image:url('<?php echo esc_url($bg_image); ?>');<?php } ?>" class="pxl-pagetitle bg-image  layout-el relative">
                    <?php echo Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $ptitle_layout);?>
                </div>
                <?php 
            } else {
                ?>
                <div id="pxl-pagetitle" style="<?php if(!empty($bg_image)){?>background-image:url('<?php echo esc_url($bg_image); ?>');<?php } ?>" class="pxl-pagetitle bg-image layout-df relative">
                    <div class="pxl-page-title-overlay"></div>
                    <div class="container relative">
                        <div class="pxl-page-title-inner text-center">
                            <div class="pxl-page-title col-12">
                                <h1 class="main-title"><?php pxl_print_html($titles['title']) ?></h1>
                            </div>
                            <?php if ( !empty( $entries ) && $breadcrumb_on ): ?>
                                <div class="pxl-breadcrumb hover-underline">
                                    <?php 
                                        foreach ( $entries as $entry ){
                                            $entry = wp_parse_args( $entry, array(
                                                'label' => '',
                                                'url'   => ''
                                            ) );

                                            if ( empty( $entry['label'] ) ){
                                                continue;
                                            }

                                            echo '<div class="br-item">';
                                            if ( ! empty( $entry['url'] ) ){
                                                printf(
                                                    '<a class="br-link" href="%1$s">%2$s</a>%3$s',
                                                    esc_url( $entry['url'] ),
                                                    esc_attr( $entry['label'] ),
                                                    $brc_divider
                                                );           
                                            }else{
                                                printf( '<span class="br-text" >%s</span>%2$s', $entry['label'], $brc_divider );
                                            }
                                            echo '</div>';
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
            } 
        } 
        
        public function get_title() {
            $title = '';
            $single_post_title_layout = powerlegal()->get_theme_opt('single_post_title_layout','0');
            $post_custom_title  = powerlegal()->get_theme_opt('post_custom_title',esc_html__('Blog details', 'powerlegal'));
            // Default titles
            if ( ! is_archive() ) {
                // Posts page view
                if ( is_home() ) {
                    // Only available if posts page is set.
                    if ( ! is_front_page() && $page_for_posts = get_option( 'page_for_posts' ) ) {
                        $title = get_post_meta( $page_for_posts, 'custom_title', true );
                        if ( empty( $title ) ) {
                            $title = get_the_title( $page_for_posts );
                        }
                    }
                    if ( is_front_page() ) {
                        $title = esc_html__( 'Blog', 'powerlegal' );
                    }
                } // Single page view
                elseif ( is_page() ) {
                    $title = get_post_meta( get_the_ID(), 'custom_title', true );
                    if ( ! $title ) {
                        $title = get_the_title();
                    }
                } elseif ( is_404() ) {
                    $title = esc_html__( '404', 'powerlegal' );
                } elseif ( is_search() ) {
                    $title = esc_html__( 'Search results', 'powerlegal' );
                } else {
                    $title = get_post_meta( get_the_ID(), 'custom_title', true );
                    if( is_singular('post') && $single_post_title_layout == '1'){
                        $title = $post_custom_title; 
                    } elseif ( ! $title ) {
                        $title = get_the_title();
                    } else {
                        $title = $title; //get_the_title();
                    }
                }
            } elseif ( is_author() ) {  
                $title     = get_the_author();
            } else {
                $custom_title = powerlegal()->get_opt('custom_title');
                $_title = get_the_archive_title();
                if( class_exists( 'WooCommerce' ) && is_shop() ) {
                    $_title = get_post_meta( wc_get_page_id('shop'), 'custom_title', true );
                    if(!$_title) {
                        $_title = get_the_title( get_option( 'woocommerce_shop_page_id' ) );
                    }
                }
                $title = !empty($custom_title) ? $custom_title : $_title;
            }
            //* Custom main and sub title
            $main_title = powerlegal()->get_page_opt('custom_main_title');
            if (!empty($main_title)){
                $title = $main_title;
            }
            $sub_title = powerlegal()->get_opt('custom_sub_title');
            $page_sub_title = powerlegal()->get_page_opt('custom_sub_title');
            if (!empty($page_sub_title)){
                $sub_title = $page_sub_title;
            }
            return array(
                'title' => $title,
                'sub_title' => $sub_title
            );
        }
    }
     
}
 
