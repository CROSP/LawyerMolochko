<?php
/**
 * One-time: assign case_category (ACF) to all review posts (UA + RO).
 * Uses content => category map; resolves default-language term and sets ACF.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/assign-reviews-case-category-acf.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/assign-reviews-case-category-acf.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! post_type_exists( 'reviews' ) ) {
	echo "Reviews CPT not found.\n";
	exit( 1 );
}
if ( ! taxonomy_exists( 'case_study_category' ) ) {
	echo "Taxonomy case_study_category was removed. Set review case category via ACF field \"case_category\" (text/select) in the admin.\n";
	exit( 0 );
}

if ( ! function_exists( 'update_field' ) ) {
	echo "ACF (update_field) is required.\n";
	exit( 1 );
}

// Post content (UA or RO) => UA category name (for term lookup in default language)
$content_to_category_ua = array(
	// UA
	'Дякую за професійну допомогу з питаннями ТЦК та відстрочки. Все пояснили по-людськи, допомогли зібрати документи. Результат — відстрочка оформлена. Рекомендую.' => 'ТЦК / відстрочка',
	'Після ДТП допомогли отримати відшкодування від страхової та винуватця. Не потрібно було нічого робити самостійно — юристи вели справу від початку до кінця. Дуже вдячна.' => 'ДТП',
	'Звертався щодо захисту в кримінальній справі. Адвокат був присутній на допитах, все контролював. Справа закрита. Дякую за спокій і професіоналізм.' => 'Кримінальні справи',
	'Допомогли з розірванням шлюбу та поділом майна. Супруг не погоджувався — провели переговори, у суді все вирішили цивілізовано. Рекомендую бюро.' => 'Сімейні спори',
	'Звільнили незаконно — звернувся до бюро. Підготували позов, відновили на роботі та стягнули компенсацію. Дякую за чесність і результат.' => 'Трудові спори',
	'Консультували з питань спадщини та допомогли оформити документи. Все зрозуміло, без зайвих ходів. Раджу звертатися, якщо потрібна юридична допомога.' => 'Спадкування',
	// RO
	'Mulțumesc pentru ajutorul profesional în problemele TCC și amânare. Totul a fost explicat clar, m-au ajutat să adun documentele. Rezultat — amânarea a fost formalizată. Recomand.' => 'ТЦК / відстрочка',
	'După accidentul rutier m-au ajutat să obțin despăgubirea de la asigurator și de la vinovat. Nu a trebuit să fac nimic singură — avocații au condus cazul de la început până la sfârșit. Sunt foarte recunoscătoare.' => 'ДТП',
	'M-am adresat pentru apărare într-o cauză penală. Avocatul a fost prezent la interogatorii, a controlat totul. Cauza a fost închisă. Mulțumesc pentru liniște și profesionalism.' => 'Кримінальні справи',
	'M-au ajutat cu divorțul și împărțirea averii. Soțul nu era de acord — au condus negocieri, în instanță totul s-a rezolvat civilizat. Recomand biroul.' => 'Сімейні спори',
	'Am fost concediat ilegal — m-am adresat biroului. Au pregătit cererea, m-au repus la lucru și au încasat compensația. Mulțumesc pentru onestitate și rezultat.' => 'Трудові спори',
	'M-au consultat în probleme de succesiune și m-au ajutat să formalizez actele. Totul clar, fără pași inutili. Îi recomand dacă aveți nevoie de ajutor juridic.' => 'Спадкування',
);

$pll_default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
$args        = array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => false,
	'fields'     => 'all',
);
if ( $pll_default ) {
	$default_terms = get_terms( array_merge( $args, array( 'lang' => $pll_default ) ) );
} else {
	$default_terms = get_terms( $args );
}
if ( is_wp_error( $default_terms ) ) {
	$default_terms = array();
}
// Build name => term_id (default language)
$name_to_term_id = array();
foreach ( $default_terms as $t ) {
	$name_to_term_id[ $t->name ] = (int) $t->term_id;
}
// If we got no terms by lang, get all and use first match by name (fallback)
if ( empty( $name_to_term_id ) ) {
	$all = get_terms( $args );
	if ( ! is_wp_error( $all ) ) {
		foreach ( $all as $t ) {
			if ( ! isset( $name_to_term_id[ $t->name ] ) ) {
				$name_to_term_id[ $t->name ] = (int) $t->term_id;
			}
		}
	}
}
// Ensure all needed UA category names have a term (e.g. Спадкування might be in DB but not in lang filter)
$needed_names = array_unique( array_values( $content_to_category_ua ) );
foreach ( $needed_names as $name ) {
	if ( isset( $name_to_term_id[ $name ] ) ) {
		continue;
	}
	$term = get_term_by( 'name', $name, 'case_study_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$name_to_term_id[ $name ] = (int) $term->term_id;
		continue;
	}
	// Create term if missing (e.g. Спадкування)
	$slug = sanitize_title( $name );
	if ( function_exists( 'cyr_to_lat' ) && is_object( cyr_to_lat() ) && method_exists( cyr_to_lat(), 'transliterate' ) ) {
		$slug = sanitize_title( cyr_to_lat()->transliterate( $name ) );
	}
	$insert = wp_insert_term( $name, 'case_study_category', array( 'slug' => $slug ?: 'category-' . $name ) );
	if ( ! is_wp_error( $insert ) ) {
		$name_to_term_id[ $name ] = (int) $insert['term_id'];
		echo "Created term: {$name} (ID: {$insert['term_id']}).\n";
	}
}

$reviews = get_posts( array(
	'post_type'      => 'reviews',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
) );
if ( empty( $reviews ) ) {
	echo "No review posts found.\n";
	exit( 0 );
}

$updated = 0;
$skipped = 0;
foreach ( $reviews as $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'reviews' ) {
		continue;
	}
	$content = $post->post_content;
	$content = trim( $content );
	if ( $content === '' ) {
		$skipped++;
		continue;
	}
	if ( ! isset( $content_to_category_ua[ $content ] ) ) {
		$skipped++;
		continue;
	}
	$cat_name_ua = $content_to_category_ua[ $content ];
	if ( ! isset( $name_to_term_id[ $cat_name_ua ] ) ) {
		echo "Term not found for category: {$cat_name_ua} (review ID: {$post_id}).\n";
		$skipped++;
		continue;
	}
	$term_id = $name_to_term_id[ $cat_name_ua ];
	$updated_field = update_field( 'case_category', $term_id, $post_id );
	if ( $updated_field !== false ) {
		$updated++;
		$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id ) : '';
		echo "Review ID {$post_id}" . ( $lang ? " ({$lang})" : '' ) . " → case_category = {$term_id} ({$cat_name_ua}).\n";
	}
}

echo "Done. Set case_category for {$updated} review(s), skipped {$skipped}.\n";
