<?php
/**
 * Taxonomy archive for Case Study Category (e.g. /case-study-category/vijskove-pravo/).
 * Same design as case study archive: hero + filter pills + grid of cards.
 *
 * @package Molochko
 */

get_header();

$term = get_queried_object();
if ( ! $term || ! isset( $term->term_id ) ) {
	get_template_part( 'archive', 'molochko-case-study' );
	return;
}

$archive_subtitle = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Практичний досвід' ) : __( 'Практичний досвід', 'molochko' );
$archive_title   = $term->name;
$archive_desc    = $term->description ? $term->description : ( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Кейси за цією категорією.' ) : __( 'Кейси за цією категорією.', 'molochko' ) );

$categories = get_terms( array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => true,
) );
$current_term_id = $term->term_id;
?>
<div class="molochko-case-study-archive">
	<header class="cs-archive-hero">
		<div class="cs-archive-hero-pattern" aria-hidden="true"></div>
		<div class="cs-archive-hero-overlay"></div>
		<div class="cs-archive-hero-inner">
			<p class="cs-archive-hero-subtitle"><?php echo esc_html( $archive_subtitle ); ?></p>
			<h1 class="cs-archive-hero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<?php if ( $archive_desc ) : ?>
				<p class="cs-archive-hero-desc"><?php echo wp_kses_post( $archive_desc ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<div class="cs-archive-main">
		<div class="cs-archive-container">
			<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
			<nav class="cs-archive-filters" aria-label="<?php esc_attr_e( 'Фільтр за категоріями', 'molochko' ); ?>">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'molochko-case-study' ) ); ?>" class="cs-archive-filter-pill">
					<?php esc_html_e( 'Усі', 'molochko' ); ?>
				</a>
				<?php foreach ( $categories as $t ) : ?>
					<a href="<?php echo esc_url( get_term_link( $t ) ); ?>" class="cs-archive-filter-pill<?php echo (int) $t->term_id === (int) $current_term_id ? ' active' : ''; ?>">
						<?php echo esc_html( $t->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="cs-archive-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content', 'archive-molochko-case-study' );
					}
					?>
				</div>
				<?php
				echo '<nav class="cs-archive-pagination" aria-label="' . esc_attr__( 'Навігація по кейсах', 'molochko' ) . '">';
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . __( 'Назад', 'molochko' ),
					'next_text' => __( 'Далі', 'molochko' ) . ' <i class="zmdi zmdi-chevron-right"></i>',
				) );
				echo '</nav>';
				?>
			<?php else : ?>
				<div class="cs-archive-empty">
					<p class="cs-archive-empty-text"><?php esc_html_e( 'У цій категорії кейсів поки немає.', 'molochko' ); ?></p>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'molochko-case-study' ) ); ?>" class="cs-archive-empty-btn"><?php esc_html_e( 'Усі кейси', 'molochko' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
