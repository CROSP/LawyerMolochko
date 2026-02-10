<?php
/**
 * Single case study card (image, title, category, readmore).
 *
 * @package Molochko
 * @var WP_Post $post
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
<div class="molochko-case-study-item">
	<div class="item-inner relative">
		<?php if ( $thumb_id ) : ?>
		<div class="item-featured scale-hover">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo wp_get_attachment_image( $thumb_id, 'medium_large', false, array( 'class' => 'no-lazyload', 'loading' => 'lazy' ) ); ?>
			</a>
		</div>
		<?php endif; ?>
		<div class="box-title">
			<div class="title-wrap">
				<h3 class="item-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
				<?php if ( $term ) : ?>
				<div class="item-category">
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" rel="tag"><?php echo esc_html( $term->name ); ?></a>
				</div>
				<?php endif; ?>
			</div>
			<div class="item-readmore">
				<a href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
					<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>
	</div>
</div>
