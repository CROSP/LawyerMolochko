<?php
/**
 * Front Page – Hero, Fancy boxes (ACF). No Elementor.
 *
 * @package Molochko
 */
get_header();

$default_fid = (int) get_option( 'page_on_front' );
$fid         = $default_fid;
if ( $default_fid && function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
	$lang = pll_current_language( 'slug' );
	if ( $lang ) {
		$tr_id = pll_get_post( $default_fid, $lang );
		if ( $tr_id ) {
			$fid = (int) $tr_id;
		}
	}
}

$hero_shortcode = $fid ? get_field( 'hero_slider_shortcode', $fid ) : '';
$hero_features  = $fid ? get_field( 'hero_features', $fid ) : array();

// Romanian front page: use RO slider alias (slider-1-1). UA uses slider-1 or ACF value.
if ( function_exists( 'pll_current_language' ) && pll_current_language( 'slug' ) === 'ro' ) {
	$hero_shortcode = '[rev_slider alias="slider-1-1"][/rev_slider]';
} elseif ( empty( $hero_shortcode ) ) {
	$hero_shortcode = '[rev_slider alias="slider-1"]';
}

// For translated front page (e.g. Romanian), fall back to default language's icon/image when current has none (media is shared).
$default_hero_features = ( $default_fid && $fid !== $default_fid ) ? get_field( 'hero_features', $default_fid ) : array();

// Default flaticon classes per row when no icon image is set (calling, reliability, medal — theme only has these for layout-4).
$default_icon_classes = array( 'flaticon flaticon-calling', 'flaticon flaticon-reliability', 'flaticon flaticon-medal' );

// Map ACF hero_features (title, icon, description, image, link) to fancy box layout.
$fancy_boxes = array();
if ( ! empty( $hero_features ) && is_array( $hero_features ) ) {
	foreach ( $hero_features as $index => $row ) {
		$link = isset( $row['link'] ) ? $row['link'] : null;
		$url  = '#';
		$btn  = '';
		if ( is_array( $link ) && ! empty( $link['url'] ) ) {
			$url = $link['url'];
			$btn = ! empty( $link['title'] ) ? $link['title'] : molochko_pll__( 'Детальніше' );
		} elseif ( is_string( $link ) && $link !== '' ) {
			$url = $link;
			$btn = molochko_pll__( 'Детальніше' );
		}
		$icon_image = isset( $row['icon'] ) ? $row['icon'] : null;
		$has_icon_img = $icon_image && ( is_array( $icon_image ) ? ( ! empty( $icon_image['id'] ) || ! empty( $icon_image ) ) : (int) $icon_image > 0 );
		$box_image   = isset( $row['image'] ) ? $row['image'] : null;
		$has_box_img = $box_image && ( is_array( $box_image ) ? ( ! empty( $box_image['id'] ) || ! empty( $box_image ) ) : (int) $box_image > 0 );
		// Use default language's icon/image when current row has none (e.g. Romanian front page without media).
		if ( ( ! $has_icon_img || ! $has_box_img ) && isset( $default_hero_features[ $index ] ) && is_array( $default_hero_features[ $index ] ) ) {
			$def = $default_hero_features[ $index ];
			if ( ! $has_icon_img && isset( $def['icon'] ) ) {
				$icon_image = $def['icon'];
				$has_icon_img = $icon_image && ( is_array( $icon_image ) ? ( ! empty( $icon_image['id'] ) || ! empty( $icon_image ) ) : (int) $icon_image > 0 );
			}
			if ( ! $has_box_img && isset( $def['image'] ) ) {
				$box_image = $def['image'];
				$has_box_img = $box_image && ( is_array( $box_image ) ? ( ! empty( $box_image['id'] ) || ! empty( $box_image ) ) : (int) $box_image > 0 );
			}
		}
		$icon_class  = $default_icon_classes[ $index % count( $default_icon_classes ) ];
		$fancy_boxes[] = array(
			'title'       => isset( $row['title'] ) ? $row['title'] : '',
			'icon'        => $icon_class,
			'icon_image'  => $has_icon_img ? $icon_image : null,
			'description' => isset( $row['description'] ) ? $row['description'] : '',
			'image'       => $has_box_img ? $box_image : null,
			'button_url'  => $url,
			'button_text' => $btn,
		);
	}
}
?>

<!-- Hero: Rev Slider or custom shortcode (RO = slider-1-1, UA = slider-1 or ACF) -->
<section class="molochko-hero section-full">
	<?php echo molochko_fix_content_image_urls( do_shortcode( $hero_shortcode ) ); ?>
</section>

<!-- Hero features (ACF hero_features): overlaps hero, 3 cols -->
<section class="molochko-fancy-boxes section-overlap">
	<div class="container">
		<div class="row row-cols-1 row-cols-md-3" style="margin-top: -86px; position: relative; z-index: 2; align-items: flex-start;">
			<?php
			if ( ! empty( $fancy_boxes ) ) {
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

<?php molochko_law_talk_section(); ?>

<?php molochko_blog_section(); ?>

<?php molochko_reviews_section(); ?>

<?php molochko_front_contact_section(); ?>

<?php
get_footer();
