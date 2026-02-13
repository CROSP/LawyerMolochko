<?php
/**
 * Template Name: Contact Us
 * Contact page: hero, contact info, map placeholder, Contact Form 7.
 *
 * @package Molochko
 */
get_header();

$phone        = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_phone' ) : '';
$email        = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_email' ) : '';
$address      = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_address' ) : '';
$address_url  = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_address_url' ) : '';
$working_hours = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_working_hours' ) : __( 'Пн–Пт: 9:00 – 18:00', 'molochko' );
$cf7_shortcode = function_exists( 'molochko_get_contact_form_shortcode' ) ? molochko_get_contact_form_shortcode() : '';
$tel_href     = $phone ? 'tel:' . preg_replace( '/\D+/', '', $phone ) : '';
?>
<div class="molochko-contact-page">
	<!-- Hero -->
	<section class="molochko-contact-hero">
		<div class="molochko-contact-hero-inner">
			<p class="molochko-contact-hero-subtitle"><?php esc_html_e( 'Зв’яжіться з нами', 'molochko' ); ?></p>
			<h1 class="molochko-contact-hero-title"><?php esc_html_e( 'Контакти', 'molochko' ); ?></h1>
			<p class="molochko-contact-hero-desc"><?php esc_html_e( 'Маєте питання або потрібна консультація? Напишіть нам або зателефонуйте — ми відповімо якомога швидше.', 'molochko' ); ?></p>
		</div>
	</section>

	<!-- Contact content: info + form -->
	<section class="molochko-contact-content">
		<div class="molochko-contact-container">
			<div class="molochko-contact-grid">
				<!-- Left: contact info -->
				<div class="molochko-contact-info">
					<h2 class="molochko-contact-info-title"><?php esc_html_e( 'Контактна інформація', 'molochko' ); ?></h2>
					<ul class="molochko-contact-info-list">
						<?php if ( $phone ) : ?>
							<li class="molochko-contact-info-item">
								<span class="molochko-contact-info-icon" aria-hidden="true"><i class="zmdi zmdi-phone"></i></span>
								<div>
									<span class="molochko-contact-info-label"><?php esc_html_e( 'Телефон', 'molochko' ); ?></span>
									<a href="<?php echo esc_attr( $tel_href ); ?>" rel="nofollow"><?php echo esc_html( $phone ); ?></a>
								</div>
							</li>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<li class="molochko-contact-info-item">
								<span class="molochko-contact-info-icon" aria-hidden="true"><i class="zmdi zmdi-email"></i></span>
								<div>
									<span class="molochko-contact-info-label"><?php esc_html_e( 'Email', 'molochko' ); ?></span>
									<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
								</div>
							</li>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<li class="molochko-contact-info-item">
								<span class="molochko-contact-info-icon" aria-hidden="true"><i class="zmdi zmdi-pin"></i></span>
								<div>
									<span class="molochko-contact-info-label"><?php esc_html_e( 'Адреса', 'molochko' ); ?></span>
									<?php if ( $address_url ) : ?>
										<a href="<?php echo esc_url( $address_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $address ); ?></a>
									<?php else : ?>
										<span><?php echo esc_html( $address ); ?></span>
									<?php endif; ?>
								</div>
							</li>
						<?php endif; ?>
						<?php if ( $working_hours ) : ?>
							<li class="molochko-contact-info-item">
								<span class="molochko-contact-info-icon" aria-hidden="true"><i class="zmdi zmdi-time"></i></span>
								<div>
									<span class="molochko-contact-info-label"><?php esc_html_e( 'Графік роботи', 'molochko' ); ?></span>
									<span><?php echo esc_html( $working_hours ); ?></span>
								</div>
							</li>
						<?php endif; ?>
					</ul>
					<div class="molochko-contact-cta">
						<p><?php esc_html_e( 'Або натисніть кнопку «Замовити консультацію» у шапці сайту — відкриється форма для швидкого запиту.', 'molochko' ); ?></p>
					</div>
				</div>

				<!-- Right: form -->
				<div class="molochko-contact-form-wrap">
					<div class="molochko-contact-form-box">
						<h2 class="molochko-contact-form-title"><?php esc_html_e( 'Напишіть нам', 'molochko' ); ?></h2>
						<?php if ( $cf7_shortcode ) : ?>
							<div class="molochko-contact-form-inner">
								<?php echo do_shortcode( $cf7_shortcode ); ?>
							</div>
						<?php else : ?>
							<p class="molochko-contact-form-missing">
								<?php esc_html_e( 'Створіть форму в Контакт → Контактні форми (Contact Form 7) і переконайтесь, що хоча б одна форма опублікована.', 'molochko' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
<?php
get_footer();
