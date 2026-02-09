<?php
/**
 * One-time script: create Molochko ACF field groups in the database.
 *
 * Run inside DDEV (DB must be reachable):
 *   ddev exec php import-acf-groups.php
 * Or: ddev ssh → cd /var/www/html → php import-acf-groups.php
 *
 * Or via WP-CLI:  ddev exec wp eval-file import-acf-groups.php
 *
 * Field groups are stored only in DB after this; theme does not register them in code.
 */

// Load WordPress when run as CLI script.
if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( __FILE__ ) . '/wp-load.php';
	if ( ! is_file( $wp_load ) ) {
		fwrite( STDERR, "Error: wp-load.php not found. Run from project root.\n" );
		exit( 1 );
	}
	require_once $wp_load;
}

if ( ! function_exists( 'acf_update_field_group' ) ) {
	fwrite( STDERR, "Error: ACF not active. Activate Advanced Custom Fields PRO.\n" );
	exit( 1 );
}

$is_cli = ( php_sapi_name() === 'cli' );
function _out( $msg ) {
	global $is_cli;
	if ( $is_cli ) {
		echo $msg . "\n";
	} else {
		echo '<p>' . esc_html( $msg ) . '</p>';
	}
}

// --- Front Page field group ---
$group_front_page = array(
	'key'                   => 'group_molochko_front_page',
	'title'                 => 'Головна сторінка (Molochko)',
	'fields'                => array(
		array(
			'key'           => 'field_molochko_hero_shortcode',
			'label'         => 'Шорткод слайдера (Hero)',
			'name'          => 'hero_slider_shortcode',
			'type'          => 'text',
			'instructions'  => 'Наприклад [rev_slider alias="slider-1"] або будь-який шорткод для блоку Hero.',
			'default_value' => '[rev_slider alias="slider-1"]',
			'placeholder'   => '[rev_slider alias="slider-1"]',
		),
		array(
			'key'        => 'field_molochko_fancy_boxes',
			'label'      => 'Картки (3 блоки)',
			'name'       => 'fancy_boxes',
			'type'       => 'repeater',
			'layout'     => 'block',
			'min'        => 0,
			'max'        => 6,
			'button_label' => 'Додати блок',
			'sub_fields' => array(
				array(
					'key'           => 'field_fb_icon',
					'label'         => 'Іконка (CSS класи)',
					'name'          => 'icon',
					'type'          => 'text',
					'instructions'  => 'Наприклад flaticon flaticon-calling або flaticon flaticon-award. Ігнорується, якщо задано зображення іконки.',
					'default_value' => 'flaticon flaticon-calling',
				),
				array(
					'key'           => 'field_fb_icon_image',
					'label'         => 'Іконка (зображення/SVG)',
					'name'          => 'icon_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'instructions'  => 'Опціонально. Якщо задано — використовується замість CSS-класів іконки.',
				),
				array(
					'key'   => 'field_fb_title',
					'label' => 'Заголовок',
					'name'  => 'title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_fb_description',
					'label' => 'Опис',
					'name'  => 'description',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'   => 'field_fb_button_text',
					'label' => 'Текст кнопки',
					'name'  => 'button_text',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_fb_link',
					'label' => 'Посилання',
					'name'  => 'link',
					'type'  => 'link',
				),
				array(
					'key'          => 'field_fb_image',
					'label'        => 'Зображення (опціонально)',
					'name'         => 'image',
					'type'         => 'image',
					'return_format' => 'array',
					'preview_size' => 'medium',
				),
			),
		),
		array(
			'key'   => 'field_molochko_about_tab',
			'label' => 'Блок «Про нас»',
			'name'  => 'about_tab',
			'type'  => 'tab',
		),
		array(
			'key'          => 'field_about_image_bg',
			'label'        => 'Про нас: фонове зображення (ліва колонка)',
			'name'         => 'about_image_bg',
			'type'         => 'image',
			'return_format' => 'array',
			'preview_size' => 'medium',
		),
		array(
			'key'          => 'field_about_image_person',
			'label'        => 'Про нас: фото персони/CEO (накладення)',
			'name'         => 'about_image_person',
			'type'         => 'image',
			'return_format' => 'array',
			'preview_size' => 'medium',
		),
		array(
			'key'   => 'field_about_subtitle',
			'label' => 'Про нас: підзаголовок',
			'name'  => 'about_subtitle',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_about_title',
			'label' => 'Про нас: заголовок (H2)',
			'name'  => 'about_title',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_about_description',
			'label' => 'Про нас: основний текст',
			'name'  => 'about_description',
			'type'  => 'wysiwyg',
		),
		array(
			'key'   => 'field_about_cta_line',
			'label' => 'Про нас: заклик до дії (напр. Дзвоніть 24/7...)',
			'name'  => 'about_cta_line',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_about_contact_line',
			'label' => 'Про нас: контактний рядок (телефон + посилання, дозволений HTML)',
			'name'  => 'about_contact_line',
			'type'  => 'wysiwyg',
		),
		array(
			'key'   => 'field_about_name',
			'label' => 'Про нас: ім\'я персони',
			'name'  => 'about_name',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_about_role',
			'label' => 'Про нас: посада',
			'name'  => 'about_role',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_molochko_practice_areas_tab',
			'label' => 'Секція «Напрямки практики»',
			'name'  => 'practice_areas_tab',
			'type'  => 'tab',
		),
		array(
			'key'           => 'field_practice_areas_subtitle',
			'label'         => 'Напрямки практики: підзаголовок',
			'name'          => 'practice_areas_subtitle',
			'type'          => 'text',
			'default_value' => 'Наша експертиза',
		),
		array(
			'key'           => 'field_practice_areas_title',
			'label'         => 'Напрямки практики: заголовок (H2)',
			'name'          => 'practice_areas_title',
			'type'          => 'text',
			'default_value' => 'Напрямки юридичної практики',
		),
		array(
			'key'           => 'field_practice_areas_description',
			'label'         => 'Напрямки практики: опис',
			'name'          => 'practice_areas_description',
			'type'          => 'textarea',
			'rows'          => 3,
			'default_value' => 'Надаємо кваліфіковану юридичну допомогу у кримінальних справах, спорах через ДТП, військових питаннях, сімейних та трудових спорах. Маємо значний досвід у цих напрямках та гарантуємо індивідуальний підхід до кожної справи.',
		),
		array(
			'key'           => 'field_practice_areas_items',
			'label'         => 'Напрямки практики: картки сітки',
			'name'          => 'practice_areas',
			'type'          => 'repeater',
			'layout'        => 'block',
			'min'           => 0,
			'max'           => 50,
			'button_label'  => 'Додати напрямок практики',
			'instructions'  => 'Якщо заповнено — цей список не використовується; секція показує записи типу «Напрямки практики» (pxl-practice-area).',
			'sub_fields'    => array(
				array(
					'key'   => 'field_pa_item_title',
					'label' => 'Заголовок',
					'name'  => 'title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_pa_item_description',
					'label' => 'Опис',
					'name'  => 'description',
					'type'  => 'textarea',
					'rows'  => 2,
				),
				array(
					'key'           => 'field_pa_item_icon',
					'label'         => 'Іконка (CSS клас)',
					'name'          => 'icon',
					'type'          => 'text',
					'default_value' => 'flaticon flaticon-businessman',
					'placeholder'   => 'flaticon flaticon-businessman',
				),
				array(
					'key'          => 'field_pa_item_icon_image',
					'label'        => 'Іконка (зображення, опціонально)',
					'name'         => 'icon_image',
					'type'         => 'image',
					'return_format' => 'array',
					'preview_size' => 'thumbnail',
				),
				array(
					'key'   => 'field_pa_item_link',
					'label' => 'Посилання',
					'name'  => 'link',
					'type'  => 'link',
				),
			),
		),
	),
	'location'              => array(
		array(
			array(
				'param'    => 'page_type',
				'operator' => '==',
				'value'    => 'front_page',
			),
		),
	),
	'menu_order'            => 0,
	'position'              => 'normal',
	'style'                 => 'default',
	'label_placement'       => 'top',
	'instruction_placement' => 'label',
	'active'                => true,
	'show_in_rest'          => 1,
	'description'           => 'Hero та картки для головної сторінки Molochko. Замінює Elementor на головній.',
);

