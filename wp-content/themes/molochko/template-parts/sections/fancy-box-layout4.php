<?php
/**
 * Single Fancy Box layout-4 (icon, title, description, button, optional image).
 *
 * @package Molochko
 * @var array $b Keys: icon, icon_image, title, description, button_text, button_url, button_target, image.
 *              url, target, icon_html are computed in the caller.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$b = get_query_var( 'args', array() );
if ( ! is_array( $b ) ) {
	$b = array();
}

$url    = esc_url( $b['button_url'] ?? '#' );
$target = ( $b['button_target'] ?? '' ) === '_blank' ? ' target="_blank" rel="noopener"' : '';
$icon   = $b['icon_html'] ?? ( '<i class="' . esc_attr( $b['icon'] ?? 'flaticon flaticon-calling' ) . ' pxl-fancy-icon pxl-icon"></i>' );
$img    = $b['image_html'] ?? '';
?>
<div class="col">
	<div class="pxl-fancy-box layout-4">
		<div class="box-inner">
			<div class="box-top d-flex">
				<div class="box-icon d-flex">
					<?php echo $icon; ?>
				</div>
				<h3 class="box-title"><?php echo nl2br( esc_html( $b['title'] ?? '' ) ); ?></h3>
			</div>
			<div class="box-center">
				<?php if ( ! empty( $b['description'] ) ) : ?>
					<div class="box-description"><?php echo wp_kses_post( $b['description'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $b['button_text'] ) ) : ?>
					<a class="btn-more" href="<?php echo $url; ?>"<?php echo $target; ?>>
						<span><?php echo esc_html( $b['button_text'] ); ?></span>
						<i class="zmdi zmdi-long-arrow-right"></i>
					</a>
				<?php endif; ?>
			</div>
			<?php if ( $img ) : ?>
				<div class="box-image"><?php echo $img; ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
