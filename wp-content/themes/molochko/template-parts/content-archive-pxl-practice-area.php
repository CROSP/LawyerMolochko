<?php
/**
 * Single card for Practice Area archive grid.
 *
 * @package Molochko
 */
$post_id     = get_the_ID();
$icon_type   = function_exists( 'get_field' ) ? get_field( 'area_icon_type', $post_id ) : get_post_meta( $post_id, 'area_icon_type', true );
$area_icon   = function_exists( 'get_field' ) ? get_field( 'area_icon', $post_id ) : get_post_meta( $post_id, 'area_icon', true );
$area_img    = function_exists( 'get_field' ) ? get_field( 'area_img', $post_id ) : get_post_meta( $post_id, 'area_img', true );
if ( ! is_array( $area_img ) && is_numeric( $area_img ) ) {
	$aid     = (int) $area_img;
	$area_img = array( 'id' => $aid, 'url' => wp_get_attachment_image_url( $aid, 'full' ), 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
}
$area_img    = is_array( $area_img ) ? $area_img : array();
$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( (int) $area_img['id'], '_wp_attachment_image_alt', true ) : ( ! empty( $area_img['alt'] ) ? $area_img['alt'] : '' );
// Fallback: use default-language (UA) post icon when RO post has none.
$has_icon = ( ! empty( $area_icon ) ) || ( ! empty( $area_img['url'] ) );
if ( ! $has_icon && function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
	$default_slug = pll_default_language( 'slug' );
	$default_id   = $default_slug ? (int) pll_get_post( $post_id, $default_slug ) : 0;
	if ( $default_id && $default_id !== $post_id ) {
		$icon_type   = function_exists( 'get_field' ) ? get_field( 'area_icon_type', $default_id ) : get_post_meta( $default_id, 'area_icon_type', true );
		$area_icon   = function_exists( 'get_field' ) ? get_field( 'area_icon', $default_id ) : get_post_meta( $default_id, 'area_icon', true );
		$area_img    = function_exists( 'get_field' ) ? get_field( 'area_img', $default_id ) : get_post_meta( $default_id, 'area_img', true );
		if ( ! is_array( $area_img ) && is_numeric( $area_img ) ) {
			$aid     = (int) $area_img;
			$area_img = array( 'id' => $aid, 'url' => wp_get_attachment_image_url( $aid, 'full' ), 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
		}
		$area_img    = is_array( $area_img ) ? $area_img : array();
		$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( (int) $area_img['id'], '_wp_attachment_image_alt', true ) : ( ! empty( $area_img['alt'] ) ? $area_img['alt'] : '' );
	}
}
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
