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
$use_custom_hero = $fid && get_field( 'use_custom_hero_slider', $fid );
$hero_slides    = ( $fid && $use_custom_hero ) ? get_field( 'hero_slides', $fid ) : array();
if ( ! is_array( $hero_slides ) ) {
	$hero_slides = array();
}
// Normalize slides: need at least image (ID or array with id).
$custom_slides = array();
foreach ( $hero_slides as $row ) {
	$img = isset( $row['image'] ) ? $row['image'] : null;
	$url = null;
	if ( is_array( $img ) && ! empty( $img['id'] ) ) {
		$url = wp_get_attachment_image_url( (int) $img['id'], 'full' );
	} elseif ( is_array( $img ) && ! empty( $img['url'] ) ) {
		$url = $img['url'];
	} elseif ( (int) $img > 0 ) {
		$url = wp_get_attachment_image_url( (int) $img, 'full' );
	}
	if ( $url ) {
		$custom_slides[] = array(
			'image_url' => $url,
			'title'     => isset( $row['title'] ) ? $row['title'] : '',
			'caption'   => isset( $row['caption'] ) ? $row['caption'] : '',
			'link'      => isset( $row['link'] ) ? $row['link'] : '',
		);
	}
}
$use_custom_hero = $use_custom_hero && ! empty( $custom_slides );

// Hero: ACF custom slider takes priority; otherwise fall back to Molochko Slider (mu-plugin).
// Was Revolution Slider before — replaced 2026-05-17 after revslider plugin removal.
if ( ! $use_custom_hero ) {
	if ( function_exists( 'pll_current_language' ) && pll_current_language( 'slug' ) === 'ro' ) {
		$hero_shortcode = '[molochko_slider id="7"]';
	} elseif ( empty( $hero_shortcode ) ) {
		$hero_shortcode = '[molochko_slider id="1"]';
	}
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

<!-- Hero: custom ACF slider (hero_slides) or Rev Slider shortcode -->
<section class="molochko-hero section-full">
	<?php if ( $use_custom_hero ) : ?>
		<div class="molochko-hero-custom">
			<div class="molochko-hero-slides">
				<?php foreach ( $custom_slides as $slide ) : ?>
					<div class="molochko-hero-slide">
						<?php if ( ! empty( $slide['link'] ) ) : ?>
							<a href="<?php echo esc_url( is_array( $slide['link'] ) ? ( $slide['link']['url'] ?? '#' ) : $slide['link'] ); ?>" class="molochko-hero-slide-link">
						<?php endif; ?>
						<img src="<?php echo esc_url( $slide['image_url'] ); ?>" alt="<?php echo esc_attr( $slide['title'] ); ?>" loading="lazy" />
						<?php if ( $slide['title'] || $slide['caption'] ) : ?>
							<div class="molochko-hero-slide-caption">
								<?php if ( $slide['title'] ) : ?><h2 class="molochko-hero-slide-title"><?php echo esc_html( $slide['title'] ); ?></h2><?php endif; ?>
								<?php if ( $slide['caption'] ) : ?><p class="molochko-hero-slide-text"><?php echo esc_html( $slide['caption'] ); ?></p><?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $slide['link'] ) ) : ?></a><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<?php echo do_shortcode( $hero_shortcode ); ?>
	<?php endif; ?>
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
