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
 *   blog-image-obshuk      — Set featured image for "Обшук у помешканні" post (Pexels 7715258)
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

$command = isset( $argv[1] ) ? trim( $argv[1] ) : 'list';

$commands_help = array(
	'menu-primary-reviews' => 'Point Відгуки → /reviews/, remove Про нас',
	'blog-page-and-menu'   => 'Rename blog page to slug "blog", add Новини to menu',
	'menu-order'           => 'Primary menu order: Головна, Послуги, Новини, Відгуки, Кейси, Контакти',
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
		$img_url = 'https://images.pexels.com/photos/7715258/pexels-photo-7715258.jpeg?auto=compress&w=1200';
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
			'Обшук у помешканні'       => 'https://images.pexels.com/photos/7714849/pexels-photo-7714849.jpeg?auto=compress&w=1200',
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
			'obshuk-u-pomeshkanni-prava-ta-poryadok-provedennya'       => 'https://images.pexels.com/photos/5669619/pexels-photo-5669619.jpeg?auto=compress&w=1200',
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
			if ( $term_id ) {
				wp_set_object_terms( $post_id, array( $term_id ), 'case_study_category' );
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
