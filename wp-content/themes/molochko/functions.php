<?php
/**
 * Molochko theme – no Elementor. ACF, vanilla CSS/JS.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOLOCHKO_VERSION', '1.0.0' );
define( 'MOLOCHKO_DIR', get_template_directory() );
define( 'MOLOCHKO_URI', get_template_directory_uri() );

/**
 * Theme setup
 */
add_action( 'after_setup_theme', 'molochko_setup' );
function molochko_setup() {
	$GLOBALS['content_width'] = 1200;
	load_theme_textdomain( 'molochko', MOLOCHKO_DIR . '/languages' );
	// Force-load theme Romanian .mo when Polylang language is Romanian (locale may be ro or ro_RO).
	add_filter( 'load_textdomain_mofile', 'molochko_load_romanian_mo', 10, 2 );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1170, 560, true );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 300,
		'flex-width'  => true,
		'flex-height' => true,
	) );
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'molochko' ),
	) );
}

/**
 * Use Polylang for translations when active; otherwise theme text domain.
 * When PLL returns the source (no translation), fall back to theme .po so RO strings
 * like "або" / "Записатися на консультацію" show in Romanian via molochko-ro_RO.mo.
 * Use these everywhere instead of __( '...', 'molochko' ) / esc_html_e( '...', 'molochko' ).
 */
function molochko_pll__( $string ) {
	if ( ! function_exists( 'pll__' ) ) {
		return __( $string, 'molochko' );
	}
	$translated = pll__( $string );
	// PLL returns source when string is not in String translations; use theme .po.
	if ( $translated === $string || $translated === '' ) {
		$translated = __( $string, 'molochko' );
	}
	return $translated;
}

function molochko_pll_e( $string ) {
	if ( function_exists( 'pll_e' ) ) {
		pll_e( $string );
	} else {
		_e( $string, 'molochko' );
	}
}

function molochko_pll_esc_html__( $string ) {
	return esc_html( molochko_pll__( $string ) );
}

function molochko_pll_esc_html_e( $string ) {
	echo molochko_pll_esc_html__( $string );
}

function molochko_pll_esc_attr__( $string ) {
	return esc_attr( molochko_pll__( $string ) );
}

/**
 * Force-load theme Romanian .mo when current language is Romanian.
 * Ensures strings like "Замовити консультацію" translate to "Comandă consultanță" on /ro/ pages.
 *
 * @param string $mofile Path to the .mo file.
 * @param string $domain Text domain.
 * @return string
 */
function molochko_load_romanian_mo( $mofile, $domain ) {
	if ( $domain !== 'molochko' ) {
		return $mofile;
	}
	if ( function_exists( 'pll_current_language' ) && pll_current_language( 'slug' ) === 'ro' ) {
		$ro_file = MOLOCHKO_DIR . '/languages/molochko-ro_RO.mo';
		if ( file_exists( $ro_file ) ) {
			return $ro_file;
		}
	}
	return $mofile;
}

/**
 * Polylang: output current language flag for dropdown (avoids walker-dropdown.php bug when selected URL has no match).
 */
function molochko_pll_current_flag() {
	if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
		return '';
	}
	$slug = pll_current_language( 'slug' );
	if ( ! $slug ) {
		return '';
	}
	$languages = pll_the_languages( array( 'raw' => 1, 'show_flags' => 1 ) );
	if ( empty( $languages ) || ! is_array( $languages ) ) {
		return '';
	}
	foreach ( $languages as $lang ) {
		if ( ! empty( $lang['current_lang'] ) || ( ! empty( $lang['slug'] ) && $lang['slug'] === $slug ) ) {
			$flag = isset( $lang['flag'] ) ? $lang['flag'] : '';
			if ( ! $flag ) {
				return '';
			}
			// When raw + show_flags=0 Polylang returns URL string; ensure we output an img.
			if ( is_string( $flag ) && ( strpos( $flag, 'http://' ) === 0 || strpos( $flag, 'https://' ) === 0 ) ) {
				$flag = '<img src="' . esc_url( $flag ) . '" alt="" width="24" height="18" loading="lazy" />';
			}
			$html = '<span class="pll-select-flag">' . $flag . '</span>';
			return function_exists( 'molochko_fix_content_image_urls' ) ? molochko_fix_content_image_urls( $html ) : $html;
		}
	}
	return '';
}

