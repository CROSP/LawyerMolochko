<?php
/**
 * Template part: Header (no Elementor)
 * Logo: theme assets/images/logo.svg (your Logo_blue). Customizer logo is ignored
 * so the previous theme’s logo does not show. To use Customizer again, restore the
 * has_custom_logo() branch below.
 */
$logo_url = MOLOCHKO_URI . '/assets/images/logo.svg';
?>
<header id="pxl-header" class="pxl-header header-type-df header-layout-0">
	<div class="header-container">
		<div class="row justify-content-between align-items-center">
			<div class="pxl-header-logo col-auto">
				<a class="logo-default" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" rel="home">
					<img class="pxl-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
			</div>
			<div class="pxl-navigation col-auto d-none d-xl-block">
				<div class="pxl-main-navigation">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'container'      => '',
							'menu_id'        => 'pxl-primary-menu',
							'menu_class'     => 'pxl-primary-menu clearfix',
							'link_before'    => '<span>',
							'link_after'     => '</span>',
						) );
					} else {
						printf(
							'<ul class="pxl-primary-menu primary-menu-not-set"><li><a href="%1$s">%2$s</a></li></ul>',
							esc_url( admin_url( 'nav-menus.php' ) ),
							esc_html__( 'Create New Menu', 'molochko' )
						);
					}
					?>
				</div>
			</div>
			<div class="col-auto d-xl-none">
				<div id="main-menu-mobile" class="main-menu-mobile">
					<span class="btn-nav-mobile open-menu" data-target=".pxl-side-mobile" aria-label="<?php esc_attr_e( 'Menu', 'molochko' ); ?>">
						<span></span>
					</span>
				</div>
			</div>
		</div>
	</div>
</header>
