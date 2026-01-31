<?php
/**
 * Post card for archive. No Elementor.
 *
 * @package Molochko
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
		</div>
	<?php endif; ?>
	<header class="entry-header">
		<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
		<div class="entry-meta">
			<span class="posted-on"><?php echo get_the_date(); ?></span>
			<span class="byline"> <?php the_author(); ?></span>
		</div>
	</header>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div>
	<a href="<?php the_permalink(); ?>" class="btn-more"><span><?php esc_html_e( 'Read more', 'molochko' ); ?></span><i class="zmdi zmdi-long-arrow-right"></i></a>
</article>
