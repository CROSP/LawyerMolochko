<?php
/**
 * Contact Form 7 integration: get shortcode for contact page and consultation popup.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Contact Form 7 shortcode for contact/consultation forms.
 * Prefers form titled "Замовити консультацію", else form ID from filter, else first form.
 *
 * @return string Shortcode string e.g. [contact-form-7 id="123" title="Consultation"] or empty.
 */
function molochko_get_contact_form_shortcode() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return '';
	}
	$form_id = (int) apply_filters( 'molochko_contact_form_id', 0 );
	if ( $form_id > 0 ) {
		$form = WPCF7_ContactForm::get_instance( $form_id );
		if ( $form ) {
			return $form->shortcode();
		}
	}
	$forms = WPCF7_ContactForm::find( array( 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	if ( empty( $forms ) ) {
		return '';
	}
	$consult_title = 'Замовити консультацію';
	foreach ( $forms as $form ) {
		if ( $form->title() === $consult_title ) {
			return $form->shortcode();
		}
	}
	return $forms[0]->shortcode();
}
