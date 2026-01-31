<?php
/**
 * About block: two columns (images left, text right). Data from ACF.
 *
 * @package Molochko
 * @var int    $bg_id     Attachment ID for background image.
 * @var int    $person_id Attachment ID for person image.
 * @var string $sub       Subtitle.
 * @var string $title     Title.
 * @var string $desc      Description (HTML).
 * @var string $cta       CTA line.
 * @var string $phone     Phone number (from theme mod).
 * @var string $tel_href  tel: link.
 * @var string $book_url  URL for booking.
 * @var string $name      Signature name.
 * @var string $role      Signature role.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
if ( ! is_array( $args ) ) {
	$args = array();
}
$bg_id     = isset( $args['bg_id'] ) ? (int) $args['bg_id'] : 0;
$person_id = isset( $args['person_id'] ) ? (int) $args['person_id'] : 0;
$sub       = $args['sub'] ?? '';
$title     = $args['title'] ?? '';
$desc      = $args['desc'] ?? '';
$cta       = $args['cta'] ?? '';
$phone     = $args['phone'] ?? '';
$tel_href  = $args['tel_href'] ?? '';
$book_url  = $args['book_url'] ?? '';
$name      = $args['name'] ?? '';
$role      = $args['role'] ?? '';

if ( ! $sub && ! $title && ! $desc ) {
	return;
}
?>
<section class="molochko-about">
	<div class="container">
		<div class="row row-cols-1 row-cols-md-2 align-items-center">
			<div class="col">
				<div class="molochko-about-images">
					<?php if ( $bg_id ) : ?>
						<?php echo wp_get_attachment_image( $bg_id, 'full', false, array( 'class' => 'molochko-about-bg', 'alt' => '' ) ); ?>
					<?php endif; ?>
					<?php if ( $person_id ) : ?>
						<?php echo wp_get_attachment_image( $person_id, 'full', false, array( 'class' => 'molochko-about-person pxl-elementor-animate', 'alt' => '' ) ); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="col">
				<div class="molochko-about-content">
					<div class="molochko-about-spacer molochko-about-spacer--top"></div>
					<?php if ( $sub || $title ) : ?>
						<div class="pxl-heading-wrap d-flex layout1 pxl-heading-layout-1">
							<div class="pxl-heading-inner">
								<?php if ( $sub ) : ?>
									<div class="heading-subtitle"><span><?php echo esc_html( $sub ); ?></span></div>
								<?php endif; ?>
								<?php if ( $title ) : ?>
									<h2 class="heading-title"><span><?php echo esc_html( $title ); ?></span></h2>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $desc ) : ?>
						<div class="molochko-about-description pxl-text-editor"><?php echo wp_kses_post( $desc ); ?></div>
					<?php endif; ?>
					<?php if ( $cta ) : ?>
						<div class="molochko-about-cta pxl-text-editor"><p><?php echo esc_html( $cta ); ?></p></div>
					<?php endif; ?>

					<div class="molochko-about-contact pxl-text-editor">
						<p>
							<a href="<?php echo esc_url( $tel_href ); ?>"><?php echo esc_html( $phone ); ?></a>
							<?php esc_html_e( 'або', 'molochko' ); ?>
							<a href="<?php echo esc_url( $book_url ); ?>"><?php esc_html_e( 'Записатися на консультацію', 'molochko' ); ?></a>
						</p>
					</div>
					<div class="elementor-divider molochko-about-divider"><span class="elementor-divider-separator"></span></div>
					<div class="molochko-about-signature-meta d-flex flex-wrap align-items-center">
						<?php if ( $name || $role ) : ?>
							<div class="molochko-about-meta elementor-icon-box-wrapper">
								<?php if ( $name ) : ?>
									<div class="elementor-icon-box-title"><span><?php echo esc_html( $name ); ?></span></div>
								<?php endif; ?>
								<?php if ( $role ) : ?>
									<p class="elementor-icon-box-description"><?php echo esc_html( $role ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="molochko-about-spacer molochko-about-spacer--bottom"></div>
				</div>
			</div>
		</div>
	</div>
</section>
