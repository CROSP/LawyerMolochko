<?php
/**
 * Blog / archive. No Elementor.
 *
 * @package Molochko
 */
get_header();
?>
<div class="container">
	<div class="row">
		<div id="pxl-content-area" class="col-12 col-lg-8">
			<?php if ( have_posts() ) : ?>
				<main id="pxl-content-main" class="pxl-content-main content-archive">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content' );
					}
					?>
				</main>
				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
				) );
				?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<div id="pxl-sidebar-area" class="col-12 col-lg-4">
			<aside class="sidebar-sticky">
				<?php get_sidebar(); ?>
			</aside>
		</div>
	</div>
</div>
<?php
get_footer();
