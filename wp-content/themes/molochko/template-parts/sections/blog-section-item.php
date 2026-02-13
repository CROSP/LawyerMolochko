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
$excerpt   = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : wp_trim_words( get_post_field( 'post_content', $post->ID ), 18 );
?>
<div class="blog-section-slide">
	<article class="blog-section-card">
		<div class="blog-section-card-image">
			<?php if ( $thumb_id ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="blog-section-placeholder"></a>
			<?php endif; ?>
		</div>
		<div class="blog-section-card-body">
			<time class="blog-section-card-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post->ID ) ); ?>"><?php echo esc_html( get_the_date( '', $post->ID ) ); ?></time>
			<h3 class="blog-section-card-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
			<?php if ( $excerpt ) : ?>
				<p class="blog-section-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( $permalink ); ?>" class="blog-section-card-link"><?php esc_html_e( 'Читати далі', 'molochko' ); ?></a>
		</div>
	</article>
</div>
