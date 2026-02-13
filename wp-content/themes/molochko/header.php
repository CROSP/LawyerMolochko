<?php
/**
 * @package Molochko
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="pxl-page" class="pxl-page">
	<div class="pxl-page-overlay"></div>
	<?php get_template_part( 'template-parts/header' ); ?>
	<?php if ( ! is_front_page() && ! is_singular( 'molochko-case-study' ) && ! is_singular( 'pxl-practice-area' ) && ! is_singular( 'post' ) && ! is_post_type_archive( 'molochko-case-study' ) && ! is_post_type_archive( 'pxl-practice-area' ) && ! is_home() && ! is_tax( 'case_study_category' ) && ! is_page_template( 'page-contact.php' ) ) : ?>
		<div id="pxl-pagetitle" class="pxl-pagetitle bg-image layout-df relative">
			<div class="pxl-page-title-overlay"></div>
			<div class="container relative">
				<div class="pxl-page-title-inner text-center">
					<div class="pxl-page-title col-12">
						<h1 class="main-title"><?php echo esc_html( molochko_page_title() ); ?></h1>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div id="pxl-main" class="pxl-main">
