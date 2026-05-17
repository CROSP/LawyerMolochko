<?php
/**
 * Section heading block (subtitle, title, description, divider).
 *
 * @package Molochko
 * @var array $args {
 *   @type string $subtitle    Small text above the title.
 *   @type string $title       Main H2 title (HTML allowed for <br>).
 *   @type string $description Optional paragraph text below the title.
 *   @type string $align       'left' or 'center'. Default 'center'.
 *   @type bool   $divider     Whether to render a divider line. Default true.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = get_query_var( 'args', array() );
if ( isset( $args['args'] ) && is_array( $args['args'] ) ) {
	$args = $args['args'];
}
if ( ! is_array( $args ) ) {
	$args = array();
}
if ( empty( $args['subtitle'] ) && empty( $args['title'] ) && empty( $args['description'] ) ) {
	return;
}

$align_class = ( $args['align'] ?? 'center' ) === 'left' ? '' : 'center';
?>
<div class="molochko-section-heading">
	<div class="pxl-heading-wrap d-flex layout1 pxl-heading-layout-1">
		<div class="pxl-heading-inner <?php echo esc_attr( $align_class ); ?>">
			<?php if ( ! empty( $args['subtitle'] ) ) : ?>
				<div class="heading-subtitle">
					<span><?php echo esc_html( $args['subtitle'] ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $args['title'] ) ) : ?>
				<h2 class="heading-title">
					<span><?php echo wp_kses_post( $args['title'] ); ?></span>
				</h2>
			<?php endif; ?>

			<?php if ( ! empty( $args['description'] ) ) : ?>
				<div class="molochko-section-heading-desc pxl-text-editor">
					<p><?php echo wp_kses_post( $args['description'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $args['divider'] ) ) : ?>
				<div class="elementor-divider molochko-section-heading-divider">
					<span class="elementor-divider-separator"></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
