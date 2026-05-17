<?php
/**
 * Single Practice Area – full width, no sidebar, premium layout.
 *
 * @package Molochko
 */
get_header();
?>
<div class="pa-single">
	<main id="pxl-content-main" class="pa-single-main">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content', 'single-pxl-practice-area' );
		}
		?>
	</main>
</div>
<?php
get_footer();
