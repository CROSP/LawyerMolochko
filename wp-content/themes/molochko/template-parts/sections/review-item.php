<?php
/**
 * Single review card for the reviews section.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = get_query_var( 'args', array() );
$review = isset( $args['review'] ) && is_array( $args['review'] ) ? $args['review'] : array();
if ( empty( $review ) || empty( $review['text'] ) ) {
	return;
}

$name                    = isset( $review['name'] ) ? $review['name'] : '';
$text                    = $review['text'];
$case_type               = isset( $review['case_type'] ) ? $review['case_type'] : '';
$case_study_archive_url  = isset( $review['case_study_archive_url'] ) ? $review['case_study_archive_url'] : '';
?>
<div class="molochko-review-slide">
	<article class="molochko-review-card">
		<div class="molochko-review-card-quote" aria-hidden="true">
			<i class="zmdi zmdi-quote" aria-hidden="true"></i>
		</div>
		<blockquote class="molochko-review-card-text"><?php echo esc_html( $text ); ?></blockquote>
		<footer class="molochko-review-card-footer">
			<?php if ( $name ) : ?>
				<cite class="molochko-review-card-name"><?php echo esc_html( $name ); ?></cite>
			<?php endif; ?>
			<?php if ( $case_type ) : ?>
				<span class="molochko-review-card-type"><?php echo esc_html( $case_type ); ?></span>
			<?php endif; ?>
		</footer>
		<?php if ( $case_study_archive_url ) : ?>
			<a href="<?php echo esc_url( $case_study_archive_url ); ?>" class="molochko-review-card-archive-link">
				<?php molochko_pll_esc_html_e( 'Переглянути кейси' ); ?>
				<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
	</article>
</div>
