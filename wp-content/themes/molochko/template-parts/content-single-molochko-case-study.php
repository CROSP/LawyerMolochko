<?php
/**
 * Single Case Study content – hero, content block, CTA, navigation.
 *
 * @package Molochko
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$thumb_id    = get_post_thumbnail_id();
$terms       = get_the_terms( get_the_ID(), 'case_study_category' );
$term        = is_array( $terms ) && ! empty( $terms ) ? $terms[0] : null;
$archive_url = get_post_type_archive_link( 'molochko-case-study' );
$consult_url = function_exists( 'molochko_get_contact_option' ) ? molochko_get_contact_option( 'header_consultation_url' ) : '';
$consult_url = $consult_url ?: '#consultation-popup';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'cs-single-article' ); ?>>
	<!-- Hero -->
	<header class="cs-single-hero">
		<?php if ( $thumb_id ) : ?>
			<div class="cs-single-hero-bg">
				<?php echo wp_get_attachment_image( $thumb_id, 'full', false, array( 'loading' => 'eager' ) ); ?>
				<div class="cs-single-hero-overlay"></div>
			</div>
		<?php endif; ?>
		<div class="cs-single-hero-inner">
			<div class="cs-single-hero-content">
				<?php if ( $term ) : ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="cs-single-category"><?php echo esc_html( $term->name ); ?></a>
				<?php endif; ?>
				<h1 class="cs-single-title"><?php the_title(); ?></h1>
				<div class="cs-single-meta">
					<span class="cs-single-date"><?php echo get_the_date(); ?></span>
				</div>
				<?php if ( $archive_url ) : ?>
					<a href="<?php echo esc_url( $archive_url ); ?>" class="cs-single-back">
						<i class="zmdi zmdi-arrow-left" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Усі кейси', 'molochko' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<!-- Content -->
	<div class="cs-single-body">
		<div class="cs-single-content-wrap">
			<div class="cs-single-content entry-content">
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
		<div class="cs-single-cta">
			<div class="cs-single-cta-inner">
				<p class="cs-single-cta-title"><?php esc_html_e( 'Потрібна консультація з вашого питання?', 'molochko' ); ?></p>
				<p class="cs-single-cta-desc"><?php esc_html_e( 'Отримайте кваліфіковану допомогу адвоката.', 'molochko' ); ?></p>
				<a href="<?php echo esc_url( $consult_url ); ?>" class="cs-single-cta-btn js-consultation-popup" data-popup="consultation">
					<span><?php esc_html_e( 'Замовити консультацію', 'molochko' ); ?></span>
					<i class="zmdi zmdi-long-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>

		<!-- Post nav -->
		<nav class="cs-single-nav" aria-label="<?php esc_attr_e( 'Навігація по кейсах', 'molochko' ); ?>">
			<?php
			the_post_navigation( array(
				'prev_text' => '<span class="cs-single-nav-label">' . __( 'Попередній кейс', 'molochko' ) . '</span><span class="cs-single-nav-title">%title</span>',
				'next_text' => '<span class="cs-single-nav-label">' . __( 'Наступний кейс', 'molochko' ) . '</span><span class="cs-single-nav-title">%title</span>',
			) );
			?>
		</nav>
	</div>
</article>
