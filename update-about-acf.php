<?php
/**
 * Update front page About block ACF fields (same as MCP acf_update_fields_batch).
 * Run from project root: php update-about-acf.php
 * With DDEV: ddev exec php update-about-acf.php
 * Requires: static front page set in Settings → Reading.
 *
 * When MCP is connected, use acf_get_front_page_id + acf_update_fields_batch instead.
 */

require_once __DIR__ . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed.' );
}

if ( ! function_exists( 'update_field' ) ) {
	die( "ACF is not active.\n" );
}

$post_id = (int) get_option( 'page_on_front' );
if ( ! $post_id ) {
	die( "No static front page set. Set Settings → Reading → Your homepage displays → A static page.\n" );
}

$phone     = get_theme_mod( 'molochko_phone', '+38(050)-606-00-79' );
$tel_href  = 'tel:' . preg_replace( '/\D+/', '', $phone );
$book_url  = esc_url( home_url( '/book-appointment' ) );
$contact_p = '<p><a href="' . esc_url( $tel_href ) . '">' . esc_html( $phone ) . '</a> або <a href="' . $book_url . '">Записатися на консультацію</a></p>';

$fields = array(
	'about_subtitle'     => 'ПРО бюро',
	'about_title'        => 'Пристрасть до справедливості. Досвід для перемоги.',
	'about_description'  => '<p>В Адвокатському бюро ми створили команду юристів, які поєднують глибокі знання, практичний досвід та щире прагнення допомогти клієнтам у складних юридичних ситуаціях.</p><p>Наша культура базується на довірі, прозорості та відповідальності. Щодня ми працюємо над тим, щоб клієнт відчував підтримку на кожному етапі — від першої консультації до завершення справи.</p>',
	'about_cta_line'     => 'Телефонуйте нам 24/7. Почнімо боротися разом.',
	'about_contact_line' => $contact_p,
	'about_name'         => 'Молочко Тарас Вікторович',
	'about_role'         => 'голова',
);

foreach ( $fields as $name => $value ) {
	$ok = update_field( $name, $value, $post_id );
	echo ( $ok ? '[OK]' : '[--]' ) . " {$name}\n";
}

echo "Done. Front page ID: {$post_id}\n";
