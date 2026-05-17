<?php
/**
 * Single Case Study slide: image with bottom overlay (title + category + arrow).
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
$terms     = get_the_terms( $post->ID, 'case_study_category' );
$term      = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : null;
?>
<div class="mcs-slide">
	<div class="mcs-image-wrap">
		<?php if ( $thumb_id ) : ?>
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
			</a>
		<?php endif; ?>
		<div class="mcs-overlay">
			<div class="mcs-overlay-text">
				<?php if ( $term ) : ?>
					<div class="mcs-category"><?php echo esc_html( $term->name ); ?></div>
				<?php endif; ?>
				<h3 class="mcs-case-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
			</div>
			<div class="mcs-arrow-wrap">
				<a href="<?php echo esc_url( $permalink ); ?>" class="mcs-arrow" aria-label="<?php echo esc_attr( $title ); ?>">
					<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>
	</div>
</div>
