<?php
/**
 * Single card for Case Study archive grid.
 *
 * @package Molochko
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'cs-archive-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="cs-archive-card-link" aria-label="<?php the_title_attribute( array( 'echo' => false ) ); ?>">
		<div class="cs-archive-card-image">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'alt' => get_the_title() ) ); ?>
			<?php else : ?>
				<div class="cs-archive-card-placeholder"></div>
			<?php endif; ?>
			<div class="cs-archive-card-overlay"></div>
		</div>
		<div class="cs-archive-card-body">
			<?php
			$terms = get_the_terms( get_the_ID(), 'case_study_category' );
			$term  = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : null;
			if ( $term ) :
				?>
				<span class="cs-archive-card-category"><?php echo esc_html( $term->name ); ?></span>
			<?php endif; ?>
			<h2 class="cs-archive-card-title"><?php the_title(); ?></h2>
			<?php if ( has_excerpt() ) : ?>
				<p class="cs-archive-card-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<span class="cs-archive-card-more">
				<?php esc_html_e( 'Детальніше', 'molochko' ); ?>
				<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
			</span>
		</div>
	</a>
</article>
