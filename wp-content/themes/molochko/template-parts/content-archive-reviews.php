<?php
/**
 * Single review card for Reviews archive grid.
 *
 * @package Molochko
 */

$review_id = get_the_ID();
$name      = get_field( 'person_name', $review_id );
if ( $name === null || $name === false || $name === '' ) {
	$name = get_the_title();
}
$text      = get_the_content();
$case_type = function_exists( 'molochko_review_case_type_label' ) ? molochko_review_case_type_label( $review_id ) : '';
$case_study_archive_url = get_post_type_archive_link( 'molochko-case-study' );
if ( empty( $text ) ) {
	return;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'molochko-review-archive-card' ); ?>>
	<div class="molochko-review-card-quote" aria-hidden="true">
		<i class="zmdi zmdi-quote" aria-hidden="true"></i>
	</div>
	<blockquote class="molochko-review-card-text"><?php echo wp_kses_post( $text ); ?></blockquote>
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
			<?php esc_html_e( 'Переглянути кейси', 'molochko' ); ?>
			<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
		</a>
	<?php endif; ?>
</article>
