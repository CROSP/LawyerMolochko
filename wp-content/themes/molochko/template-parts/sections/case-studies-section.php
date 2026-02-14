<?php
/**
 * Case Studies section – static heading + simple horizontal carousel.
 * Uses default post fields only (title, featured image, taxonomy).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args  = get_query_var( 'args', array() );
$posts = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();

$subtitle = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Практичний досвід' ) : __( 'Практичний досвід', 'molochko' );
$title    = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Останні кейси' ) : __( 'Останні кейси', 'molochko' );
$desc     = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Наша відданість справі та досвід дозволяють ефективно представляти ваші інтереси — від консультації до суду. ТЦК, ВЛК, ДТП, кримінал, сімейні та трудові спори.' ) : __( 'Наша відданість справі та досвід дозволяють ефективно представляти ваші інтереси — від консультації до суду. ТЦК, ВЛК, ДТП, кримінал, сімейні та трудові спори.', 'molochko' );
$btn_text = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Усі кейси' ) : __( 'Усі кейси', 'molochko' );
$btn_url  = get_post_type_archive_link( 'molochko-case-study' ) ?: '#';
?>
<section class="molochko-case-studies">
	<div class="mcs-header-wrapper">
		<div class="mcs-header-inner">
			<div class="mcs-header-left">
				<div class="mcs-subtitle"><?php echo esc_html( $subtitle ); ?></div>
				<h2 class="mcs-title"><?php echo esc_html( $title ); ?></h2>
				<p class="mcs-desc"><?php echo esc_html( $desc ); ?></p>
			</div>
			<div class="mcs-header-right">
				<a href="<?php echo esc_url( $btn_url ); ?>" class="mcs-view-all">
					<span><?php echo esc_html( $btn_text ); ?></span>
				</a>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $posts ) ) : ?>
	<div class="mcs-carousel">
		<div class="mcs-track">
			<?php
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				set_query_var( 'args', array( 'post' => $post ) );
				get_template_part( 'template-parts/sections/case-study-item' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php endif; ?>
</section>
