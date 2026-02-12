<?php
/**
 * Front Page – Hero, Fancy boxes (ACF). No Elementor.
 *
 * @package Molochko
 */
get_header();

$fid            = (int) get_option( 'page_on_front' );
$hero_shortcode = $fid ? get_field( 'hero_slider_shortcode', $fid ) : '';
$fancy_boxes    = $fid ? get_field( 'fancy_boxes', $fid ) : array();

if ( empty( $hero_shortcode ) ) {
	$hero_shortcode = '[rev_slider alias="slider-1"]';
}
?>

<!-- Hero: Rev Slider or custom shortcode -->
<section class="molochko-hero section-full">
	<?php echo do_shortcode( $hero_shortcode ); ?>
</section>

<!-- Fancy boxes (layout 4): overlaps hero, 3 cols -->
<section class="molochko-fancy-boxes section-overlap">
	<div class="container">
		<div class="row row-cols-1 row-cols-md-3" style="margin-top: -86px; position: relative; z-index: 2; align-items: flex-start;">
			<?php
			if ( ! empty( $fancy_boxes ) && is_array( $fancy_boxes ) ) {
				foreach ( $fancy_boxes as $box ) {
					molochko_fancy_box_layout4( $box );
				}
			}
			?>
		</div>
	</div>
</section>

<?php molochko_about_block( $fid ); ?>

<?php molochko_practice_areas_section(); ?>

<?php molochko_case_studies_section(); ?>

<?php molochko_blog_section(); ?>

<?php molochko_law_talk_section(); ?>

<?php
get_footer();
