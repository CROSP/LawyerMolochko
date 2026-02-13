<?php
/**
 * Consultation popup modal with Contact Form 7.
 * Opened by .js-consultation-popup links (e.g. "Замовити консультацію").
 *
 * @package Molochko
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cf7_shortcode = function_exists( 'molochko_get_contact_form_shortcode' ) ? molochko_get_contact_form_shortcode() : '';
?>
<div id="consultation-popup" class="molochko-consultation-popup is-closed" role="dialog" aria-modal="true" aria-labelledby="consultation-popup-title" aria-hidden="true">
	<div class="molochko-consultation-popup-backdrop" data-close="consultation"></div>
	<div class="molochko-consultation-popup-dialog" tabindex="-1">
		<div class="molochko-consultation-popup-content">
			<button type="button" class="molochko-consultation-popup-close" data-close="consultation" aria-label="<?php esc_attr_e( 'Закрити', 'molochko' ); ?>">
				<i class="zmdi zmdi-close" aria-hidden="true"></i>
			</button>
			<h2 id="consultation-popup-title" class="molochko-consultation-popup-title"><?php esc_html_e( 'Замовити консультацію', 'molochko' ); ?></h2>
			<p class="molochko-consultation-popup-desc"><?php esc_html_e( 'Залиште заявку — ми передзвонимо або напишемо вам найближчим часом.', 'molochko' ); ?></p>
			<?php if ( $cf7_shortcode ) : ?>
				<div class="molochko-consultation-popup-form">
					<?php echo do_shortcode( $cf7_shortcode ); ?>
				</div>
			<?php else : ?>
				<p class="molochko-consultation-popup-missing"><?php esc_html_e( 'Створіть форму в Контакт → Контактні форми (Contact Form 7).', 'molochko' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
