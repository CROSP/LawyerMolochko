<?php
/**
 * Template part for displaying default header layout
 */

$logo_url = get_template_directory_uri().'/assets/images/logo.png';
$p_menu = powerlegal()->get_page_opt('p_menu');
 
$header_css_cls = powerlegal()->header->get_header_css_class(['class' => '']);
 
?>
 
<header id="pxl-header" class="<?php echo esc_attr($header_css_cls); ?>">
    <div class="header-container">
        <div class="row justify-content-between align-items-center gutters-40">
            <div class="pxl-header-logo col-auto">
                <?php 
                printf(
                    '<a class="logo-default" href="%1$s" title="%2$s" rel="home"><img class="pxl-logo" src="%3$s" alt="%2$s"/></a>',
                    esc_url( home_url( '/' ) ),
                    esc_attr( get_bloginfo( 'name' ) ),
                    esc_url( $logo_url )
                );
                ?>
            </div>
            <div class="pxl-navigation col-auto d-none d-xl-block">
                <div class="row align-items-center justify-content-between">
                    <div class="col-12 col-xl-auto">
                        <div class="row align-items-center">
                            <div class="pxl-main-navigation col-12 col-xl-auto">
                                <?php 
                                if ( has_nav_menu( 'primary' ) ){
                                    $attr_menu = array(
                                        'theme_location' => 'primary',
                                        'container'  => '',
                                        'menu_id'    => 'pxl-primary-menu',
                                        'menu_class' => 'pxl-primary-menu clearfix',
                                        'link_before'     => '<span>',
                                        'link_after'      => '</span>',
                                        'walker'         => class_exists( 'PXL_Mega_Menu_Walker' ) ? new PXL_Mega_Menu_Walker : '',
                                    );
                                    if(isset($p_menu) && !empty($p_menu)) {
                                        $attr_menu['menu'] = $p_menu;
                                    }

                                    wp_nav_menu( $attr_menu );
                                }else{
                                    printf(
                                        '<ul class="pxl-primary-menu primary-menu-not-set"><li><a href="%1$s">%2$s</a></li></ul>',
                                        esc_url( admin_url( 'nav-menus.php' ) ),
                                        esc_html__( 'Create New Menu', 'powerlegal' )
                                    );
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
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
</header>
