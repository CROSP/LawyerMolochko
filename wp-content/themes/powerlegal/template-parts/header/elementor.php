<?php 
 
$logo = powerlegal()->get_theme_opt( 'logo_m', ['url' => get_template_directory_uri().'/assets/images/logo.png', 'id' => '' ] );
$p_menu = powerlegal()->get_page_opt('p_menu');
$header_css_cls = powerlegal()->header->get_header_css_class(['class' => '']);
?>

<header id="pxl-header" class="<?php echo esc_attr($header_css_cls); ?>">
	<?php if(isset($args['header_layout']) && $args['header_layout'] > 0) : ?>
        <div class="pxl-header-main d-none d-xl-block">
            <?php echo Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $args['header_layout']); ?>
        </div>
        
	<?php endif; ?>
    <?php if(isset($args['header_sticky_layout']) && $args['header_sticky_layout'] > 0) : ?>
        <div class="pxl-header-sticky d-none d-xl-block">
            <?php echo Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $args['header_sticky_layout']); ?>  
        </div>
    <?php endif; ?>
    
    <?php if(isset($args['header_mobile_layout']) && $args['header_mobile_layout'] > 0) : ?>
        <div class="pxl-header-mobile pxl-header-mobile-sticky d-xl-none">
            <?php echo Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $args['header_mobile_layout']); ?>         
        </div> 
    <?php else: ?>    
        <div class="pxl-header-mobile container d-xl-none">
            <div class="row justify-content-between align-items-center gx-40">
                <div class="pxl-header-logo col-auto">
                    <?php 
                    printf(
                        '<a class="logo-default" href="%1$s" title="%2$s" rel="home"><img class="pxl-logo" src="%3$s" alt="%2$s"/></a>',
                        esc_url( home_url( '/' ) ),
                        esc_attr( get_bloginfo( 'name' ) ),
                        esc_url( $logo['url'] )
                    );
                    ?>
                </div>
                <div class="col col-auto d-xl-none">
                    <div class="row align-items-center justify-content-end">
                        <div id="main-menu-mobile" class="main-menu-mobile">
                            <span class="btn-nav-mobile open-menu" data-target=".pxl-side-mobile" onclick="">
                                <span></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>    
</header>