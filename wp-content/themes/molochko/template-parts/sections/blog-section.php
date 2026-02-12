<?php
/**
 * Blog section – recent posts carousel for front page.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args  = get_query_var( 'args', array() );
$posts = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : array();

$subtitle = __( 'Корисні статті', 'molochko' );
$title    = __( 'Останні публікації', 'molochko' );
$desc     = __( 'Про право, ваші права та типові юридичні ситуації — коротко та зрозуміло.', 'molochko' );
$btn_text = __( 'Усі статті', 'molochko' );
$blog_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' );
?>
<section class="molochko-blog-section">
	<div class="blog-section-header">
		<div class="blog-section-inner">
			<div class="blog-section-left">
				<div class="blog-section-subtitle"><?php echo esc_html( $subtitle ); ?></div>
				<h2 class="blog-section-title"><?php echo esc_html( $title ); ?></h2>
				<p class="blog-section-desc"><?php echo esc_html( $desc ); ?></p>
			</div>
			<div class="blog-section-right">
				<a href="<?php echo esc_url( $blog_url ); ?>" class="blog-section-view-all">
					<span><?php echo esc_html( $btn_text ); ?></span>
				</a>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $posts ) ) : ?>
	<div class="blog-section-carousel">
		<div class="blog-section-track">
			<?php
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				set_query_var( 'args', array( 'post' => $post ) );
				get_template_part( 'template-parts/sections/blog-section-item' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php endif; ?>
</section>
