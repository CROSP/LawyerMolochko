<?php
/**
 * Single Practice Area content – hero, content block, CTA, navigation.
 *
 * @package Molochko
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$thumb_id     = get_post_thumbnail_id();
$archive_url  = get_post_type_archive_link( 'pxl-practice-area' );
$consult_url  = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_consultation_url' ) : '';
$consult_url  = $consult_url ?: '#contact';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'pa-single-article' ); ?>>
	<!-- Hero -->
	<header class="pa-single-hero">
		<?php if ( $thumb_id ) : ?>
			<div class="pa-single-hero-bg">
				<?php echo wp_get_attachment_image( $thumb_id, 'full', false, array( 'loading' => 'eager' ) ); ?>
				<div class="pa-single-hero-overlay"></div>
			</div>
		<?php else : ?>
			<div class="pa-single-hero-pattern" aria-hidden="true"></div>
			<div class="pa-single-hero-overlay pa-single-hero-overlay-solid"></div>
		<?php endif; ?>
		<div class="pa-single-hero-inner">
			<div class="pa-single-hero-content">
				<?php if ( $archive_url ) : ?>
					<a href="<?php echo esc_url( $archive_url ); ?>" class="pa-single-back">
						<i class="zmdi zmdi-arrow-left" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Усі напрямки практики', 'molochko' ); ?></span>
					</a>
				<?php endif; ?>
				<h1 class="pa-single-title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="pa-single-lead"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<!-- Content -->
	<div class="pa-single-body">
		<div class="pa-single-content-wrap">
			<div class="pa-single-content entry-content">
				<?php
				the_content();
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Сторінки:', 'molochko' ) . ' ',
					'after'  => '</div>',
				) );
				?>
			</div>
		</div>

		<!-- CTA -->
		<div class="pa-single-cta">
			<div class="pa-single-cta-inner">
				<p class="pa-single-cta-title"><?php esc_html_e( 'Потрібна консультація з цього напрямку?', 'molochko' ); ?></p>
				<p class="pa-single-cta-desc"><?php esc_html_e( 'Отримайте кваліфіковану допомогу адвоката. Зв\'яжіться з нами зручним способом.', 'molochko' ); ?></p>
				<a href="<?php echo esc_url( $consult_url ); ?>" class="pa-single-cta-btn">
					<span><?php esc_html_e( 'Замовити консультацію', 'molochko' ); ?></span>
					<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>

		<!-- Post nav -->
		<nav class="pa-single-nav" aria-label="<?php esc_attr_e( 'Навігація по напрямках', 'molochko' ); ?>">
			<?php
			the_post_navigation( array(
				'prev_text' => '<span class="pa-single-nav-label">' . __( 'Попередній напрямок', 'molochko' ) . '</span><span class="pa-single-nav-title">%title</span>',
				'next_text' => '<span class="pa-single-nav-label">' . __( 'Наступний напрямок', 'molochko' ) . '</span><span class="pa-single-nav-title">%title</span>',
			) );
			?>
		</nav>
	</div>
</article>
