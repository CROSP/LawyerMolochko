<?php
/**
 * Single practice area grid item (for loop in practice-areas section).
 *
 * @package Molochko
 * @var WP_Post $post
 * @var string  $permalink
 * @var string  $title
 * @var string  $excerpt
 * @var string  $button_text
 * @var string  $icon_type
 * @var string  $area_icon
 * @var array   $area_img
 * @var string  $area_img_alt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
if ( ! is_array( $args ) ) {
	$args = array();
}
$post         = $args['post'] ?? null;
$permalink    = $args['permalink'] ?? '';
$title        = $args['title'] ?? '';
$excerpt      = $args['excerpt'] ?? '';
$button_text  = $args['button_text'] ?? '';
$icon_type    = $args['icon_type'] ?? '';
$area_icon    = $args['area_icon'] ?? '';
$area_img     = $args['area_img'] ?? array();
$area_img_alt = $args['area_img_alt'] ?? '';

if ( ! $post && ! $title ) {
	return;
}
?>
<div class="grid-item col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">
	<div class="grid-item-inner cross-hover">
		<div class="item-content">
			<div class="content-inner">
				<h4 class="item-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h4>
				<?php if ( ! empty( $area_img['url'] ) && $icon_type === 'image' ) : ?>
					<div class="area-icon-wrap">
						<img decoding="async" class="area-icon" src="<?php echo esc_url( $area_img['url'] ); ?>" alt="<?php echo esc_attr( $area_img_alt ); ?>">
					</div>
				<?php elseif ( ! empty( $area_icon ) ) : ?>
					<div class="area-icon-wrap">
						<i class="<?php echo esc_attr( $area_icon ); ?>"></i>
					</div>
				<?php elseif ( ! empty( $area_img['url'] ) ) : ?>
					<div class="area-icon-wrap">
						<img decoding="async" class="area-icon" src="<?php echo esc_url( $area_img['url'] ); ?>" alt="<?php echo esc_attr( $area_img_alt ); ?>">
					</div>
				<?php endif; ?>
				<?php if ( $excerpt ) : ?>
					<div class="item-excerpt"><?php echo esc_html( $excerpt ); ?></div>
				<?php endif; ?>
				<div class="item-readmore pxl-button-wrapper">
					<a class="btn-more" href="<?php echo esc_url( $permalink ); ?>">
						<span><?php echo esc_html( $button_text ); ?></span>
						<i class="zmdi zmdi-long-arrow-right"></i>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
