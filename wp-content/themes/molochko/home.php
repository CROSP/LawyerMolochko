<?php
/**
 * Blog index (Posts page). Full-width hero + grid of post cards, no sidebar.
 *
 * @package Molochko
 */
get_header();

$archive_title    = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Блог' ) : __( 'Блог', 'molochko' );
$archive_subtitle = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Про право та практику' ) : __( 'Про право та практику', 'molochko' );
$archive_desc     = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Корисні статті про законодавство, ваші права та типові юридичні ситуації.' ) : __( 'Корисні статті про законодавство, ваші права та типові юридичні ситуації.', 'molochko' );
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
				$nav_label = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Навігація по сторінках' ) : __( 'Навігація по сторінках', 'molochko' );
				$prev_text = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Назад' ) : __( 'Назад', 'molochko' );
				$next_text = function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Далі' ) : __( 'Далі', 'molochko' );
				echo '<nav class="blog-archive-pagination" aria-label="' . esc_attr( $nav_label ) . '">';
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => '<i class="zmdi zmdi-chevron-left"></i> ' . $prev_text,
					'next_text' => $next_text . ' <i class="zmdi zmdi-chevron-right"></i>',
				) );
				echo '</nav>';
				?>
			<?php else : ?>
				<div class="blog-archive-empty">
					<p class="blog-archive-empty-text"><?php echo esc_html( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'Статей поки немає.' ) : __( 'Статей поки немає.', 'molochko' ) ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="blog-archive-empty-btn"><?php echo esc_html( function_exists( 'molochko_pll__' ) ? molochko_pll__( 'На головну' ) : __( 'На головну', 'molochko' ) ); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
