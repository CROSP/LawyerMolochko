<?php
/**
 * Archive template for Reviews CPT.
 * URL: /reviews/
 *
 * @package Molochko
 */

get_header();

$archive_title    = __( 'Відгуки клієнтів', 'molochko' );
$archive_subtitle = __( 'Що говорять клієнти', 'molochko' );
$archive_desc     = __( 'Реальні історії та відгуки людей, яким ми допомогли.', 'molochko' );
?>
<div class="molochko-reviews-archive">
	<header class="molochko-reviews-archive-hero">
		<div class="molochko-reviews-bg" aria-hidden="true"></div>
		<div class="molochko-reviews-archive-hero-inner">
			<p class="molochko-reviews-subtitle"><?php echo esc_html( $archive_subtitle ); ?></p>
			<h1 class="molochko-reviews-title"><?php echo esc_html( $archive_title ); ?></h1>
			<p class="molochko-reviews-desc"><?php echo esc_html( $archive_desc ); ?></p>
		</div>
	</header>

	<div class="molochko-reviews-archive-main">
		<div class="molochko-reviews-archive-container">
			<?php if ( have_posts() ) : ?>
				<div class="molochko-reviews-archive-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content', 'archive-reviews' );
					}
					?>
				</div>
				<?php
				echo '<nav class="molochko-reviews-archive-pagination" aria-label="' . esc_attr__( 'Навігація по відгуках', 'molochko' ) . '">';
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . __( 'Назад', 'molochko' ),
					'next_text' => __( 'Далі', 'molochko' ) . ' <i class="zmdi zmdi-chevron-right"></i>',
				) );
				echo '</nav>';
				?>
			<?php else : ?>
				<div class="molochko-reviews-archive-empty">
					<p class="molochko-reviews-archive-empty-text"><?php esc_html_e( 'Відгуків поки немає.', 'molochko' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="molochko-reviews-archive-empty-btn"><?php esc_html_e( 'На головну', 'molochko' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
