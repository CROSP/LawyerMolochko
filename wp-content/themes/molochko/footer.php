<?php
/**
 * @package Molochko
 */
?>
</div><!-- #pxl-main -->

<?php get_template_part( 'template-parts/footer' ); ?>

<?php /* Consultation popup (Contact Form 7) */ ?>
<?php get_template_part( 'template-parts/consultation-popup' ); ?>

<?php /* Mobile full-screen menu with phone */ ?>
<?php
$mobile_phone   = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_phone' ) : '';
$mobile_phone   = $mobile_phone ?: '+38 (050) 606-00-79';
$mobile_tel     = 'tel:' . preg_replace( '/\D+/', '', $mobile_phone );
$mobile_consult = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_consultation_url' ) : '';
$mobile_consult = $mobile_consult ?: '#consultation-popup';
?>
<nav class="pxl-hidden-template pos-left pxl-side-mobile" role="dialog" aria-modal="true" aria-label="<?php echo molochko_pll_esc_attr__( 'Головне меню' ); ?>" aria-hidden="true">
	<div class="pxl-panel-header pxl-mobile-panel-header">
		<div class="panel-header-inner">
			<button type="button" class="pxl-close pxl-mobile-close" data-target=".pxl-side-mobile" aria-label="<?php echo molochko_pll_esc_attr__( 'Закрити меню' ); ?>"></button>
		</div>
	</div>
	<div class="pxl-panel-content pxl-mobile-panel-content">
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
		<div class="pxl-mobile-menu-footer">
			<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
				<div class="pxl-mobile-lang-switcher" aria-label="<?php echo molochko_pll_esc_attr__( 'Мова' ); ?>">
					<?php echo molochko_pll_current_flag(); ?>
					<?php
					pll_the_languages( array(
						'echo'                   => 1,
						'dropdown'               => 'mobile',
						'show_flags'             => 0,
						'show_names'             => 1,
						'hide_current'           => 0,
						'hide_if_no_translation' => 0,
					) );
					?>
				</div>
			<?php endif; ?>
			<a href="<?php echo esc_attr( $mobile_tel ); ?>" class="pxl-mobile-phone" rel="nofollow">
				<span class="pxl-mobile-phone-icon"><i class="flaticon flaticon-phone-call" aria-hidden="true"></i></span>
				<span class="pxl-mobile-phone-number"><?php echo esc_html( $mobile_phone ); ?></span>
			</a>
			<a href="<?php echo esc_url( $mobile_consult ); ?>" class="pxl-mobile-consult-btn js-consultation-popup" data-popup="consultation">
				<?php echo molochko_pll_esc_html__( 'Замовити консультацію' ); ?>
			</a>
		</div>
	</div>
</nav>

<?php /* Back to top */ ?>
<a href="#pxl-page" class="pxl-scroll-top" aria-label="<?php echo molochko_pll_esc_attr__( 'Back to top' ); ?>"><i class="zmdi zmdi-long-arrow-up"></i></a>

</div><!-- #pxl-page -->
<?php wp_footer(); ?>
</body>
</html>