/**
 * Disable comments for all posts and pages (front-end and admin).
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_action( 'admin_init', 'molochko_disable_comments_support' );
function molochko_disable_comments_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );
}

/**
 * Fix image URLs: replace any stored site URL with current request URL so images
 * load on both lawyermolochko.ddev.site and lawyer-molochko.com.ua.
 */
add_filter( 'body_class', 'molochko_body_classes', 10, 1 );
function molochko_body_classes( $classes ) {
	if ( is_page_template( 'page-contact.php' ) ) {
		$classes[] = 'page-contact';
	}
	if ( is_front_page() ) {
		$classes[] = 'front-page';
	}
	return $classes;
}

add_filter( 'the_content', 'molochko_fix_content_image_urls', 20 );
add_filter( 'post_thumbnail_url', 'molochko_fix_attachment_url', 10, 3 );
add_filter( 'wp_get_attachment_url', 'molochko_fix_attachment_url_single', 10, 2 );
add_filter( 'wp_calculate_image_srcset', 'molochko_fix_srcset_urls', 10, 5 );

function molochko_fix_content_image_urls( $content ) {
	if ( ! is_string( $content ) || '' === $content ) {
		return $content;
	}
	$current = home_url( '/' );
	$bases   = array(
		'https://lawyer-molochko.com.ua:8443/',
		'http://lawyer-molochko.com.ua:8443/',
		'https://lawyermolochko.ddev.site:8443/',
		'http://lawyermolochko.ddev.site:8443/',
		'https://lawyermolochko.ddev.site:8080/',
		'http://lawyermolochko.ddev.site:8080/',
	);
	foreach ( $bases as $base ) {
		if ( $base !== $current && strpos( $content, $base ) !== false ) {
			$content = str_replace( $base, $current, $content );
		}
	}
	$content = preg_replace( '#(https?://[^/]+)/+#', '$1/', $content );
	return $content;
}

function molochko_fix_attachment_url( $url, $post_id, $size ) {
	return molochko_normalize_media_url( $url );
}

function molochko_fix_attachment_url_single( $url, $attachment_id ) {
	return molochko_normalize_media_url( $url );
}

/**
 * Root site URL for media (wp-content/uploads). Uses current request host (including port)
 * so images load when the site is accessed via a non-default port (e.g. :8443).
 * Avoids language path (e.g. /ro/home/) so media always points to root.
 */
function molochko_get_media_root_url() {
	if ( ! empty( $_SERVER['HTTP_HOST'] ) && is_string( $_SERVER['HTTP_HOST'] ) ) {
		$scheme = is_ssl() ? 'https' : 'http';
		return $scheme . '://' . $_SERVER['HTTP_HOST'] . '/';
	}
	return untrailingslashit( get_option( 'siteurl' ) ) . '/';
}

function molochko_normalize_media_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}
	$current = molochko_get_media_root_url();
	$bases   = array(
		'https://lawyer-molochko.com.ua:8443',
		'http://lawyer-molochko.com.ua:8443',
		'https://lawyermolochko.ddev.site:8443',
		'http://lawyermolochko.ddev.site:8443',
		'https://lawyermolochko.ddev.site:8080',
		'http://lawyermolochko.ddev.site:8080',
		// No-port variants (e.g. WordPress srcset can emit these; must match siteurl so images load on :8443).
		'https://lawyermolochko.ddev.site',
		'http://lawyermolochko.ddev.site',
	);
	foreach ( $bases as $base ) {
		if ( strpos( $url, $base ) === 0 ) {
			$path = substr( $url, strlen( $base ) );
			$path = '/' . ltrim( $path, '/' );
			// Media lives at root: strip any language path (e.g. /ro/home/) so URL is .../wp-content/uploads/...
			$wp_content = strpos( $path, '/wp-content/' );
			if ( $wp_content !== false ) {
				$path = substr( $path, $wp_content );
			}
			return rtrim( $current, '/' ) . $path;
		}
	}
	return $url;
}

