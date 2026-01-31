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
 * Register pxl-practice-area post type (same slug as Powerlegal for content compatibility).
 */
add_action( 'init', 'molochko_register_practice_area_cpt' );
function molochko_register_practice_area_cpt() {
	if ( post_type_exists( 'pxl-practice-area' ) ) {
		return;
	}
	register_post_type( 'pxl-practice-area', array(
		'labels'             => array(
			'name'               => __( 'Напрямки практики', 'molochko' ),
			'singular_name'      => __( 'Напрямок практики', 'molochko' ),
			'add_new'            => __( 'Додати', 'molochko' ),
			'add_new_item'       => __( 'Додати напрямок практики', 'molochko' ),
			'edit_item'          => __( 'Редагувати', 'molochko' ),
			'view_item'          => __( 'Переглянути', 'molochko' ),
			'menu_name'          => __( 'Напрямки практики', 'molochko' ),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'practice-area' ),
		'supports'           => array( 'title', 'thumbnail', 'editor', 'excerpt' ),
		'menu_icon'          => 'dashicons-image-filter',
	) );
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
	wp_enqueue_script( 'molochko-theme', MOLOCHKO_URI . '/assets/js/theme.js', array( 'jquery' ), MOLOCHKO_VERSION, true );
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
	$wp_customize->add_setting( 'molochko_phone', array( 'default' => '+38(050)-606-00-79', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_primary_color', array( 'label' => __( 'Primary', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_secondary_color', array( 'label' => __( 'Secondary', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_heading_color', array( 'label' => __( 'Heading', 'molochko' ), 'section' => 'molochko_colors' ) ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'molochko_body_color', array( 'label' => __( 'Body', 'molochko' ), 'section' => 'molochko_colors' ) ) );

	$wp_customize->add_control(
		'molochko_phone',
		array(
			'label'       => __( 'Основний телефон (About блок)', 'molochko' ),
			'section'     => 'molochko_colors',
			'type'        => 'text',
			'description' => __( 'Використовується в блоці \"Про бюро\" та інших місцях сайту.', 'molochko' ),
		)
	);
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
 * Practice Areas section: heading + grid from ACF repeater (practice_areas) or pxl-practice-area posts.
 */
function molochko_practice_areas_section() {
	$fid          = (int) get_option( 'page_on_front' );
	$items        = $fid && function_exists( 'get_field' ) ? get_field( 'practice_areas', $fid ) : null;
	if ( $fid && function_exists( 'update_field' ) && ( empty( $items ) || ! is_array( $items ) ) ) {
		$data_file = MOLOCHKO_DIR . '/inc/practice-areas-data.php';
		if ( file_exists( $data_file ) ) {
			$default_items = include $data_file;
			if ( is_array( $default_items ) && ! empty( $default_items ) ) {
				update_field( 'practice_areas', $default_items, $fid );
				$items = $default_items;
			}
		}
	}
	$use_repeater = ! empty( $items ) && is_array( $items );
	$posts       = array();
	$button_text = __( 'Детальніше', 'molochko' );
	$num_words   = 15;

	if ( ! $use_repeater ) {
		$query = new WP_Query( array(
			'post_type'      => 'pxl-practice-area',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		$posts = $query->have_posts() ? $query->posts : array();
	}

	set_query_var( 'args', array(
		'posts'         => $posts,
		'items'         => $use_repeater ? $items : array(),
		'use_repeater'  => $use_repeater,
		'button_text'   => $button_text,
		'num_words'     => $num_words,
	) );
	get_template_part( 'template-parts/sections/practice-areas-section' );
	if ( ! $use_repeater ) {
		wp_reset_postdata();
	}
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
	$phone     = get_theme_mod( 'molochko_phone', '+38(050)-606-00-79' );
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

/**
 * ACF field groups (front page)
 */
add_action( 'acf/init', 'molochko_acf_front_page_fields' );
function molochko_acf_front_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	require_once MOLOCHKO_DIR . '/inc/acf-front-page.php';
}

/**
 * Google Fonts
 */
add_action( 'wp_enqueue_scripts', 'molochko_google_fonts', 5 );
function molochko_google_fonts() {
	wp_enqueue_style( 'molochko-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap', array(), null );
}
