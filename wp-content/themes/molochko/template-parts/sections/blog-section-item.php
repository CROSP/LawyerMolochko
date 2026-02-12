<?php
/**
 * Single blog post slide for front-page carousel.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
$post = isset( $args['post'] ) ? $args['post'] : null;
if ( ! $post instanceof WP_Post ) {
	return;
}

$permalink = get_permalink( $post->ID );
$title     = get_the_title( $post->ID );
$thumb_id  = get_post_thumbnail_id( $post->ID );
?>
<div class="blog-section-slide">
	<div class="blog-section-image-wrap">
		<?php if ( $thumb_id ) : ?>
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
			</a>
		<?php else : ?>
			<div class="blog-section-placeholder"></div>
		<?php endif; ?>
		<div class="blog-section-overlay">
			<div class="blog-section-overlay-text">
				<div class="blog-section-date"><?php echo esc_html( get_the_date( '', $post->ID ) ); ?></div>
				<h3 class="blog-section-case-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
			</div>
			<a href="<?php echo esc_url( $permalink ); ?>" class="blog-section-arrow" aria-label="<?php echo esc_attr( $title ); ?>">
				<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
			</a>
		</div>
	</div>
</div>
