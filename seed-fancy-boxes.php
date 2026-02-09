<?php
/**
 * One-time script: seed the 3 fancy box blocks on the front page (ACF repeater).
 *
 * Run from project root:
 *   ddev exec php seed-fancy-boxes.php
 * Or: wp eval-file seed-fancy-boxes.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( __FILE__ ) . '/wp-load.php';
	if ( ! is_file( $wp_load ) ) {
		fwrite( STDERR, "Error: wp-load.php not found. Run from project root.\n" );
		exit( 1 );
	}
	require_once $wp_load;
}

if ( ! function_exists( 'update_field' ) ) {
	fwrite( STDERR, "Error: ACF not active. Activate Advanced Custom Fields PRO.\n" );
	exit( 1 );
}

$fid = (int) get_option( 'page_on_front' );
if ( ! $fid ) {
	fwrite( STDERR, "Error: No front page set. Set Settings → Reading → Your homepage displays → A static page.\n" );
	exit( 1 );
}

// Resolve attachment IDs for fancy box images. Card 1: consultation image; cards 2–3: Power Legal h1_fancy2/3.
$filenames = array( '2026/01/consult-crop.jpg', '2022/07/h1_fancy2.jpg', '2022/07/h1_fancy3.jpg' );
$img_ids   = array();
foreach ( $filenames as $path ) {
	$id = attachment_url_to_postid( content_url( 'uploads/' . $path ) );
	if ( ! $id && function_exists( 'get_posts' ) ) {
		$posts = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array( array( 'key' => '_wp_attached_file', 'value' => $path, 'compare' => 'LIKE' ) ),
		) );
		$id = ! empty( $posts[0] ) ? (int) $posts[0]->ID : 0;
	}
	$img_ids[] = $id ?: 0;
}

$fancy_boxes = array(
	array(
		'icon'        => 'flaticon flaticon-calling',
		'title'       => 'Безкоштовна консультація',
		'description' => 'Якщо вам потрібна допомога юриста, ми надамо безкоштовну консультацію незалежно від складності справи. Досвід, прозорість, результат.',
		'button_text' => 'Зв\'язатися',
		'link'        => array(
			'url'    => '#contact',
			'title'  => 'Зв\'язатися',
			'target' => '',
		),
		'image'       => $img_ids[0],
	),
	array(
		'icon'        => 'flaticon flaticon-reliability',
		'title'       => '20 років досвіду',
		'description' => 'Найкращі юристи з багаторічним досвідом. Поєднуємо експертизу з індивідуальним підходом до кожної справи. Гарантуємо якість та результат.',
		'button_text' => 'Детальніше',
		'link'        => array(
			'url'    => '/pro-nas/',
			'title'  => 'Детальніше',
			'target' => '',
		),
		'image'       => $img_ids[1],
	),
	array(
		'icon'        => 'flaticon flaticon-medal',
		'title'       => 'Нагороди та сертифікати',
		'description' => 'Ми отримали визнання та довіру клієнтів. Прозорість, чесність та ефективність — наші принципи роботи в кожній справі.',
		'button_text' => 'Детальніше',
		'link'        => array(
			'url'    => '/pro-nas/',
			'title'  => 'Детальніше',
			'target' => '',
		),
		'image'       => $img_ids[2],
	),
);

$updated = update_field( 'fancy_boxes', $fancy_boxes, $fid );

if ( $updated !== false ) {
	echo "Done. Seeded 3 fancy box blocks on front page (ID {$fid}). Refresh the homepage to see them.\n";
} else {
	echo "Warning: update_field returned false. Check that the front page has the ACF field group 'Головна сторінка (Molochko)' assigned. You can still try editing the page in WP Admin and add the blocks manually.\n";
	exit( 1 );
}