function molochko_fix_srcset_urls( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( ! is_array( $sources ) ) {
		return $sources;
	}
	foreach ( $sources as $width => $data ) {
		if ( ! empty( $data['url'] ) ) {
			$sources[ $width ]['url'] = molochko_normalize_media_url( $data['url'] );
		}
	}
	return $sources;
}

/**
 * Enqueue scripts and styles
 */
add_action( 'wp_enqueue_scripts', 'molochko_scripts' );
function molochko_scripts() {
	// Tailwind CSS (CDN build) so utility classes like text-neutral-500 work.
	wp_enqueue_style(
		'molochko-tailwind',
		'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
		array(),
		'2.2.19'
	);

	wp_enqueue_style( 'molochko-flaticon', MOLOCHKO_URI . '/assets/fonts/flaticon/css/flaticon.css', array(), MOLOCHKO_VERSION );
	wp_enqueue_style( 'molochko-material', MOLOCHKO_URI . '/assets/fonts/material/css/font-material.min.css', array(), MOLOCHKO_VERSION );
	wp_enqueue_style( 'molochko-grid', MOLOCHKO_URI . '/assets/css/grid.css', array(), MOLOCHKO_VERSION );
	wp_enqueue_style(
		'molochko-theme',
		MOLOCHKO_URI . '/assets/css/theme.css',
		array( 'molochko-tailwind', 'molochko-grid', 'molochko-flaticon' ),
		MOLOCHKO_VERSION
	);

	wp_enqueue_script( 'jquery' );

	// Slick carousel for Case Studies on front page
	$theme_deps = array( 'jquery' );
	if ( is_front_page() ) {
		wp_enqueue_style(
			'slick-carousel',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css',
			array(),
			'1.8.1'
		);
		wp_enqueue_style(
			'slick-carousel-theme',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.min.css',
			array( 'slick-carousel' ),
			'1.8.1'
		);
		wp_enqueue_script(
			'slick-carousel',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
			array( 'jquery' ),
			'1.8.1',
			true
		);
		$theme_deps[] = 'slick-carousel';
	}
	wp_enqueue_script( 'molochko-theme', MOLOCHKO_URI . '/assets/js/theme.js', $theme_deps, MOLOCHKO_VERSION, true );
}

/**
 * CSS variables and overrides (replaces Redux/theme options)
 */
function molochko_inline_css() {
	$primary   = get_theme_mod( 'molochko_primary_color', '#ad9779' );
	$secondary = get_theme_mod( 'molochko_secondary_color', '#1a243f' );
	$heading   = get_theme_mod( 'molochko_heading_color', '#10172c' );
	$body      = get_theme_mod( 'molochko_body_color', '#6d6d6d' );

	$pri_rgb = implode( ',', molochko_hex_to_rgb( $primary ) );
	$sec_rgb = implode( ',', molochko_hex_to_rgb( $secondary ) );
	$h_rgb   = implode( ',', molochko_hex_to_rgb( $heading ) );

	// Revolution Slider revicons font
	$revicons_url = '';
	if ( defined( 'RS_PLUGIN_URL' ) ) {
		$revicons_url = RS_PLUGIN_URL . 'sr6/assets/fonts/revicons/';
	} elseif ( file_exists( WP_PLUGIN_DIR . '/revslider/sr6/assets/fonts/revicons/revicons.woff' ) ) {
		$revicons_url = plugins_url( 'revslider/sr6/assets/fonts/revicons/' );
	} elseif ( file_exists( WP_PLUGIN_DIR . '/revslider/public/css/fonts/revicons/fonts/revicons.woff' ) ) {
		$revicons_url = plugins_url( 'revslider/public/css/fonts/revicons/fonts/' );
	}

	$css = ":root {
		--primary-color: {$primary};
		--primary-color-rgb: {$pri_rgb};
		--secondary-color: {$secondary};
		--secondary-color-rgb: {$sec_rgb};
		--heading-color: {$heading};
		--heading-color-rgb: {$h_rgb};
		--body-color: {$body};
		--body-font-family: 'Poppins', sans-serif;
		--heading-font-family: 'Poppins', sans-serif;
	}
	html, body { font-family: var(--body-font-family); font-weight: 300; }
	h1, h2, h3, h4, h5, h6 { font-family: var(--heading-font-family); font-weight: 300; }";

	if ( $revicons_url ) {
		$css .= "
	@font-face {
		font-family: 'revicons';
		src: url('{$revicons_url}revicons.eot?5510888');
		src: url('{$revicons_url}revicons.eot?5510888#iefix') format('embedded-opentype'),
			 url('{$revicons_url}revicons.woff?5510888') format('woff'),
			 url('{$revicons_url}revicons.ttf?5510888') format('truetype'),
			 url('{$revicons_url}revicons.svg?5510888#revicons') format('svg');
		font-weight: normal;
		font-style: normal;
		font-display: swap;
	}";
	}

	// Flaticon font – absolute URLs so it always loads
	$flaticon = MOLOCHKO_URI . '/assets/fonts/flaticon/fonts/';
	$css .= "
	@font-face {
		font-family: 'flaticon';
		src: url('{$flaticon}flaticon.eot?31e7d0fb4ce63e77bd617f2ea8e601f3#iefix') format('embedded-opentype'),
			 url('{$flaticon}flaticon.woff2?31e7d0fb4ce63e77bd617f2ea8e601f3') format('woff2'),
			 url('{$flaticon}flaticon.woff?31e7d0fb4ce63e77bd617f2ea8e601f3') format('woff'),
			 url('{$flaticon}flaticon.ttf?31e7d0fb4ce63e77bd617f2ea8e601f3') format('truetype'),
			 url('{$flaticon}flaticon.svg?31e7d0fb4ce63e77bd617f2ea8e601f3#flaticon') format('svg');
		font-weight: normal;
		font-style: normal;
		font-display: swap;
	}";

	return $css;
}

