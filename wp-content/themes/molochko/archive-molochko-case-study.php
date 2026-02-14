<?php
/**
 * Archive template for Case Studies (Кейси).
 * Full-width hero + category filter + grid of case study cards.
 *
 * @package Molochko
 */

get_header();

$archive_title    = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Кейси' ) : __( 'Кейси', 'molochko' );
$archive_subtitle = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Практичний досвід' ) : __( 'Практичний досвід', 'molochko' );
$archive_desc     = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Наша відданість справі та досвід дозволяють ефективно представляти ваші інтереси — від консультації до суду.' ) : __( 'Наша відданість справі та досвід дозволяють ефективно представляти ваші інтереси — від консультації до суду.', 'molochko' );

$categories = get_terms( array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => true,
) );
$current_cat = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
?>
<div class="molochko-case-study-archive">
	<!-- Hero -->
	<header class="cs-archive-hero">
		<div class="cs-archive-hero-pattern" aria-hidden="true"></div>
		<div class="cs-archive-hero-overlay"></div>
		<div class="cs-archive-hero-inner">
			<p class="cs-archive-hero-subtitle"><?php echo esc_html( $archive_subtitle ); ?></p>
			<h1 class="cs-archive-hero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<p class="cs-archive-hero-desc"><?php echo esc_html( $archive_desc ); ?></p>
		</div>
	</header>

	<!-- Content -->
	<div class="cs-archive-main">
		<div class="cs-archive-container">
			<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
			<nav class="cs-archive-filters" aria-label="<?php esc_attr_e( 'Фільтр за категоріями', 'molochko' ); ?>">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'molochko-case-study' ) ); ?>" class="cs-archive-filter-pill<?php echo $current_cat === '' ? ' active' : ''; ?>">
					<?php esc_html_e( 'Усі', 'molochko' ); ?>
				</a>
				<?php foreach ( $categories as $term ) : ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="cs-archive-filter-pill">
						<?php echo esc_html( $term->name ); ?>
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
					<p class="cs-archive-empty-text"><?php esc_html_e( 'Кейсів поки немає.', 'molochko' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cs-archive-empty-btn"><?php esc_html_e( 'На головну', 'molochko' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
