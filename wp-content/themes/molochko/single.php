<?php
/**
 * Single post. Full-width, no sidebar.
 *
 * @package Molochko
 */
get_header();
?>
<div class="container pxl-content-container">
	<div class="row">
		<div id="pxl-content-area" class="col-12">
			<main id="pxl-content-main" class="pxl-content-main molochko-blog-single">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'single' );
					the_post_navigation( array(
						'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . __( 'Попередня', 'molochko' ),
						'next_text' => __( 'Наступна', 'molochko' ) . ' <i class="zmdi zmdi-chevron-right"></i>',
					) );
				}
				?>
			</main>
		</div>
	</div>
</div>
<?php
get_footer();
