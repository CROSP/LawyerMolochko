<?php
/**
 * Single Case Study – full width, no sidebar, premium layout.
 *
 * @package Molochko
 */
get_header();
?>
<div class="molochko-case-study-single">
	<main id="pxl-content-main" class="cs-single-main">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content', 'single-molochko-case-study' );
		}
		?>
	</main>
</div>
<?php
get_footer();