// --- Practice Area field group ---
$group_practice_area = array(
	'key'                   => 'group_molochko_practice_area',
	'title'                 => 'Напрямок практики (поля картки)',
	'fields'                => array(
		array(
			'key'           => 'field_pa_area_icon_type',
			'label'         => 'Тип іконки',
			'name'          => 'area_icon_type',
			'type'          => 'select',
			'choices'       => array(
				''       => 'Іконка (CSS клас)',
				'image'  => 'Зображення',
			),
			'default_value' => '',
			'instructions'  => 'Як показувати іконку в картці на головній.',
		),
		array(
			'key'           => 'field_pa_area_icon',
			'label'         => 'Іконка (CSS класи)',
			'name'          => 'area_icon',
			'type'          => 'text',
			'placeholder'   => 'flaticon flaticon-businessman',
			'instructions'  => 'Наприклад: flaticon flaticon-medal, flaticon flaticon-idea. Використовується, якщо тип іконки не «Зображення».',
		),
		array(
			'key'           => 'field_pa_area_img',
			'label'         => 'Іконка (зображення)',
			'name'          => 'area_img',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'thumbnail',
			'instructions'  => 'Опціонально. Якщо тип іконки «Зображення» або потрібна своя картинка замість CSS-іконки.',
		),
	),
	'location'              => array(
		array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'pxl-practice-area',
			),
		),
	),
	'menu_order'            => 0,
	'position'              => 'normal',
	'style'                 => 'default',
	'label_placement'       => 'top',
	'instruction_placement' => 'label',
	'active'                => true,
	'show_in_rest'          => 1,
	'description'           => 'Поля для карток у секції «Напрямки юридичної практики» на головній.',
);

