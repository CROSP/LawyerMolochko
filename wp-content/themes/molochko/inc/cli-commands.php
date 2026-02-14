<?php
/**
 * Unified CLI commands for Molochko theme (menu, blog page, featured images, migrations).
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/cli-commands.php [command]
 * Or:  wp eval-file wp-content/themes/molochko/inc/cli-commands.php [command]
 *
 * Commands:
 *   menu-primary-reviews   — Point Відгуки → /reviews/, remove Про нас from Primary menu
 *   blog-page-and-menu     — Rename blog page slug to "blog", add Новини to menu
 *   menu-order             — Set Primary menu order: Головна, Послуги, Новини, Відгуки, Кейси, Контакти
 *   menu-romanian          — Create Romanian primary menu (Acasă, Servicii, Știri, Recenzii, Cazuri, Contacte) and assign to RO
 *   page-slug-home         — Rename front page slug from home-layout-1 to home
 *   polylang-strings-ro    — Translate all Polylang registered strings to Romanian (run after DB backup)
 *   seed-hero-features     — Seed hero_features ACF repeater on default + Romanian front pages
 *   seed-about-ro          — Seed About block ACF on Romanian front page with Romanian text
 *   blog-image-obshuk      — Set featured image for "Обшук у помешканні" post (Pexels 7821937, documents)
 *   blog-images-three      — Set featured images for 3 posts by title (TCC, Обшук, Police)
 *   blog-images-five       — Set featured images for 5 legal posts by title (debt, obshuk, criminal, police, TCC)
 *   migrate-reviews        — Create 6 review CPT posts, change Reviews page slug to reviews-legacy, flush rules
 *   list                   — List available commands (default)
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

global $args;

$valid_commands = array( 'menu-primary-reviews', 'blog-page-and-menu', 'menu-order', 'menu-romanian', 'page-slug-home', 'polylang-strings-ro', 'seed-hero-features', 'seed-about-ro', 'blog-image-obshuk', 'blog-images-three', 'blog-images-five', 'migrate-reviews' );
$command = 'list';
if ( ! empty( $args[0] ) ) {
	$command = trim( $args[0] );
} else {
	foreach ( array( 3, 2, 1 ) as $i ) {
		if ( ! empty( $argv[ $i ] ) && in_array( trim( $argv[ $i ] ), $valid_commands, true ) ) {
			$command = trim( $argv[ $i ] );
			break;
		}
	}
	if ( $command === 'list' && ( $env_cmd = getenv( 'MOLOCHKO_CLI_CMD' ) ) && in_array( $env_cmd, $valid_commands, true ) ) {
		$command = $env_cmd;
	}
}

$commands_help = array(
	'menu-primary-reviews' => 'Point Відгуки → /reviews/, remove Про нас',
	'blog-page-and-menu'   => 'Rename blog page to slug "blog", add Новини to menu',
	'menu-order'           => 'Primary menu order: Головна, Послуги, Новини, Відгуки, Кейси, Контакти',
	'menu-romanian'        => 'Create Romanian primary menu and assign to RO',
	'page-slug-home'       => 'Rename front page slug home-layout-1 → home',
	'polylang-strings-ro'  => 'Translate all Polylang strings to Romanian',
	'seed-hero-features'   => 'Seed hero_features ACF repeater on default + Romanian front pages',
	'seed-about-ro'       => 'Seed About block ACF on Romanian front page with Romanian text',
	'blog-image-obshuk'    => 'Set featured image for Обшук у помешканні post',
	'blog-images-three'    => 'Set images for 3 posts (TCC, Обшук, Police) by title',
	'blog-images-five'     => 'Set images for 5 legal posts by title',
	'migrate-reviews'      => 'Migrate reviews to CPT, change page slug, flush rules',
);

function molochko_cli_require_media() {
	if ( ! function_exists( 'media_sideload_image' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
}

switch ( $command ) {

	case 'list':
	case '--help':
	case '-h':
		echo "Molochko CLI commands. Usage: wp eval-file inc/cli-commands.php <command>\n\n";
		foreach ( $commands_help as $cmd => $desc ) {
			echo "  " . $cmd . "\t" . $desc . "\n";
		}
		echo "\n";
		exit( 0 );

	case 'menu-primary-reviews': {
		$menu_id = 53;
		$reviews_archive_url = get_post_type_archive_link( 'reviews' );
		if ( ! $reviews_archive_url ) {
			echo "Reviews archive URL not found.\n";
			exit( 1 );
		}
		$reviews_item_id = 10832;
		$post = get_post( $reviews_item_id );
		if ( $post && $post->post_type === 'nav_menu_item' ) {
			$item = wp_setup_nav_menu_item( $post );
			wp_update_nav_menu_item( $menu_id, $reviews_item_id, array(
				'menu-item-db-id' => $reviews_item_id,
				'menu-item-type' => 'custom',
				'menu-item-url' => $reviews_archive_url,
				'menu-item-title' => 'Відгуки',
				'menu-item-position' => (int) $item->menu_order,
				'menu-item-parent-id' => (int) $item->menu_item_parent,
				'menu-item-status' => 'publish',
			) );
			echo "Updated Відгуки → {$reviews_archive_url}\n";
		}
		$about_item_id = 10831;
		if ( get_post_status( $about_item_id ) !== false ) {
			wp_delete_post( $about_item_id, true );
			echo "Removed Про нас.\n";
		}
		break;
	}

	case 'blog-page-and-menu': {
		$blog_page_id = (int) get_option( 'page_for_posts' );
		if ( ! $blog_page_id ) {
			echo "No Posts page set.\n";
			exit( 1 );
		}
		$page = get_post( $blog_page_id );
		if ( ! $page || $page->post_type !== 'page' ) {
			echo "Blog page not found.\n";
			exit( 1 );
		}
		if ( $page->post_name === 'blog-default' ) {
			wp_update_post( array( 'ID' => $blog_page_id, 'post_name' => 'blog' ) );
			echo "Renamed slug to 'blog'.\n";
		}
		$blog_url = get_permalink( $blog_page_id );
		$menu_id = 53;
		$items = wp_get_nav_menu_items( $menu_id );
		$has_novini = false;
		if ( $items ) {
			foreach ( $items as $item ) {
				if ( trim( $item->title ) === 'Новини' ) {
					$has_novini = true;
					break;
				}
			}
		}
		if ( ! $has_novini ) {
			$max_order = 0;
			foreach ( (array) $items as $item ) {
				if ( $item->menu_order > $max_order ) {
					$max_order = $item->menu_order;
				}
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-type' => 'custom',
				'menu-item-url' => $blog_url,
				'menu-item-title' => 'Новини',
				'menu-item-position' => $max_order + 1,
				'menu-item-parent-id' => 0,
				'menu-item-status' => 'publish',
			) );
			echo "Added Новини to menu.\n";
		} else {
			echo "Новини already in menu.\n";
		}
		break;
	}

	case 'menu-order': {
		$menu_id = 53;
		$items = wp_get_nav_menu_items( $menu_id );
		if ( ! $items ) {
			echo "No menu items.\n";
			exit( 1 );
		}
		$order_map = array( 'Головна' => 1, 'Послуги' => 2, 'Новини' => 3, 'Відгуки' => 4, 'Кейси' => 5, 'Контакти' => 6 );
		foreach ( $items as $item ) {
			$title = trim( $item->title );
			$pos = isset( $order_map[ $title ] ) ? $order_map[ $title ] : $item->menu_order;
			if ( (int) $item->menu_order !== (int) $pos ) {
				wp_update_post( array( 'ID' => (int) $item->db_id, 'menu_order' => (int) $pos ) );
			}
		}
		echo "Menu order updated.\n";
		break;
	}

	case 'menu-romanian': {
		if ( ! function_exists( 'pll_current_language' ) || ! function_exists( 'pll_home_url' ) || ! function_exists( 'pll_set_term_language' ) || ! function_exists( 'pll_get_post' ) ) {
			echo "Polylang is required. Activate Polylang and run again.\n";
			exit( 1 );
		}
		$langs = array();
		if ( function_exists( 'pll_languages_list' ) ) {
			$langs = pll_languages_list();
		} elseif ( ! empty( PLL()->model ) && method_exists( PLL()->model, 'get_languages_list' ) ) {
			$list = PLL()->model->get_languages_list();
			$langs = wp_list_pluck( $list, 'slug' );
		}
		if ( ! in_array( 'ro', $langs, true ) ) {
			echo "Romanian (ro) is not configured in Polylang. Add Romanian in Languages first.\n";
			exit( 1 );
		}
		$theme = get_option( 'stylesheet' );
		$uk_menu_id = 53;
		$uk_items = wp_get_nav_menu_items( $uk_menu_id );
		$ro_titles = array(
			'Головна'   => 'Acasă',
			'Послуги'   => 'Servicii',
			'Новини'    => 'Știri',
			'Відгуки'   => 'Recenzii',
			'Кейси'     => 'Cazuri',
			'Контакти'  => 'Contacte',
		);
		$ro_urls = array();
		$ro_urls['Головна'] = pll_home_url( 'ro' );
		if ( $uk_items ) {
			foreach ( $uk_items as $item ) {
				if ( $item->menu_item_parent != 0 ) {
					continue;
				}
				$title = trim( $item->title );
				if ( isset( $ro_titles[ $title ] ) ) {
					if ( $title === 'Головна' ) {
						continue;
					}
					if ( $item->type === 'post_type' && ! empty( $item->object_id ) ) {
						$tr_id = pll_get_post( (int) $item->object_id, 'ro' );
						$ro_urls[ $title ] = $tr_id ? get_permalink( $tr_id ) : pll_home_url( 'ro' );
					} else {
						$home = home_url( '/' );
						$ro_home = pll_home_url( 'ro' );
						$url = $item->url;
						if ( $url === $home || $url === rtrim( $home, '/' ) ) {
							$ro_urls[ $title ] = $ro_home;
						} else {
							$ro_urls[ $title ] = $ro_home . wp_parse_url( $url, PHP_URL_PATH ) ?: '';
						}
					}
				}
			}
		}
		foreach ( array_keys( $ro_titles ) as $uk_title ) {
			if ( ! isset( $ro_urls[ $uk_title ] ) ) {
				$ro_urls[ $uk_title ] = pll_home_url( 'ro' );
			}
		}
		$menu_name = 'Meniu principal (RO)';
		$existing = wp_get_nav_menu_object( $menu_name );
		if ( $existing ) {
			$menu_term_id = (int) $existing->term_id;
			$items_old = wp_get_nav_menu_items( $menu_term_id );
			if ( $items_old ) {
				foreach ( $items_old as $it ) {
					wp_delete_post( (int) $it->db_id, true );
				}
			}
			echo "Using existing menu: {$menu_name} (ID {$menu_term_id}).\n";
		} else {
			$menu_term_id = wp_create_nav_menu( $menu_name );
			if ( is_wp_error( $menu_term_id ) ) {
				echo $menu_term_id->get_error_message() . "\n";
				exit( 1 );
			}
			echo "Created menu: {$menu_name} (ID {$menu_term_id}).\n";
		}
		pll_set_term_language( $menu_term_id, 'ro' );
		$position = 1;
		foreach ( $ro_titles as $uk_title => $ro_title ) {
			$url = isset( $ro_urls[ $uk_title ] ) ? $ro_urls[ $uk_title ] : pll_home_url( 'ro' );
			wp_update_nav_menu_item( $menu_term_id, 0, array(
				'menu-item-type'   => 'custom',
				'menu-item-url'    => $url,
				'menu-item-title'  => $ro_title,
				'menu-item-position' => $position++,
				'menu-item-parent-id' => 0,
				'menu-item-status' => 'publish',
			) );
		}
		$nav_menus = PLL()->options->get( 'nav_menus' );
		if ( ! is_array( $nav_menus ) ) {
			$nav_menus = array();
		}
		if ( ! isset( $nav_menus[ $theme ] ) ) {
			$nav_menus[ $theme ] = array();
		}
		if ( ! isset( $nav_menus[ $theme ]['primary'] ) ) {
			$nav_menus[ $theme ]['primary'] = array();
		}
		$nav_menus[ $theme ]['primary']['ro'] = (int) $menu_term_id;
		PLL()->options->set( 'nav_menus', $nav_menus );
		echo "Romanian primary menu created and assigned to location 'primary' for language 'ro'.\n";
		break;
	}

	case 'page-slug-home': {
		$front_page_id = (int) get_option( 'page_on_front' );
		if ( ! $front_page_id ) {
			$pages = get_posts( array( 'post_type' => 'page', 'post_name' => 'home-layout-1', 'post_status' => 'any', 'posts_per_page' => 1 ) );
			$page = ! empty( $pages ) ? $pages[0] : null;
		} else {
			$page = get_post( $front_page_id );
		}
		if ( ! $page || $page->post_type !== 'page' ) {
			echo "Front page or page with slug 'home-layout-1' not found.\n";
			exit( 1 );
		}
		$flushed = false;
		if ( $page->post_name !== 'home' ) {
			$updated = wp_update_post( array(
				'ID'        => $page->ID,
				'post_name' => 'home',
			), true );
			if ( is_wp_error( $updated ) ) {
				echo $updated->get_error_message() . "\n";
				exit( 1 );
			}
			echo "Renamed default front page slug → home (ID {$page->ID}).\n";
			$flushed = true;
		}
		if ( function_exists( 'pll_get_post' ) ) {
			$ro_page_id = pll_get_post( $page->ID, 'ro' );
			if ( $ro_page_id ) {
				$ro_page = get_post( $ro_page_id );
				if ( $ro_page && $ro_page->post_type === 'page' && $ro_page->post_name !== 'home' ) {
					$updated_ro = wp_update_post( array(
						'ID'        => $ro_page->ID,
						'post_name' => 'home',
					), true );
					if ( ! is_wp_error( $updated_ro ) ) {
						echo "Renamed Romanian front page slug → home (ID {$ro_page->ID}).\n";
						$flushed = true;
					}
				}
			}
		}
		if ( $flushed ) {
			flush_rewrite_rules();
			echo "Flushed rewrite rules.\n";
		} else {
			echo "Front page slug(s) already 'home'. Nothing to do.\n";
		}
		if ( function_exists( 'pll_home_url' ) && PLL() && PLL()->options ) {
			$theme = get_option( 'stylesheet' );
			$nav_menus = PLL()->options->get( 'nav_menus' );
			$ro_menu_id = isset( $nav_menus[ $theme ]['primary']['ro'] ) ? (int) $nav_menus[ $theme ]['primary']['ro'] : 0;
			if ( $ro_menu_id ) {
				$ro_items = wp_get_nav_menu_items( $ro_menu_id );
				$ro_home = pll_home_url( 'ro' );
				$updated = 0;
				foreach ( (array) $ro_items as $it ) {
					if ( strpos( $it->url, 'home-layout-1' ) !== false ) {
						$new_url = str_replace( 'home-layout-1', 'home', $it->url );
						wp_update_nav_menu_item( $ro_menu_id, (int) $it->db_id, array(
							'menu-item-db-id'       => (int) $it->db_id,
							'menu-item-type'        => $it->type,
							'menu-item-url'         => $new_url,
							'menu-item-title'       => $it->title,
							'menu-item-position'    => (int) $it->menu_order,
							'menu-item-parent-id'   => (int) $it->menu_item_parent,
							'menu-item-status'      => 'publish',
						) );
						$updated++;
					}
				}
				if ( $updated ) {
					echo "Updated {$updated} Romanian menu item(s) URL: home-layout-1 → home.\n";
				}
			}
		}
		break;
	}

	case 'seed-hero-features': {
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			echo "ACF is required. Activate Advanced Custom Fields and run again.\n";
			exit( 1 );
		}
		$default_fid = (int) get_option( 'page_on_front' );
		if ( ! $default_fid || ! get_post( $default_fid ) ) {
			echo "No front page set (Settings → Reading). Set a front page first.\n";
			exit( 1 );
		}
		$ro_fid = 0;
		if ( function_exists( 'pll_get_post' ) ) {
			$ro_fid = (int) pll_get_post( $default_fid, 'ro' );
		}
		$home_ua = home_url();
		$home_ro = function_exists( 'pll_home_url' ) ? pll_home_url( 'ro' ) : ( $home_ua . '/ro/' );

		// Try to copy icon_image and image from old fancy_boxes so we don't lose media.
		$old_fancy = get_field( 'fancy_boxes', $default_fid );
		$old_ro_fancy = $ro_fid ? get_field( 'fancy_boxes', $ro_fid ) : null;
		$get_media = function( $rows, $index ) {
			if ( empty( $rows ) || ! is_array( $rows ) || ! isset( $rows[ $index ] ) ) {
				return array( 'icon' => 0, 'image' => 0 );
			}
			$r = $rows[ $index ];
			$icon = 0;
			$img  = 0;
			if ( ! empty( $r['icon_image'] ) ) {
				$icon = is_array( $r['icon_image'] ) ? ( (int) ( $r['icon_image']['id'] ?? $r['icon_image'] ) ) : (int) $r['icon_image'];
			}
			if ( ! empty( $r['image'] ) ) {
				$img = is_array( $r['image'] ) ? ( (int) ( $r['image']['id'] ?? $r['image'] ) ) : (int) $r['image'];
			}
			return array( 'icon' => $icon ?: 0, 'image' => $img ?: 0 );
		};

		$m0 = $get_media( $old_fancy, 0 );
		$m1 = $get_media( $old_fancy, 1 );
		$m2 = $get_media( $old_fancy, 2 );
		$ua_boxes = array(
			array(
				'title'       => 'Безкоштовна консультація',
				'icon'        => $m0['icon'],
				'description' => 'Якщо вам потрібна допомога юриста, ми надамо безкоштовну консультацію незалежно від складності справи. Досвід, прозорість, результат.',
				'image'       => $m0['image'],
				'link'        => array( 'url' => $home_ua . '/#contact', 'title' => "Зв'язатися", 'target' => '_self' ),
			),
			array(
				'title'       => '20 років досвіду',
				'icon'        => $m1['icon'],
				'description' => 'Найкращі юристи з багаторічним досвідом. Поєднуємо експертизу з індивідуальним підходом до кожної справи. Гарантуємо якість та результат.',
				'image'       => $m1['image'],
				'link'        => array( 'url' => $home_ua . '/#about', 'title' => 'Детальніше', 'target' => '_self' ),
			),
			array(
				'title'       => 'Нагороди та сертифікати',
				'icon'        => $m2['icon'],
				'description' => 'Ми отримали визнання та довіру клієнтів. Прозорість, чесність та ефективність — наші принципи роботи в кожній справі.',
				'image'       => $m2['image'],
				'link'        => array( 'url' => $home_ua . '/#about', 'title' => 'Детальніше', 'target' => '_self' ),
			),
		);
		$rm0 = $get_media( $old_ro_fancy, 0 );
		$rm1 = $get_media( $old_ro_fancy, 1 );
		$rm2 = $get_media( $old_ro_fancy, 2 );
		// Romanian: use RO old media if any, else same as UA so images are shared.
		$ro_boxes = array(
			array(
				'title'       => 'Consultație gratuită',
				'icon'        => $rm0['icon'] ?: $m0['icon'],
				'description' => 'Dacă aveți nevoie de ajutor juridic, vă oferim o consultație gratuită indiferent de complexitatea cauzei. Experiență, transparență, rezultat.',
				'image'       => $rm0['image'] ?: $m0['image'],
				'link'        => array( 'url' => $home_ro . '#contact', 'title' => 'Contactați-ne', 'target' => '_self' ),
			),
			array(
				'title'       => '20 de ani experiență',
				'icon'        => $rm1['icon'] ?: $m1['icon'],
				'description' => 'Cei mai buni avocați cu experiență de ani. Combinăm expertiza cu o abordare individuală pentru fiecare cauză. Garanție de calitate și rezultat.',
				'image'       => $rm1['image'] ?: $m1['image'],
				'link'        => array( 'url' => $home_ro . '#about', 'title' => 'Detalii', 'target' => '_self' ),
			),
			array(
				'title'       => 'Premii și certificate',
				'icon'        => $rm2['icon'] ?: $m2['icon'],
				'description' => 'Am câștigat recunoașterea și încrederea clienților. Transparență, onestitate și eficiență — principiile noastre în fiecare cauză.',
				'image'       => $rm2['image'] ?: $m2['image'],
				'link'        => array( 'url' => $home_ro . '#about', 'title' => 'Detalii', 'target' => '_self' ),
			),
		);
		update_field( 'hero_features', $ua_boxes, $default_fid );
		echo "Seeded hero_features on default front page (ID {$default_fid}).\n";
		if ( $ro_fid && get_post( $ro_fid ) ) {
			update_field( 'hero_features', $ro_boxes, $ro_fid );
			echo "Seeded hero_features on Romanian front page (ID {$ro_fid}).\n";
		} else {
			echo "Romanian front page not found; only default page was updated.\n";
		}
		break;
	}

	case 'seed-about-ro': {
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			echo "ACF is required. Activate Advanced Custom Fields and run again.\n";
			exit( 1 );
		}
		$default_fid = (int) get_option( 'page_on_front' );
		if ( ! $default_fid || ! get_post( $default_fid ) ) {
			echo "No front page set (Settings → Reading). Set a front page first.\n";
			exit( 1 );
		}
		$ro_fid = 0;
		if ( function_exists( 'pll_get_post' ) ) {
			$ro_fid = (int) pll_get_post( $default_fid, 'ro' );
		}
		if ( ! $ro_fid || ! get_post( $ro_fid ) ) {
			echo "Romanian front page not found. Add Romanian in Polylang and set the front page translation.\n";
			exit( 1 );
		}
		$bg_id    = get_field( 'about_image_bg', $default_fid );
		$person   = get_field( 'about_image_person', $default_fid );
		$bg_id    = is_array( $bg_id ) ? (int) ( $bg_id['id'] ?? $bg_id ) : (int) $bg_id;
		$person_id = is_array( $person ) ? (int) ( $person['id'] ?? $person ) : (int) $person;
		update_field( 'about_image_bg', $bg_id ?: null, $ro_fid );
		update_field( 'about_image_person', $person_id ?: null, $ro_fid );
		update_field( 'about_subtitle', 'Despre birou', $ro_fid );
		update_field( 'about_title', 'Pasiune pentru dreptate. Experiență pentru victorie.', $ro_fid );
		update_field( 'about_description', 'În Biroul de Avocatură am creat o echipă de juriști care combină cunoștințe profunde, experiență practică și dorința sinceră de a-i ajuta pe clienți în situații juridice dificile.<br><br>Cultura noastră se bazează pe încredere, transparență și responsabilitate. Lucrăm zilnic pentru ca clientul să se simtă sprijinit în fiecare etapă — de la prima consultație până la încheierea cauzei.', $ro_fid );
		update_field( 'about_cta_line', 'Sună-ne 24/7. Să luptăm împreună.', $ro_fid );
		update_field( 'about_name', 'Molochko Taras Viktorovici', $ro_fid );
		update_field( 'about_role', 'președinte', $ro_fid );
		echo "Seeded About block on Romanian front page (ID {$ro_fid}).\n";
		break;
	}

	case 'polylang-strings-ro': {
		if ( ! function_exists( 'pll_current_language' ) || ! PLL() || ! PLL()->model ) {
			echo "Polylang is required. Activate Polylang and run again.\n";
			exit( 1 );
		}
		$languages = PLL()->model->get_languages_list();
		$ro_lang = null;
		foreach ( $languages as $lang ) {
			if ( $lang->slug === 'ro' ) {
				$ro_lang = $lang;
				break;
			}
		}
		if ( ! $ro_lang ) {
			echo "Romanian (ro) is not configured in Polylang. Add Romanian in Languages first.\n";
			exit( 1 );
		}
		$sources = array();
		if ( PLL() instanceof PLL_Admin_Base && class_exists( 'PLL_Admin_Strings' ) ) {
			$registered = PLL_Admin_Strings::get_strings();
			if ( ! empty( $registered ) ) {
				$sources = array_values( array_unique( wp_list_pluck( $registered, 'string' ) ) );
			}
		}
		if ( empty( $sources ) ) {
			$all_sources = array();
			foreach ( $languages as $lang ) {
				$meta = get_term_meta( $lang->term_id, '_pll_strings_translations', true );
				if ( ! is_array( $meta ) ) {
					continue;
				}
				foreach ( $meta as $pair ) {
					if ( isset( $pair[0] ) && $pair[0] !== '' ) {
						$all_sources[ $pair[0] ] = true;
					}
				}
			}
			$sources = array_keys( $all_sources );
		}
		require_once __DIR__ . '/polylang-strings-ro.php';
		$map = molochko_polylang_strings_ro_map();
		// Include all map keys so CF7 and other theme strings get RO translations even if not yet registered.
		$sources = array_values( array_unique( array_merge( $sources, array_keys( $map ) ) ) );
		if ( empty( $sources ) ) {
			echo "No Polylang strings found in DB. Open Languages → String translations, click Save once, then run this command again.\n";
			exit( 1 );
		}
		$ro_strings = array();
		$mapped = 0;
		foreach ( $sources as $src ) {
			$translation = isset( $map[ $src ] ) ? $map[ $src ] : $src;
			if ( $translation !== $src ) {
				$mapped++;
			}
			$ro_strings[] = wp_slash( array( $src, $translation ) );
		}
		update_term_meta( $ro_lang->term_id, '_pll_strings_translations', $ro_strings );
		foreach ( $languages as $lang ) {
			if ( $lang->slug === 'ro' ) {
				continue;
			}
			$existing_meta = get_term_meta( $lang->term_id, '_pll_strings_translations', true );
			$existing = array();
			if ( is_array( $existing_meta ) ) {
				foreach ( $existing_meta as $pair ) {
					if ( isset( $pair[0] ) && isset( $pair[1] ) && $pair[0] !== '' ) {
						$existing[ $pair[0] ] = $pair[1];
					}
				}
			}
			$lang_strings = array();
			foreach ( $sources as $src ) {
				$translation = isset( $existing[ $src ] ) ? $existing[ $src ] : $src;
				$lang_strings[] = wp_slash( array( $src, $translation ) );
			}
			update_term_meta( $lang->term_id, '_pll_strings_translations', $lang_strings );
		}
		echo "Updated Romanian Polylang strings: " . count( $ro_strings ) . " entries (" . $mapped . " translated from map). Seeded all languages.\n";
		break;
	}

	case 'blog-image-obshuk': {
		molochko_cli_require_media();
		$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'name' => 'obshuk-u-pomeshkanni-prava-ta-poryadok-provedennya', 'posts_per_page' => 1 ) );
		$post = ! empty( $posts ) ? $posts[0] : null;
		if ( ! $post ) {
			$all = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
			foreach ( $all as $p ) {
				if ( strpos( $p->post_title, 'Обшук у помешканні' ) !== false ) {
					$post = $p;
					break;
				}
			}
		}
		if ( ! $post ) {
			echo "Post not found.\n";
			exit( 1 );
		}
		$img_url = 'https://images.pexels.com/photos/7821937/pexels-photo-7821937.jpeg?auto=compress&w=1200';
		$aid = media_sideload_image( $img_url, $post->ID, $post->post_title, 'id' );
		if ( is_wp_error( $aid ) ) {
			echo $aid->get_error_message() . "\n";
			exit( 1 );
		}
		set_post_thumbnail( $post->ID, $aid );
		echo "Set image for: {$post->post_title}\n";
		break;
	}

	case 'blog-images-three': {
		molochko_cli_require_media();
		$by_title = array(
			'Адвокатський запит до ТЦК' => 'https://images.pexels.com/photos/5393593/pexels-photo-5393593.jpeg?auto=compress&w=1200',
			'Обшук у помешканні'       => 'https://images.pexels.com/photos/7821937/pexels-photo-7821937.jpeg?auto=compress&w=1200',
			'Як вести себе з поліцією' => 'https://images.pexels.com/photos/4827720/pexels-photo-4827720.jpeg?auto=compress&w=1200',
		);
		$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		$updated = 0;
		foreach ( $posts as $post ) {
			$t = $post->post_title;
			$img_url = null;
			foreach ( $by_title as $part => $url ) {
				if ( strpos( $t, $part ) !== false ) {
					$img_url = $url;
					break;
				}
			}
			if ( ! $img_url ) {
				continue;
			}
			$aid = media_sideload_image( $img_url, $post->ID, $t, 'id' );
			if ( is_wp_error( $aid ) ) {
				echo "Skip {$t}: " . $aid->get_error_message() . "\n";
				continue;
			}
			set_post_thumbnail( $post->ID, $aid );
			$updated++;
			echo "Set image for: {$t}\n";
		}
		echo "Done. Updated {$updated} post(s).\n";
		break;
	}

	case 'blog-images-five': {
		molochko_cli_require_media();
		$image_map = array(
			'styagnennya-borgu-cherez-sud-pretenziya-pozov-vikonannya' => 'https://images.pexels.com/photos/6077326/pexels-photo-6077326.jpeg?auto=compress&w=1200',
			'obshuk-u-pomeshkanni-prava-ta-poryadok-provedennya'       => 'https://images.pexels.com/photos/7821937/pexels-photo-7821937.jpeg?auto=compress&w=1200',
			'kriminalna-sprava-etapi-vid-pidozri-do-viroku'            => 'https://images.pexels.com/photos/5669619/pexels-photo-5669619.jpeg?auto=compress&w=1200',
			'yak-vesti-sebe-z-policzi'                                 => 'https://images.pexels.com/photos/8760946/pexels-photo-8760946.jpeg?auto=compress&w=1200',
			'advokatskij-zapit-do-tczk'                                => 'https://images.pexels.com/photos/7687557/pexels-photo-7687557.jpeg?auto=compress&w=1200',
		);
		$default_url = 'https://images.pexels.com/photos/5669619/pexels-photo-5669619.jpeg?auto=compress&w=1200';
		$posts_to_update = array();
		$all = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		foreach ( $all as $p ) {
			if ( preg_match( '/styagnennya-borgu|obshuk-u-pomeshkanni|kriminalna-sprava|yak-vesti-sebe-z-policz|advokatskij-zapit-do-tczk/', $p->post_name ) ) {
				$posts_to_update[ $p->ID ] = $p;
			}
		}
		$updated = 0;
		foreach ( $posts_to_update as $post ) {
			$t = $post->post_title;
			$img_url = null;
			if ( strpos( $t, 'борг' ) !== false || strpos( $t, 'Стягнення' ) !== false ) {
				$img_url = $image_map['styagnennya-borgu-cherez-sud-pretenziya-pozov-vikonannya'];
			} elseif ( strpos( $t, 'Обшук' ) !== false ) {
				$img_url = $image_map['obshuk-u-pomeshkanni-prava-ta-poryadok-provedennya'];
			} elseif ( strpos( $t, 'Кримінальна' ) !== false ) {
				$img_url = $image_map['kriminalna-sprava-etapi-vid-pidozri-do-viroku'];
			} elseif ( strpos( $t, 'поліцією' ) !== false || strpos( $t, 'затриманні' ) !== false ) {
				$img_url = $image_map['yak-vesti-sebe-z-policzi'];
			} elseif ( strpos( $t, 'ТЦК' ) !== false || strpos( $t, 'Адвокатський' ) !== false ) {
				$img_url = $image_map['advokatskij-zapit-do-tczk'];
			}
			if ( ! $img_url ) {
				$img_url = $default_url;
			}
			$aid = media_sideload_image( $img_url, $post->ID, $post->post_title, 'id' );
			if ( is_wp_error( $aid ) ) {
				echo "Skip {$t}: " . $aid->get_error_message() . "\n";
				continue;
			}
			set_post_thumbnail( $post->ID, $aid );
			$updated++;
			echo "Set image for: {$t}\n";
		}
		echo "Done. Updated {$updated} post(s).\n";
		break;
	}

	case 'migrate-reviews': {
		if ( ! post_type_exists( 'reviews' ) ) {
			echo "Post type 'reviews' not found.\n";
			exit( 1 );
		}
		$reviews = array(
			array( 'name' => 'Олександр К.', 'text' => 'Дякую за професійну допомогу з питаннями ТЦК та відстрочки. Все пояснили по-людськи, допомогли зібрати документи. Результат — відстрочка оформлена. Рекомендую.', 'case_type' => 'ТЦК / відстрочка' ),
			array( 'name' => 'Марина В.', 'text' => 'Після ДТП допомогли отримати відшкодування від страхової та винуватця. Не потрібно було нічого робити самостійно — юристи вели справу від початку до кінця. Дуже вдячна.', 'case_type' => 'ДТП' ),
			array( 'name' => 'Андрій П.', 'text' => 'Звертався щодо захисту в кримінальній справі. Адвокат був присутній на допитах, все контролював. Справа закрита. Дякую за спокій і професіоналізм.', 'case_type' => 'Кримінальні справи' ),
			array( 'name' => 'Ірина С.', 'text' => 'Допомогли з розірванням шлюбу та поділом майна. Супруг не погоджувався — провели переговори, у суді все вирішили цивілізовано. Рекомендую бюро.', 'case_type' => 'Сімейні спори' ),
			array( 'name' => 'Дмитро Т.', 'text' => 'Звільнили незаконно — звернувся до бюро. Підготували позов, відновили на роботі та стягнули компенсацію. Дякую за чесність і результат.', 'case_type' => 'Трудові спори' ),
			array( 'name' => 'Наталія Л.', 'text' => 'Консультували з питань спадщини та допомогли оформити документи. Все зрозуміло, без зайвих ходів. Раджу звертатися, якщо потрібна юридична допомога.', 'case_type' => 'Спадкування' ),
		);
		$get_or_create_term = function( $name, $tax = 'case_study_category' ) {
			$term = get_term_by( 'name', $name, $tax );
			if ( $term && ! is_wp_error( $term ) ) {
				return (int) $term->term_id;
			}
			$r = wp_insert_term( $name, $tax, array( 'slug' => sanitize_title( $name ) ) );
			return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
		};
		$created = 0;
		foreach ( $reviews as $r ) {
			$term_id = $get_or_create_term( $r['case_type'] );
			$post_id = wp_insert_post( array(
				'post_type' => 'reviews',
				'post_title' => $r['name'] . ' – ' . $r['case_type'],
				'post_content' => $r['text'],
				'post_status' => 'publish',
				'post_author' => 1,
			) );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
			update_post_meta( $post_id, 'person_name', $r['name'] );
			if ( $term_id && function_exists( 'update_field' ) ) {
				update_field( 'case_category', $term_id, $post_id );
			}
			$created++;
		}
		$reviews_page_id = 4666;
		$page = get_post( $reviews_page_id );
		if ( $page && $page->post_type === 'page' && $page->post_name === 'reviews' ) {
			wp_update_post( array( 'ID' => $reviews_page_id, 'post_name' => 'reviews-legacy' ) );
			echo "Page slug changed to reviews-legacy.\n";
		}
		flush_rewrite_rules();
		echo "Created {$created} review posts. Flushed rewrite rules.\n";
		break;
	}

	default:
		echo "Unknown command: {$command}. Run with 'list' or --help to see commands.\n";
		exit( 1 );
}

echo "Done.\n";
