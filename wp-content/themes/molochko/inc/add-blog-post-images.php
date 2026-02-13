<?php
/**
 * One-time: add featured images to existing blog posts (post type 'post').
 * Uses free Unsplash images matched to each article topic.
 *
 * Run once:
 *   ddev exec wp eval-file wp-content/themes/molochko/inc/add-blog-post-images.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'media_sideload_image' ) ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

// Map post title (partial match) => Pexels image URL (free to use, same as case studies).
$image_map = array(
	'Повістка ТЦК'           => 'https://images.pexels.com/photos/54098/us-army-soldiers-army-men-54098.jpeg?auto=compress&w=1200',
	'Відновлення на роботі'   => 'https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg?auto=compress&w=1200',
	'Оскарження рішення ВЛК' => 'https://images.pexels.com/photos/5393593/pexels-photo-5393593.jpeg?auto=compress&w=1200',
	'Потерпілий при ДТП'     => 'https://images.pexels.com/photos/8540598/pexels-photo-8540598.jpeg?auto=compress&w=1200',
	'Розірвання шлюбу'       => 'https://images.pexels.com/photos/5691692/pexels-photo-5691692.jpeg?auto=compress&w=1200',
	'Захист під час слідчих' => 'https://images.pexels.com/photos/5669619/pexels-photo-5669619.jpeg?auto=compress&w=1200',
	'Спадкування за законом' => 'https://images.pexels.com/photos/3756678/pexels-photo-3756678.jpeg?auto=compress&w=1200',
	'Відстрочка від призову' => 'https://images.pexels.com/photos/7687557/pexels-photo-7687557.jpeg?auto=compress&w=1200',
);

$posts = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

$updated = 0;
foreach ( $posts as $post ) {
	if ( get_post_thumbnail_id( $post->ID ) ) {
		continue;
	}
	$img_url = null;
	foreach ( $image_map as $title_part => $url ) {
		if ( strpos( $post->post_title, $title_part ) !== false ) {
			$img_url = $url;
			break;
		}
	}
	if ( ! $img_url ) {
		$img_url = 'https://images.pexels.com/photos/5669619/pexels-photo-5669619.jpeg?auto=compress&w=1200';
	}

	$attachment_id = media_sideload_image( $img_url, $post->ID, $post->post_title, 'id' );
	if ( is_wp_error( $attachment_id ) ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::warning( "Failed to load image for: {$post->post_title} - " . $attachment_id->get_error_message() );
		}
		continue;
	}
	set_post_thumbnail( $post->ID, $attachment_id );
	$updated++;
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( "Set featured image for: {$post->post_title}" );
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( "Set featured images for {$updated} post(s)." );
} else {
	echo "Set featured images for {$updated} post(s).\n";
}
