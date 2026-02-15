<?php
/**
 * Reviews section – client testimonials carousel. Data from molochko_get_reviews().
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reviews = get_query_var( 'args', array() );
$reviews = isset( $reviews['reviews'] ) && is_array( $reviews['reviews'] ) ? $reviews['reviews'] : array();

$subtitle = molochko_pll__( 'Що кажуть клієнти' );
$title    = molochko_pll__( 'Відгуки клієнтів' );
$desc     = molochko_pll__( 'Реальні історії та подяки від тих, хто звертався до нас за юридичною допомогою.' );
?>
<section id="reviews" class="molochko-reviews">
	<div class="molochko-reviews-bg" aria-hidden="true"></div>
	<div class="molochko-reviews-inner">
		<header class="molochko-reviews-header">
			<p class="molochko-reviews-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<h2 class="molochko-reviews-title"><?php echo esc_html( $title ); ?></h2>
			<p class="molochko-reviews-desc"><?php echo esc_html( $desc ); ?></p>
		</header>

		<?php if ( ! empty( $reviews ) ) : ?>
		<div class="molochko-reviews-carousel">
			<div class="molochko-reviews-carousel-nav" aria-hidden="true"></div>
			<div class="molochko-reviews-track">
				<?php
				foreach ( $reviews as $review ) {
					set_query_var( 'args', array( 'review' => $review ) );
					get_template_part( 'template-parts/sections/review-item' );
				}
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
