<?php
/**
 * Archive template for Practice Areas (Напрямки практики).
 * Full-width hero + grid of practice area cards.
 *
 * @package Molochko
 */

get_header();

$archive_title    = __( 'Напрямки практики', 'molochko' );
$archive_subtitle = __( 'Наша експертиза', 'molochko' );
$archive_desc     = __( 'Надаємо кваліфіковану юридичну допомогу у різних галузях права. Кримінальні справи, ДТП, військові питання, сімейні та трудові спори, бізнес — індивідуальний підхід до кожної справи.', 'molochko' );
?>
<div class="pa-archive">
	<!-- Hero -->
	<header class="pa-archive-hero">
		<div class="pa-archive-hero-pattern" aria-hidden="true"></div>
		<div class="pa-archive-hero-overlay"></div>
		<div class="pa-archive-hero-inner">
			<p class="pa-archive-hero-subtitle"><?php echo esc_html( $archive_subtitle ); ?></p>
			<h1 class="pa-archive-hero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<p class="pa-archive-hero-desc"><?php echo esc_html( $archive_desc ); ?></p>
		</div>
	</header>

	<!-- Content -->
	<div class="pa-archive-main">
		<div class="pa-archive-container">
			<?php if ( have_posts() ) : ?>
				<div class="pa-archive-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content', 'archive-pxl-practice-area' );
					}
					?>
				</div>
			<?php else : ?>
				<div class="pa-archive-empty">
					<p class="pa-archive-empty-text"><?php esc_html_e( 'Напрямків поки немає.', 'molochko' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pa-archive-empty-btn"><?php esc_html_e( 'На головну', 'molochko' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
