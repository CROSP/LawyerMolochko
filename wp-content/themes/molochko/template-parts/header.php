<?php
/**
 * Template part: Header (Power Legal–style)
 * Data from ACF Options page "Site Contact Details" (phone_number, email, working_hours, etc.).
 */
$logo_url         = MOLOCHKO_URI . '/assets/images/logo.svg';
$phone            = molochko_get_contact_option( 'header_phone' ) ?: '+38 (050) 606-00-79';
$tel_href         = 'tel:' . preg_replace( '/\D+/', '', $phone );
$email            = molochko_get_contact_option( 'header_email' );
$address          = molochko_get_contact_option( 'header_address' ) ?: '';
$address_url      = molochko_get_contact_option( 'header_address_url' );
$working_hours    = molochko_get_contact_option( 'header_working_hours' ) ?: __( 'Пн–Пт: 9:00 – 18:00', 'molochko' );
$consultation_url = molochko_get_contact_option( 'header_consultation_url' ) ?: '#consultation-popup';
?>
<header id="pxl-header" class="pxl-header header-type-el header-layout-35">
	<!-- Top bar: email, address, hours + consultation button (like Power Legal) -->
	<section class="header-top-section elementor-section-boxed elementor-section-height-default d-none d-xl-block">
		<div class="header-container elementor-container elementor-column-gap-default">
			<div class="elementor-column elementor-col-100">
				<div class="header-top-inner d-flex justify-content-between align-items-center flex-wrap">
					<ul class="header-top-list elementor-icon-list-items elementor-inline-items">
						<li class="header-top-item elementor-icon-list-item elementor-inline-item">
							<?php if ( $email ) : ?>
								<a href="mailto:<?php echo esc_attr( $email ); ?>" rel="nofollow">
									<span class="elementor-icon-list-icon"><i class="flaticon flaticon-mail" aria-hidden="true"></i></span>
									<span class="elementor-icon-list-text"><?php echo esc_html( $email ); ?></span>
								</a>
							<?php else : ?>
								<span class="elementor-icon-list-icon"><i class="flaticon flaticon-mail" aria-hidden="true"></i></span>
								<span class="elementor-icon-list-text"><?php echo esc_html( __( '—', 'molochko' ) ); ?></span>
							<?php endif; ?>
						</li>
						<li class="header-top-item elementor-icon-list-item elementor-inline-item">
							<?php if ( $address_url ) : ?>
								<a href="<?php echo esc_url( $address_url ); ?>" target="_blank" rel="nofollow">
									<span class="elementor-icon-list-icon"><i class="flaticon flaticon-address" aria-hidden="true"></i></span>
									<span class="elementor-icon-list-text"><?php echo esc_html( $address ?: __( 'Адреса офісу', 'molochko' ) ); ?></span>
								</a>
							<?php else : ?>
								<span class="elementor-icon-list-icon"><i class="flaticon flaticon-address" aria-hidden="true"></i></span>
								<span class="elementor-icon-list-text"><?php echo esc_html( $address ?: __( 'Адреса офісу', 'molochko' ) ); ?></span>
							<?php endif; ?>
						</li>
						<li class="header-top-item header-top-hours elementor-icon-list-item elementor-inline-item">
							<span class="elementor-icon-list-icon"><i class="flaticon flaticon-calendar" aria-hidden="true"></i></span>
							<span class="elementor-icon-list-text"><?php echo esc_html( $working_hours ); ?></span>
						</li>
					</ul>
					<div class="header-consultation-wrap pxl-button-wrapper">
						<a href="<?php echo esc_url( $consultation_url ); ?>" class="header-consultation-btn btn btn-default icon-ps-left js-consultation-popup" data-popup="consultation">
							<span class="pxl-button-text"><?php echo esc_html( __( 'Замовити консультацію', 'molochko' ) ); ?></span>
							<span class="pxl-button-icon pxl-icon left zmdi zmdi-comment-more" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Main row: logo (left) | menu (centered) | phone icon + number (right) -->
	<section class="header-main-section elementor-section-boxed elementor-section-height-default">
		<div class="header-container elementor-container elementor-column-gap-default">
			<div class="header-main-row row align-items-center">
				<div class="pxl-header-logo col-3 elementor-column">
					<a class="logo-default" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" rel="home">
						<img class="pxl-logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy">
					</a>
				</div>
				<div class="header-main-right col-9 col-xl-9 elementor-column d-flex align-items-center justify-content-between flex-wrap header-mobile-right">
					<nav class="pxl-main-navigation d-none d-xl-flex flex-grow-1 justify-content-center">
						<?php
						if ( has_nav_menu( 'primary' ) ) {
							wp_nav_menu( array(
								'theme_location' => 'primary',
								'container'      => '',
								'menu_id'        => 'pxl-primary-menu',
								'menu_class'     => 'pxl-primary-menu pxl-nav-menu-main style-2 is-arrow clearfix',
								'link_before'    => '<span class="pxl-menu-title">',
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
					</nav>
					<div class="header-right d-flex align-items-center ms-xl-0 ms-auto">
						<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
							<div class="pxl-header-lang-switcher d-none d-xl-flex" aria-label="<?php esc_attr_e( 'Мова', 'molochko' ); ?>">
								<?php echo molochko_pll_current_flag(); ?>
								<?php
								pll_the_languages( array(
									'echo'                   => 1,
									'dropdown'               => 'header',
									'show_flags'             => 0,
									'show_names'             => 1,
									'hide_current'           => 0,
									'hide_if_no_translation' => 0,
								) );
								?>
							</div>
						<?php endif; ?>
						<a class="header-phone d-none d-xl-flex align-items-center" href="<?php echo esc_attr( $tel_href ); ?>" rel="nofollow" aria-label="<?php echo esc_attr( __( 'Зателефонувати', 'molochko' ) ); ?>">
							<span class="header-phone-link elementor-icon">
								<i class="flaticon flaticon-phone-call" aria-hidden="true"></i>
							</span>
							<span class="header-phone-number heading-title"><?php echo esc_html( $phone ); ?></span>
						</a>
						<div class="main-menu-mobile d-xl-none">
							<button type="button" class="btn-nav-mobile open-menu" data-target=".pxl-side-mobile" aria-label="<?php echo esc_attr( __( 'Відкрити меню', 'molochko' ) ); ?>" aria-expanded="false" aria-controls="mobile-menu-container">
								<span></span><span></span><span></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</header>
