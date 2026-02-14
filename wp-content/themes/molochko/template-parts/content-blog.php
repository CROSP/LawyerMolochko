<?php
/**
 * Single post card for blog archive grid.
 *
 * @package Molochko
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-archive-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="blog-archive-card-link" aria-label="<?php the_title_attribute( array( 'echo' => false ) ); ?>">
		<div class="blog-archive-card-image">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'alt' => get_the_title() ) ); ?>
			<?php else : ?>
				<div class="blog-archive-card-placeholder"></div>
			<?php endif; ?>
			<div class="blog-archive-card-overlay"></div>
		</div>
		<div class="blog-archive-card-body">
			<time class="blog-archive-card-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<h2 class="blog-archive-card-title"><?php the_title(); ?></h2>
			<?php if ( has_excerpt() ) : ?>
				<p class="blog-archive-card-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<span class="blog-archive-card-more">
				<?php echo esc_html( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Читати далі' ) : __( 'Читати далі', 'molochko' ) ); ?>
				<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
			</span>
		</div>
	</a>
</article>
