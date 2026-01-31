<?php
/**
 * Single post content. No Elementor.
 *
 * @package Molochko
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-thumbnail"><?php the_post_thumbnail( 'large' ); ?></div>
	<?php endif; ?>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<div class="entry-meta">
			<span class="posted-on"><?php echo get_the_date(); ?></span>
			<span class="byline"> <?php the_author(); ?></span>
		</div>
	</header>
	<div class="pxl-entry-content clearfix">
		<?php
		the_content();
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'molochko' ),
			'after'  => '</div>',
		) );
		?>
	</div>
</article>
