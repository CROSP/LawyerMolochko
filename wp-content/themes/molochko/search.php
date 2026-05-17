<?php
/**
 * Search results. No Elementor.
 *
 * @package Molochko
 */
get_header();
?>
<div class="container">
	<div class="row">
		<div id="pxl-content-area" class="col-12 col-lg-8">
			<main id="pxl-content-main" class="pxl-content-main content-archive">
				<?php if ( have_posts() ) : ?>
					<h1 class="page-title"><?php printf( esc_html__( 'Search: %s', 'molochko' ), get_search_query() ); ?></h1>
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content' );
					}
					the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) );
					?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</main>
		</div>
		<div id="pxl-sidebar-area" class="col-12 col-lg-4">
			<aside class="sidebar-sticky"><?php get_sidebar(); ?></aside>
		</div>
	</div>
</div>
<?php
get_footer();
