<?php
/**
 * Blog index (Posts page). Full-width hero + grid of post cards, no sidebar.
 *
 * @package Molochko
 */
get_header();

$archive_title    = __( 'Блог', 'molochko' );
$archive_subtitle = __( 'Про право та практику', 'molochko' );
$archive_desc     = __( 'Корисні статті про законодавство, ваші права та типові юридичні ситуації.', 'molochko' );
?>
<div class="molochko-blog-archive">
	<header class="blog-archive-hero">
		<div class="blog-archive-hero-pattern" aria-hidden="true"></div>
		<div class="blog-archive-hero-overlay"></div>
		<div class="blog-archive-hero-inner">
			<p class="blog-archive-hero-subtitle"><?php echo esc_html( $archive_subtitle ); ?></p>
			<h1 class="blog-archive-hero-title"><?php echo esc_html( $archive_title ); ?></h1>
			<p class="blog-archive-hero-desc"><?php echo esc_html( $archive_desc ); ?></p>
		</div>
	</header>

	<div class="blog-archive-main">
		<div class="blog-archive-container">
			<?php if ( have_posts() ) : ?>
				<div class="blog-archive-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content', 'blog' );
					}
					?>
				</div>
				<?php
				echo '<nav class="blog-archive-pagination" aria-label="' . esc_attr__( 'Навігація по сторінках', 'molochko' ) . '">';
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . __( 'Назад', 'molochko' ),
					'next_text' => __( 'Далі', 'molochko' ) . ' <i class="zmdi zmdi-chevron-right"></i>',
				) );
				echo '</nav>';
				?>
			<?php else : ?>
				<div class="blog-archive-empty">
					<p class="blog-archive-empty-text"><?php esc_html_e( 'Статей поки немає.', 'molochko' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="blog-archive-empty-btn"><?php esc_html_e( 'На головну', 'molochko' ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
