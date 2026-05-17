<?php
/**
 * Page content. No Elementor.
 *
 * @package Molochko
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
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
