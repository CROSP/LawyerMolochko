<?php
/**
 * One-time: translate review CPT RO copies (person_name, post_content, post_title).
 * Matches by UA post_content. Run after RO review copies exist (Polylang).
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/translate-reviews-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/translate-reviews-ro.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'pll_get_post' ) ) {
	echo "Polylang is required.\n";
	exit( 1 );
}

if ( ! post_type_exists( 'reviews' ) ) {
	echo "Reviews CPT not found.\n";
	exit( 1 );
}

// UA post_content (exact) => RO name, RO text
$ro_reviews = array(
	'Дякую за професійну допомогу з питаннями ТЦК та відстрочки. Все пояснили по-людськи, допомогли зібрати документи. Результат — відстрочка оформлена. Рекомендую.' => array(
		'name' => 'Alexandru K.',
		'text' => 'Mulțumesc pentru ajutorul profesional în problemele TCC și amânare. Totul a fost explicat clar, m-au ajutat să adun documentele. Rezultat — amânarea a fost formalizată. Recomand.',
	),
	'Після ДТП допомогли отримати відшкодування від страхової та винуватця. Не потрібно було нічого робити самостійно — юристи вели справу від початку до кінця. Дуже вдячна.' => array(
		'name' => 'Marina V.',
		'text' => 'După accidentul rutier m-au ajutat să obțin despăgubirea de la asigurator și de la vinovat. Nu a trebuit să fac nimic singură — avocații au condus cazul de la început până la sfârșit. Sunt foarte recunoscătoare.',
	),
	'Звертався щодо захисту в кримінальній справі. Адвокат був присутній на допитах, все контролював. Справа закрита. Дякую за спокій і професіоналізм.' => array(
		'name' => 'Andrei P.',
		'text' => 'M-am adresat pentru apărare într-o cauză penală. Avocatul a fost prezent la interogatorii, a controlat totul. Cauza a fost închisă. Mulțumesc pentru liniște și profesionalism.',
	),
	'Допомогли з розірванням шлюбу та поділом майна. Супруг не погоджувався — провели переговори, у суді все вирішили цивілізовано. Рекомендую бюро.' => array(
		'name' => 'Irina S.',
		'text' => 'M-au ajutat cu divorțul și împărțirea averii. Soțul nu era de acord — au condus negocieri, în instanță totul s-a rezolvat civilizat. Recomand biroul.',
	),
	'Звільнили незаконно — звернувся до бюро. Підготували позов, відновили на роботі та стягнули компенсацію. Дякую за чесність і результат.' => array(
		'name' => 'Dmitri T.',
		'text' => 'Am fost concediat ilegal — m-am adresat biroului. Au pregătit cererea, m-au repus la lucru și au încasat compensația. Mulțumesc pentru onestitate și rezultat.',
	),
	'Консультували з питань спадщини та допомогли оформити документи. Все зрозуміло, без зайвих ходів. Раджу звертатися, якщо потрібна юридична допомога.' => array(
		'name' => 'Natalia L.',
		'text' => 'M-au consultat în probleme de succesiune și m-au ajutat să formalizez actele. Totul clar, fără pași inutili. Îi recomand dacă aveți nevoie de ajutor juridic.',
	),
);

$pll_default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : '';
$ua_posts   = get_posts( array(
	'post_type'      => 'reviews',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'lang'           => $pll_default ? $pll_default : '',
) );

$updated = 0;
foreach ( $ua_posts as $post ) {
	$content_ua = $post->post_content;
	if ( ! isset( $ro_reviews[ $content_ua ] ) ) {
		continue;
	}
	$ro_post_id = (int) pll_get_post( $post->ID, 'ro' );
	if ( ! $ro_post_id ) {
		continue;
	}
	$ro = $ro_reviews[ $content_ua ];
	$ro_title = $ro['name'];

	wp_update_post( array(
		'ID'           => $ro_post_id,
		'post_title'   => $ro_title,
		'post_content' => $ro['text'],
	) );
	update_post_meta( $ro_post_id, 'person_name', $ro['name'] );
	// Case category is set via ACF field "case_category" only (Polylang may copy from UA).

	$updated++;
	echo "Translated RO review: {$ro['name']} (ID: {$ro_post_id}).\n";
}

echo "Done. Translated {$updated} review(s) to Romanian.\n";
