<?php
/**
 * 404. No Elementor.
 *
 * @package Molochko
 */
get_header();
?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-12 col-md-8 text-center">
			<main id="pxl-content-main">
				<h1 class="page-title"><?php esc_html_e( 'Page Not Found', 'molochko' ); ?></h1>
				<p><?php esc_html_e( 'The page you are looking for might have been removed or is temporarily unavailable.', 'molochko' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-more"><span><?php esc_html_e( 'Back to Home', 'molochko' ); ?></span><i class="zmdi zmdi-long-arrow-right"></i></a>
			</main>
		</div>
	</div>
</div>
<?php
get_footer();
