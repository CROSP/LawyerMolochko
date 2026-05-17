<?php
/**
 * Front page contact form section (id="contact"). Dark CTA strip + light form card.
 *
 * @package Molochko
 * @var string $cf7_shortcode Contact Form 7 shortcode or empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
$cf7_shortcode = isset( $args['cf7_shortcode'] ) ? $args['cf7_shortcode'] : '';
?>
<section id="contact" class="molochko-front-contact">
	<div class="molochko-front-contact-bg" aria-hidden="true"></div>
	<div class="molochko-front-contact-inner">
		<header class="molochko-front-contact-header">
			<p class="molochko-front-contact-subtitle"><?php molochko_pll_esc_html_e( 'Зв’яжіться з нами' ); ?></p>
			<h2 class="molochko-front-contact-title"><?php molochko_pll_esc_html_e( 'Отримайте безкоштовну консультацію' ); ?></h2>
			<p class="molochko-front-contact-desc"><?php molochko_pll_esc_html_e( 'Опишіть ситуацію в кількох словах — передзвонимо протягом робочого дня та оцінимо перспективи справи. Конфіденційно, без зобов\'язань.' ); ?></p>
		</header>
		<div class="molochko-front-contact-form-card">
			<?php if ( $cf7_shortcode ) : ?>
				<div class="molochko-front-contact-form-inner">
					<?php echo do_shortcode( $cf7_shortcode ); ?>
				</div>
			<?php else : ?>
				<p class="molochko-front-contact-form-missing">
					<?php molochko_pll_esc_html_e( 'Створіть форму в Контакт → Контактні форми (Contact Form 7) і переконайтесь, що хоча б одна форма опублікована.' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
</section>
