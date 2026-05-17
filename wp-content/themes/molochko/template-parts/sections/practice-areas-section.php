<?php
/**
 * Practice Areas section: heading + grid of items.
 *
 * @package Molochko
 * @var array $posts       Array of WP_Post (pxl-practice-area).
 * @var string $button_text "Детальніше" or similar.
 * @var int    $num_words  Excerpt length.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
if ( ! is_array( $args ) ) {
	$args = array();
}
$posts         = $args['posts'] ?? array();
$items         = $args['items'] ?? array();
$use_repeater  = ! empty( $args['use_repeater'] );
$button_text   = $args['button_text'] ?? molochko_pll__( 'Детальніше' );
$num_words     = isset( $args['num_words'] ) ? (int) $args['num_words'] : 15;
$grid_items    = $use_repeater ? $items : $posts;

$fid = (int) get_option( 'page_on_front' );
if ( $fid && function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
	$lang = pll_current_language( 'slug' );
	if ( $lang ) {
		$tr_id = pll_get_post( $fid, $lang );
		if ( $tr_id ) {
			$fid = (int) $tr_id;
		}
	}
}
$heading_subtitle = $fid && function_exists( 'get_field' ) ? get_field( 'practice_areas_subtitle', $fid ) : '';
$heading_title    = $fid && function_exists( 'get_field' ) ? get_field( 'practice_areas_title', $fid ) : '';
$heading_desc     = $fid && function_exists( 'get_field' ) ? get_field( 'practice_areas_description', $fid ) : '';
if ( ! $heading_subtitle ) {
	$heading_subtitle = molochko_pll__( 'Наша експертиза' );
}
if ( ! $heading_title ) {
	$heading_title = molochko_pll__( 'Напрямки юридичної практики' );
}
if ( ! $heading_desc ) {
	$heading_desc = molochko_pll__( 'Надаємо кваліфіковану юридичну допомогу у кримінальних справах, спорах через ДТП, військових питаннях, сімейних та трудових спорах. Маємо значний досвід у цих напрямках та гарантуємо індивідуальний підхід до кожної справи.' );
}
?>
<section class="molochko-practice-areas elementor-section elementor-section-boxed elementor-section-height-default">
	<div class="elementor-container elementor-column-gap-default">
		<div class="elementor-column elementor-col-100">
			<?php
			$heading_args = array(
				'subtitle'    => $heading_subtitle,
				'title'       => $heading_title,
				'description' => $heading_desc,
				'align'       => 'center',
				'divider'     => true,
			);
			set_query_var( 'args', $heading_args );
			get_template_part( 'template-parts/sections/section-heading' );
			set_query_var( 'args', array( 'posts' => $posts, 'items' => $items, 'use_repeater' => $use_repeater, 'button_text' => $button_text, 'num_words' => $num_words ) );
			?>

			<?php if ( ! empty( $grid_items ) ) : ?>
			<div class="pxl-grid pxl-practice-area-grid layout-pxl-practice-area-1" data-layout-mode="fitRows" data-total="<?php echo count( $grid_items ); ?>" data-perpage="<?php echo count( $grid_items ); ?>">
				<div class="pxl-grid-overlay"></div>
				<div class="pxl-grid-inner row relative overflow-hidden">
					<?php
					if ( $use_repeater ) {
						foreach ( $items as $item ) {
							$link     = isset( $item['link'] ) && is_array( $item['link'] ) ? $item['link'] : array();
							$permalink = ! empty( $link['url'] ) ? $link['url'] : '#';
							$area_img  = isset( $item['icon_image'] ) && is_array( $item['icon_image'] ) ? $item['icon_image'] : array();
							$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( $area_img['id'], '_wp_attachment_image_alt', true ) : '';
							set_query_var( 'args', array(
								'post'         => null,
								'permalink'    => $permalink,
								'title'        => isset( $item['title'] ) ? $item['title'] : '',
								'excerpt'      => isset( $item['description'] ) ? $item['description'] : '',
								'button_text'  => $button_text,
								'icon_type'    => ! empty( $area_img['url'] ) ? 'image' : '',
								'area_icon'    => isset( $item['icon'] ) ? $item['icon'] : 'flaticon flaticon-businessman',
								'area_img'     => $area_img,
								'area_img_alt' => $area_img_alt,
							) );
							get_template_part( 'template-parts/sections/practice-area-item' );
						}
					} else {
						foreach ( $posts as $post ) {
							$icon_type   = function_exists( 'get_field' ) ? get_field( 'area_icon_type', $post->ID ) : get_post_meta( $post->ID, 'area_icon_type', true );
							$area_icon   = function_exists( 'get_field' ) ? get_field( 'area_icon', $post->ID ) : get_post_meta( $post->ID, 'area_icon', true );
							$area_img    = function_exists( 'get_field' ) ? get_field( 'area_img', $post->ID ) : get_post_meta( $post->ID, 'area_img', true );
							if ( ! is_array( $area_img ) && is_numeric( $area_img ) ) {
								$aid = (int) $area_img;
								$area_img = array( 'id' => $aid, 'url' => wp_get_attachment_image_url( $aid, 'full' ), 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
							}
							$area_img    = is_array( $area_img ) ? $area_img : array();
							$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( (int) $area_img['id'], '_wp_attachment_image_alt', true ) : ( ! empty( $area_img['alt'] ) ? $area_img['alt'] : '' );
							// Fallback: use default-language (UA) post icon when RO post has none (media shared).
							$has_icon = ( ! empty( $area_icon ) ) || ( ! empty( $area_img['url'] ) );
							if ( ! $has_icon && function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
								$default_slug = pll_default_language( 'slug' );
								$default_id   = $default_slug ? (int) pll_get_post( $post->ID, $default_slug ) : 0;
								if ( $default_id && $default_id !== $post->ID ) {
									$icon_type   = function_exists( 'get_field' ) ? get_field( 'area_icon_type', $default_id ) : get_post_meta( $default_id, 'area_icon_type', true );
									$area_icon   = function_exists( 'get_field' ) ? get_field( 'area_icon', $default_id ) : get_post_meta( $default_id, 'area_icon', true );
									$area_img    = function_exists( 'get_field' ) ? get_field( 'area_img', $default_id ) : get_post_meta( $default_id, 'area_img', true );
									if ( ! is_array( $area_img ) && is_numeric( $area_img ) ) {
										$aid = (int) $area_img;
										$area_img = array( 'id' => $aid, 'url' => wp_get_attachment_image_url( $aid, 'full' ), 'alt' => get_post_meta( $aid, '_wp_attachment_image_alt', true ) );
									}
									$area_img    = is_array( $area_img ) ? $area_img : array();
									$area_img_alt = ! empty( $area_img['id'] ) ? get_post_meta( (int) $area_img['id'], '_wp_attachment_image_alt', true ) : ( ! empty( $area_img['alt'] ) ? $area_img['alt'] : '' );
								}
							}
							$permalink   = get_permalink( $post->ID );
							$title       = get_the_title( $post->ID );
							$excerpt     = $post->post_excerpt ? wp_trim_words( $post->post_excerpt, $num_words, null ) : wp_trim_words( strip_shortcodes( $post->post_content ), $num_words, null );
							set_query_var( 'args', array(
								'post'         => $post,
								'permalink'    => $permalink,
								'title'        => $title,
								'excerpt'      => $excerpt,
								'button_text'  => $button_text,
								'icon_type'    => $icon_type,
								'area_icon'    => $area_icon,
								'area_img'     => $area_img,
								'area_img_alt' => $area_img_alt,
							) );
							get_template_part( 'template-parts/sections/practice-area-item' );
						}
					}
					?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
