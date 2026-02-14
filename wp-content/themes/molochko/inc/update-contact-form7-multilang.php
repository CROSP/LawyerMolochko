<?php
/**
 * One-time: update existing Contact Form 7 "Замовити консультацію" to use
 * {translatable} placeholders for Multilingual Contact Form 7 with Polylang.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/update-contact-form7-multilang.php
 * (or: ddev exec wp eval-file wp-content/themes/molochko/inc/update-contact-form7-multilang.php)
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
	echo "Contact Form 7 plugin is not active.\n";
	exit( 1 );
}

$target_title = 'Замовити консультацію';
$forms = WPCF7_ContactForm::find( array( 'posts_per_page' => -1 ) );
$form = null;
foreach ( $forms as $f ) {
	if ( $f->title() === $target_title ) {
		$form = $f;
		break;
	}
}

if ( ! $form ) {
	echo "Form \"{$target_title}\" not found. Create it with: wp eval-file wp-content/themes/molochko/inc/seed-contact-form7.php\n";
	exit( 1 );
}

$new_form_template = '
<label> {Ваше ім\'я *}
    [text* your-name autocomplete:name placeholder "{Ім\'я}"] </label>

<label> {Телефон *}
    [text* your-phone placeholder "{Номер телефону}"] </label>

<label> {Тема (необов\'язково)}
    [text your-subject placeholder "{Коротко про ваше питання}"] </label>

<label> {Повідомлення *}
    [textarea* your-message placeholder "{Опишіть ситуацію або питання}"] </label>

[submit "{Надіслати заявку}"]';

$new_mail = array(
	'active'           => true,
	'subject'          => '[Молочко] {Нова заявка на консультацію}: [your-subject]',
	'sender'           => '[your-name] <wordpress@' . ( isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/[^a-z0-9.-]/', '', $_SERVER['HTTP_HOST'] ) : 'localhost' ) . '>',
	'body'             => "{Ім'я}: [your-name]\n{Телефон}: [your-phone]\n{Тема}: [your-subject]\n\n{Повідомлення}:\n[your-message]",
	'recipient'        => get_option( 'admin_email' ),
	'additional_headers' => '',
	'attachments'      => '',
	'use_html'         => false,
	'exclude_blank'    => false,
);

$form->set_properties( array(
	'form' => $new_form_template,
	'mail' => $new_mail,
) );

$id = $form->save();
if ( $id ) {
	echo "Updated Contact Form 7 form (ID: {$id}): \"{$target_title}\". Form and mail now use {…} placeholders for Polylang.\n";
	echo "Next: open WP Admin (e.g. Contact → edit form or Languages → String translations) so strings are registered, then run: wp molochko polylang-strings-ro\n";
} else {
	echo "Failed to save form.\n";
	exit( 1 );
}
