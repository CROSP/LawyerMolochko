<?php
/**
 * Case Studies section: two-column header (heading + description | button) + grid of case study cards.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = get_query_var( 'args', array() );
$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();
$fid    = (int) get_option( 'page_on_front' );

$subtitle = $fid && function_exists( 'get_field' ) ? get_field( 'case_studies_subtitle', $fid ) : '';
$title    = $fid && function_exists( 'get_field' ) ? get_field( 'case_studies_title', $fid ) : '';
$desc     = $fid && function_exists( 'get_field' ) ? get_field( 'case_studies_description', $fid ) : '';
$btn_text = $fid && function_exists( 'get_field' ) ? get_field( 'case_studies_button_text', $fid ) : '';
$btn_url  = $fid && function_exists( 'get_field' ) ? get_field( 'case_studies_button_url', $fid ) : '';

if ( ! $subtitle ) {
	$subtitle = __( 'Досвід роботи', 'molochko' );
}
if ( ! $title ) {
	$title = __( 'Останні кейси', 'molochko' );
}
if ( ! $desc ) {
	$desc = __( 'Наша відданість справі та постійний рух уперед дозволяють покращувати якість представництва та надавати послуги, яких ви не знайдете ніде більше.', 'molochko' );
}
if ( ! $btn_text ) {
	$btn_text = __( 'Всі кейси', 'molochko' );
}
if ( ! $btn_url ) {
	$btn_url = get_post_type_archive_link( 'molochko-case-study' ) ?: '#';
}
?>
<section class="molochko-case-studies elementor-section elementor-section-boxed elementor-section-height-default">
	<div class="elementor-container elementor-column-gap-default">
		<div class="elementor-column elementor-col-100">
			<div class="molochko-case-studies-header row align-items-center">
				<div class="col-12 col-lg-6">
					<div class="pxl-heading-wrap layout1">
						<div class="heading-subtitle"><?php echo esc_html( $subtitle ); ?></div>
						<h2 class="heading-title"><?php echo esc_html( $title ); ?></h2>
					</div>
					<div class="molochko-case-studies-desc pxl-text-editor">
						<p><?php echo esc_html( $desc ); ?></p>
					</div>
					<div class="elementor-divider molochko-case-studies-divider">
						<span class="elementor-divider-separator"></span>
					</div>
				</div>
				<div class="col-12 col-lg-6 d-flex justify-content-lg-end mt-3 mt-lg-0">
					<div class="pxl-button-wrapper">
						<a href="<?php echo esc_url( $btn_url ); ?>" class="btn btn-default icon-ps-right">
							<span class="pxl-button-text"><?php echo esc_html( $btn_text ); ?></span>
							<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
						</a>
					</div>
				</div>
			</div>

			<?php if ( ! empty( $posts ) ) : ?>
			<div class="molochko-case-studies-grid row row-cols-1 row-cols-sm-2 row-cols-xl-4">
				<?php
				foreach ( $posts as $post ) {
					setup_postdata( $post );
					set_query_var( 'args', array( 'post' => $post ) );
					get_template_part( 'template-parts/sections/case-study-item' );
				}
				wp_reset_postdata();
				?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
