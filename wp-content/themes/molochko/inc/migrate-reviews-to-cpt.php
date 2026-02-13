<?php
/**
 * One-time: migrate 6 thematic reviews from Elementor Reviews page to Reviews CPT.
 * Creates review posts with ACF person_name, post_content (quote), case_study_category term.
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/migrate-reviews-to-cpt.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

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

function molochko_migrate_get_or_create_term( $name, $taxonomy = 'case_study_category' ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}
	$slug = sanitize_title( $name );
	$r    = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
	if ( is_wp_error( $r ) ) {
		return 0;
	}
	return (int) $r['term_id'];
}

$created = 0;
foreach ( $reviews as $r ) {
	$term_id = molochko_migrate_get_or_create_term( $r['case_type'] );
	$post_id = wp_insert_post( array(
		'post_type'    => 'reviews',
		'post_title'   => $r['name'] . ' – ' . $r['case_type'],
		'post_content' => $r['text'],
		'post_status'  => 'publish',
		'post_author'  => 1,
	) );
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		echo "Failed to create review for {$r['name']}\n";
		continue;
	}
	update_post_meta( $post_id, 'person_name', $r['name'] );
	if ( $term_id ) {
		wp_set_object_terms( $post_id, array( $term_id ), 'case_study_category' );
	}
	$created++;
}

// Free /reviews/ for CPT archive: change old Reviews page slug.
$reviews_page_id = 4666;
$page = get_post( $reviews_page_id );
if ( $page && $page->post_type === 'page' && $page->post_name === 'reviews' ) {
	wp_update_post( array(
		'ID'        => $reviews_page_id,
		'post_name' => 'reviews-legacy',
	) );
	echo "Page ID {$reviews_page_id} slug changed to 'reviews-legacy'. /reviews/ is now the Reviews CPT archive.\n";
}

flush_rewrite_rules();
echo "Flushed rewrite rules. Visit " . get_post_type_archive_link( 'reviews' ) . " for the archive.\n";
echo "Created {$created} review posts. Front page and archive will use Reviews CPT.\n";