function molochko_hex_to_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
	}
	return array_map( 'hexdec', str_split( $hex, 2 ) );
}

/**
 * Customizer: colors
 */
add_action( 'customize_register', 'molochko_customize_register' );
function molochko_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section( 'molochko_colors', array(
		'title'    => __( 'Molochko Colors', 'molochko' ),
		'priority' => 30,
	) );
	$wp_customize->add_setting( 'molochko_primary_color', array( 'default' => '#ad9779', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_setting( 'molochko_secondary_color', array( 'default' => '#1a243f', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_setting( 'molochko_heading_color', array( 'default' => '#10172c', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_setting( 'molochko_body_color', array( 'default' => '#6d6d6d', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_primary_color', array( 'label' => __( 'Primary', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_secondary_color', array( 'label' => __( 'Secondary', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_heading_color', array( 'label' => __( 'Heading', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_body_color', array( 'label' => __( 'Body', 'molochko' ), 'section' => 'molochko_colors' ) ) );
}

/**
 * Sidebar
 */
add_action( 'widgets_init', 'molochko_widgets_init' );
function molochko_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Blog Sidebar', 'molochko' ),
		'id'            => 'sidebar-blog',
		'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-content">',
		'after_widget'  => '</div></section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Page Sidebar', 'molochko' ),
		'id'            => 'sidebar-page',
		'before_widget' => '<section id="%1$s" class="widget %2$s"><div class="widget-content">',
		'after_widget'  => '</div></section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );
}

/**
 * Page title helper (for non–front pages)
 */
function molochko_page_title() {
	if ( is_404() ) {
		return __( '404', 'molochko' );
	}
	if ( is_search() ) {
		return __( 'Search results', 'molochko' );
	}
	if ( is_home() && ! is_front_page() ) {
		$id = (int) get_option( 'page_for_posts' );
		return $id ? get_the_title( $id ) : __( 'Blog', 'molochko' );
	}
	if ( is_singular() ) {
		return get_the_title();
	}
	if ( is_archive() ) {
		return get_the_archive_title();
	}
	return get_bloginfo( 'name' );
}

/**
 * Reusable section heading block.
 *
 * Outputs the same structure as the original Elementor heading:
 * - optional subtitle (small uppercase line)
 * - main H2 title (can contain <br>)
 * - optional description text under the title
 * - optional divider line
 *
 * @param array $args {
 *   @type string $subtitle    Small text above the title.
 *   @type string $title       Main H2 title (HTML allowed for <br>).
 *   @type string $description Optional paragraph text below the title.
 *   @type string $align       'left' or 'center'. Default 'center'.
 *   @type bool   $divider     Whether to render a divider line. Default true.
 * }
 */
function molochko_section_heading( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'subtitle'    => '',
			'title'       => '',
			'description' => '',
			'align'       => 'center',
			'divider'     => true,
		)
	);
	get_template_part( 'template-parts/sections/section-heading', null, $args );
}

/**
 * Render one Fancy Box layout-4 (same HTML as Powerlegal)
 *
 * @param array $b Keys: icon, title, description, button_text, button_url, button_target, image (id or array)
 */
function molochko_fancy_box_layout4( $b ) {
	$b = wp_parse_args( $b, array(
		'icon'          => 'flaticon flaticon-calling',
		'icon_image'    => null,
		'title'         => '',
		'description'   => '',
		'button_text'   => '',
		'button_url'    => '#',
		'button_target' => '_self',
		'link'          => null,
		'image'         => null,
	) );
	if ( ! empty( $b['link'] ) && is_array( $b['link'] ) ) {
		$b['button_url']    = $b['link']['url'] ?? $b['button_url'];
		$b['button_target'] = $b['link']['target'] ?? $b['button_target'];
		$b['button_text']   = $b['button_text'] ?: ( $b['link']['title'] ?? '' );
	}

	$icon_html = '';
	if ( ! empty( $b['icon_image'] ) ) {
		$iid = is_array( $b['icon_image'] ) ? (int) ( $b['icon_image']['id'] ?? $b['icon_image'] ) : (int) $b['icon_image'];
		if ( $iid ) {
			$icon_html = wp_get_attachment_image( $iid, 'thumbnail', false, array( 'class' => 'pxl-fancy-icon pxl-icon' ) );
		}
	}
	if ( ! $icon_html ) {
		$icon_html = '<i class="' . esc_attr( is_string( $b['icon'] ) ? $b['icon'] : 'flaticon flaticon-calling' ) . ' pxl-fancy-icon pxl-icon"></i>';
	}
	$b['icon_html'] = $icon_html;

	$img = '';
	if ( ! empty( $b['image'] ) ) {
		$id = is_array( $b['image'] ) ? (int) ( $b['image']['id'] ?? $b['image'] ) : (int) $b['image'];
		if ( $id ) {
			$img = wp_get_attachment_image( $id, 'full', false, array( 'class' => '' ) );
		}
	}
	$b['image_html'] = $img;

	set_query_var( 'args', $b );
	get_template_part( 'template-parts/sections/fancy-box-layout4' );
}

/**
 * Practice Areas section: heading + grid from pxl-practice-area CPT only.
 */
function molochko_practice_areas_section() {
	$query_args = array(
		'post_type'      => 'pxl-practice-area',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) {
			$query_args['lang'] = $lang;
		}
	}
	$query = new WP_Query( $query_args );
	$posts       = $query->have_posts() ? $query->posts : array();
	$button_text = molochko_pll__( 'Детальніше' );
	$num_words   = 15;

	set_query_var( 'args', array(
		'posts'         => $posts,
		'items'         => array(),
		'use_repeater'  => false,
		'button_text'   => $button_text,
		'num_words'     => $num_words,
	) );
	get_template_part( 'template-parts/sections/practice-areas-section' );
	wp_reset_postdata();
}

/**
 * Case Studies section: heading + grid from molochko-case-study CPT.
 */
function molochko_case_studies_section() {
	$query = new WP_Query( array(
		'post_type'      => 'molochko-case-study',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$posts = $query->have_posts() ? $query->posts : array();

	set_query_var( 'args', array( 'posts' => $posts ) );
	get_template_part( 'template-parts/sections/case-studies-section' );
	wp_reset_postdata();
}

/**
 * Reviews section: client testimonials carousel. Data from molochko_get_reviews().
 */
function molochko_reviews_section() {
	$reviews = function_exists( 'molochko_get_reviews' ) ? molochko_get_reviews() : array();
	set_query_var( 'args', array( 'reviews' => $reviews ) );
	get_template_part( 'template-parts/sections/reviews-section' );
}

/**
 * Law Talk section: blog/social about law — ACF on front page + contact options (Instagram/TikTok).
 */
function molochko_law_talk_section() {
	get_template_part( 'template-parts/sections/law-talk-section' );
}

/**
 * Front page contact form section (id="contact"). Dark CTA strip + light form card.
 */
function molochko_front_contact_section() {
	$cf7_shortcode = function_exists( 'molochko_get_contact_form_shortcode' ) ? molochko_get_contact_form_shortcode() : '';
	set_query_var( 'args', array( 'cf7_shortcode' => $cf7_shortcode ) );
	get_template_part( 'template-parts/sections/front-contact-section' );
}

/**
 * Blog section: recent posts carousel on front page.
 */
function molochko_blog_section() {
	$query = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$posts = $query->have_posts() ? $query->posts : array();
	set_query_var( 'args', array( 'posts' => $posts ) );
	get_template_part( 'template-parts/sections/blog-section' );
	wp_reset_postdata();
}

/**
 * Render About block (two columns: images left, text right). Data from ACF.
 *
 * @param int $post_id Front page or post ID.
 */
function molochko_about_block( $post_id = 0 ) {
	if ( ! $post_id ) {
		return;
	}
	$bg     = get_field( 'about_image_bg', $post_id );
	$person = get_field( 'about_image_person', $post_id );
	$sub    = get_field( 'about_subtitle', $post_id );
	$title  = get_field( 'about_title', $post_id );
	$desc   = get_field( 'about_description', $post_id );
	$cta    = get_field( 'about_cta_line', $post_id );
	$name   = get_field( 'about_name', $post_id );
	$role   = get_field( 'about_role', $post_id );

	if ( ! $sub && ! $title && ! $desc ) {
		return;
	}

	$bg_id     = is_array( $bg ) ? (int) ( $bg['id'] ?? 0 ) : (int) $bg;
	$person_id = is_array( $person ) ? (int) ( $person['id'] ?? 0 ) : (int) $person;
	$phone     = molochko_get_contact_option( 'header_phone' ) ?: '+38 (050) 606-00-79';
	$tel_href  = 'tel:' . preg_replace( '/\D+/', '', $phone );
	$book_url  = esc_url( home_url( '/book-appointment' ) );

	set_query_var( 'args', array(
		'bg_id'     => $bg_id,
		'person_id' => $person_id,
		'sub'       => $sub,
		'title'     => $title,
		'desc'      => $desc,
		'cta'       => $cta,
		'phone'     => $phone,
		'tel_href'  => $tel_href,
		'book_url'  => $book_url,
		'name'      => $name,
		'role'      => $role,
	) );
	get_template_part( 'template-parts/sections/about-block' );
}

// ACF Options page "Контакти" and helper molochko_get_contact_option().
require_once MOLOCHKO_DIR . '/inc/contact-options.php';
// Contact Form 7 shortcode helper for contact page and consultation popup.
require_once MOLOCHKO_DIR . '/inc/contact-form7-helper.php';
// Reviews section: default thematic reviews (filter: molochko_reviews_list).
require_once MOLOCHKO_DIR . '/inc/reviews-data.php';
// Підключення CPT «Напрямки практики» (ACF групи полів — тільки в БД, створюються скриптом import-acf-groups.php).
require_once MOLOCHKO_DIR . '/inc/acf-practice-area-cpt.php';
// Кейси (Case Studies) — CPT + таксономія категорій.
require_once MOLOCHKO_DIR . '/inc/acf-case-study-cpt.php';
// Reviews CPT: archive at /reviews/, use case_study_category for Case Category.
require_once MOLOCHKO_DIR . '/inc/reviews-cpt.php';
// Reviews: ACF field "Related Case Study" – link to case study archive.
require_once MOLOCHKO_DIR . '/inc/acf-reviews-case-study-field.php';
// Law Talk: fetch latest Instagram Reels (Graph API).
require_once MOLOCHKO_DIR . '/inc/law-talk-instagram.php';

/**
 * Google Fonts
 */
add_action( 'wp_enqueue_scripts', 'molochko_google_fonts', 5 );
function molochko_google_fonts() {
	wp_enqueue_style( 'molochko-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap', array(), null );
}
