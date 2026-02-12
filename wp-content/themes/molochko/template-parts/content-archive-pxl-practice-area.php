<?php
/**
 * Single card for Practice Area archive grid.
 *
 * @package Molochko
 */
$icon_type   = function_exists( 'get_field' ) ? get_field( 'area_icon_type', get_the_ID() ) : get_post_meta( get_the_ID(), 'area_icon_type', true );
$area_icon   = function_exists( 'get_field' ) ? get_field( 'area_icon', get_the_ID() ) : get_post_meta( get_the_ID(), 'area_icon', true );
$area_img    = function_exists( 'get_field' ) ? get_field( 'area_img', get_the_ID() ) : get_post_meta( get_the_ID(), 'area_img', true );
if ( ! is_array( $area_img ) && is_numeric( $area_img ) ) {
	$aid     = (int) $area_img;
	$area_img = array( 'id' => $aid, 'url' => wp_get_attachment_image_url( $aid, 'full' ), 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
}
$area_img    = is_array( $area_img ) ? $area_img : array();
$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( (int) $area_img['id'], '_wp_attachment_image_alt', true ) : ( ! empty( $area_img['alt'] ) ? $area_img['alt'] : '' );
if ( empty( $area_icon ) ) {
	$area_icon = 'flaticon flaticon-businessman';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'pa-archive-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="pa-archive-card-link" aria-label="<?php the_title_attribute( array( 'echo' => false ) ); ?>">
		<div class="pa-archive-card-icon">
			<?php if ( ! empty( $area_img['url'] ) && $icon_type === 'image' ) : ?>
				<img src="<?php echo esc_url( $area_img['url'] ); ?>" alt="<?php echo esc_attr( $area_img_alt ?: get_the_title() ); ?>" loading="lazy">
			<?php elseif ( ! empty( $area_img['url'] ) ) : ?>
				<img src="<?php echo esc_url( $area_img['url'] ); ?>" alt="<?php echo esc_attr( $area_img_alt ?: get_the_title() ); ?>" loading="lazy">
			<?php else : ?>
				<i class="<?php echo esc_attr( $area_icon ); ?>" aria-hidden="true"></i>
			<?php endif; ?>
		</div>
		<div class="pa-archive-card-body">
			<h2 class="pa-archive-card-title"><?php the_title(); ?></h2>
			<?php if ( has_excerpt() ) : ?>
				<p class="pa-archive-card-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<span class="pa-archive-card-more">
				<?php esc_html_e( 'Детальніше', 'molochko' ); ?>
				<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
			</span>
		</div>
	</a>
</article>