// --- Contact / Header options (ACF Options page: molochko-contact-options) ---
$group_contact_options = array(
	'key'                   => 'group_molochko_contact_options',
	'title'                 => 'Контакти сайту (шапка та загальні)',
	'fields'                => array(
		array(
			'key'   => 'field_contact_tab_header',
			'label' => 'Шапка сайту',
			'name'  => '',
			'type'  => 'tab',
		),
		array(
			'key'           => 'field_header_phone',
			'label'         => 'Телефон',
			'name'          => 'header_phone',
			'type'          => 'text',
			'instructions'  => 'Основний номер. Відображається в шапці та блоці «Про нас».',
			'placeholder'   => '+38 (050) 606-00-79',
		),
		array(
			'key'           => 'field_header_email',
			'label'         => 'Email',
			'name'          => 'header_email',
			'type'          => 'email',
			'instructions'  => 'Верхня смуга шапки.',
		),
		array(
			'key'           => 'field_header_address',
			'label'         => 'Адреса',
			'name'          => 'header_address',
			'type'          => 'text',
			'instructions'  => 'Текст адреси у верхній смузі шапки.',
		),
		array(
			'key'           => 'field_header_address_url',
			'label'         => 'Посилання на карту (Google Maps)',
			'name'          => 'header_address_url',
			'type'          => 'url',
			'instructions'  => 'Якщо заповнено, адреса стає посиланням на карту.',
		),
		array(
			'key'           => 'field_header_working_hours',
			'label'         => 'Графік роботи',
			'name'          => 'header_working_hours',
			'type'          => 'text',
			'instructions'  => 'Наприклад: Пн–Пт: 9:00 – 18:00. Перекладається через Polylang.',
			'placeholder'   => 'Пн–Пт: 9:00 – 18:00',
		),
		array(
			'key'           => 'field_header_consultation_url',
			'label'         => 'Посилання кнопки «Замовити консультацію»',
			'name'          => 'header_consultation_url',
			'type'          => 'url',
			'instructions'  => 'URL або якір, наприклад #contact або /kontakty.',
			'placeholder'   => '#contact',
		),
	),
	'location'              => array(
		array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'molochko-contact-options',
			),
		),
	),
	'menu_order'            => 0,
	'position'              => 'normal',
	'style'                 => 'default',
	'label_placement'       => 'top',
	'instruction_placement' => 'label',
	'active'                => true,
	'show_in_rest'          => 0,
	'description'           => 'Телефон, email, адреса, графік, кнопка консультації. Зберігаються в ACF Options (Контакти). Переклади — Polylang.',
);

// --- Import ---
$groups = array(
	'group_molochko_front_page'    => $group_front_page,
	'group_molochko_practice_area' => $group_practice_area,
	'group_molochko_contact_options' => $group_contact_options,
);

_out( 'Importing ' . count( $groups ) . ' ACF field groups...' );

foreach ( $groups as $key => $group ) {
	acf_update_field_group( $group );
	_out( '  Created/updated: ' . $group['title'] . ' (' . $key . ')' );
}

// --- Verify ---
_out( '' );
_out( 'Verification (acf-field-group posts in DB):' );

$all = get_posts( array(
	'post_type'      => 'acf-field-group',
	'posts_per_page' => -1,
	'post_status'    => 'any',
	'orderby'        => 'title',
	'order'          => 'ASC',
) );

if ( empty( $all ) ) {
	_out( '  ERROR: No field groups found in DB.' );
	exit( 1 );
}

$expected_titles = array(
	'Головна сторінка (Molochko)',
	'Напрямок практики (поля картки)',
	'Контакти сайту (шапка та загальні)',
);
$found_titles = array();
foreach ( $all as $post ) {
	$key = get_post_meta( $post->ID, 'key', true );
	_out( '  - ID ' . $post->ID . ': ' . $post->post_title . ( $key ? ' (key: ' . $key . ')' : '' ) );
	$found_titles[] = $post->post_title;
}

$found_expected = count( array_intersect( $expected_titles, array_unique( $found_titles ) ) );
if ( $found_expected < 3 ) {
	_out( '' );
	_out( 'WARNING: Expected groups may be missing. Check WP Admin → ACF → Field Groups.' );
	exit( 1 );
}

_out( '' );
_out( 'Done. ' . count( $all ) . ' field group(s) in DB. Check WP Admin → ACF → Field Groups.' );
