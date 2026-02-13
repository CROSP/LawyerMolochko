<?php
/**
 * One-time: create Contact Form 7 form "Замовити консультацію" for contact page and popup.
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/seed-contact-form7.php
 * Creates the form only if a form with this title does not already exist.
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
foreach ( $forms as $f ) {
	if ( $f->title() === $target_title ) {
		echo "Form \"{$target_title}\" already exists (ID: " . $f->id() . ").\n";
		exit( 0 );
	}
}

$form = WPCF7_ContactForm::get_template( array(
	'title' => $target_title,
) );

$form->set_properties( array(
	'form' => '
<label> Ваше ім\'я *
    [text* your-name autocomplete:name placeholder "Ім\'я"] </label>

<label> Телефон *
    [text* your-phone placeholder "Номер телефону"] </label>

<label> Тема (необов\'язково)
    [text your-subject placeholder "Коротко про ваше питання"] </label>

<label> Повідомлення *
    [textarea* your-message placeholder "Опишіть ситуацію або питання"] </label>

[submit "Надіслати заявку"]',
	'mail' => array(
		'active' => true,
		'subject' => '[Молочко] Нова заявка на консультацію: [your-subject]',
		'sender' => '[your-name] <wordpress@' . ( isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/[^a-z0-9.-]/', '', $_SERVER['HTTP_HOST'] ) : 'localhost' ) . '>',
		'body' => "Ім'я: [your-name]\nТелефон: [your-phone]\nТема: [your-subject]\n\nПовідомлення:\n[your-message]",
		'recipient' => get_option( 'admin_email' ),
		'additional_headers' => '',
		'attachments' => '',
		'use_html' => false,
		'exclude_blank' => false,
	),
) );

$id = $form->save();
if ( $id ) {
	echo "Created Contact Form 7 form (ID: {$id}): \"Замовити консультацію\". It will be used on the Contact page and in the consultation popup.\n";
} else {
	echo "Failed to create form.\n";
	exit( 1 );
}
