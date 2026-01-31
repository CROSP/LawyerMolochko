<?php
/**
 * @package Molochko
 */
?>
</div><!-- #pxl-main -->

<?php get_template_part( 'template-parts/footer' ); ?>

<?php /* Mobile side panel */ ?>
<nav class="pxl-hidden-template pos-left pxl-side-mobile" aria-hidden="true">
	<div class="pxl-panel-header">
		<div class="panel-header-inner">
			<a href="#" class="pxl-close" data-target=".pxl-side-mobile" title="<?php esc_attr_e( 'Close', 'molochko' ); ?>"></a>
		</div>
	</div>
	<div class="pxl-panel-content">
		<div class="menu-main-container-wrap">
			<div id="mobile-menu-container" class="menu-main-container">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => '',
						'menu_id'        => 'pxl-mobile-menu',
						'menu_class'     => 'pxl-mobile-menu clearfix',
						'link_before'    => '<span class="pxl-menu-title">',
						'link_after'     => '</span>',
					) );
				} else {
					printf(
						'<ul class="pxl-mobile-menu primary-menu-not-set"><li><a href="%1$s">%2$s</a></li></ul>',
						esc_url( admin_url( 'nav-menus.php' ) ),
						esc_html__( 'Create New Menu', 'molochko' )
					);
				}
				?>
			</div>
		</div>
	</div>
</nav>

<?php /* Back to top */ ?>
<a href="#pxl-page" class="pxl-scroll-top" aria-label="<?php esc_attr_e( 'Back to top', 'molochko' ); ?>"><i class="zmdi zmdi-long-arrow-up"></i></a>

</div><!-- #pxl-page -->
<?php wp_footer(); ?>
</body>
</html>
