<?php
/**
 * Archive template for Reviews CPT.
 * URL: /reviews/
 *
 * @package Molochko
 */

get_header();

$archive_title    = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Відгуки клієнтів' ) : __( 'Відгуки клієнтів', 'molochko' );
$archive_subtitle = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Що кажуть клієнти' ) : __( 'Що говорять клієнти', 'molochko' );
$archive_desc     = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Реальні історії та подяки від тих, хто звертався до нас за юридичною допомогою.' ) : __( 'Реальні історії та відгуки людей, яким ми допомогли.', 'molochko' );
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
				$nav_label = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Навігація по відгуках' ) : __( 'Навігація по відгуках', 'molochko' );
				$prev_text = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Назад' ) : __( 'Назад', 'molochko' );
				$next_text = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Далі' ) : __( 'Далі', 'molochko' );
				echo '<nav class="molochko-reviews-archive-pagination" aria-label="' . esc_attr( $nav_label ) . '">';
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . $prev_text,
					'next_text' => $next_text . ' <i class="zmdi zmdi-chevron-right"></i>',
				) );
				echo '</nav>';
				?>
			<?php else : ?>
				<div class="molochko-reviews-archive-empty">
					<p class="molochko-reviews-archive-empty-text"><?php echo esc_html( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Відгуків поки немає.' ) : __( 'Відгуків поки немає.', 'molochko' ) ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="molochko-reviews-archive-empty-btn"><?php echo esc_html( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'На головну' ) : __( 'На головну', 'molochko' ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
