<?php
/**
 * Single post. No Elementor.
 *
 * @package Molochko
 */
get_header();
?>
<div class="container pxl-content-container">
	<div class="row">
		<div id="pxl-content-area" class="col-12 col-lg-8">
			<main id="pxl-content-main" class="pxl-content-main">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'single' );
					the_post_navigation( array(
						'prev_text' => '&larr; %title',
						'next_text' => '%title &rarr;',
					) );
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				}
				?>
			</main>
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
