<?php
/**
 * One-time: replace testimonials on Reviews page (4666) with thematic Ukrainian legal reviews.
 * Run: ddev exec wp eval-file wp-content/themes/molochko/inc/import-reviews-to-page.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

$page_id = 4666;
$page = get_post( $page_id );
if ( ! $page || $page->post_type !== 'page' ) {
	echo "Reviews page 4666 not found.\n";
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

$raw = get_post_meta( $page_id, '_elementor_data', true );
if ( ! is_string( $raw ) ) {
	echo "No Elementor data.\n";
	exit( 1 );
}

$data = json_decode( $raw, true );
if ( ! is_array( $data ) ) {
	echo "Invalid JSON.\n";
	exit( 1 );
}

$index = 0;
function replace_testimonials_in_elements( &$elements, $reviews, &$index ) {
	if ( ! is_array( $elements ) ) return;
	foreach ( $elements as &$el ) {
		if ( ! is_array( $el ) ) continue;
		if ( isset( $el['widgetType'] ) && $el['widgetType'] === 'pxl_testimonial_single' && isset( $el['settings'] ) ) {
			if ( isset( $reviews[ $index ] ) ) {
				$r = $reviews[ $index ];
				$el['settings']['tt_content'] = '"' . $r['text'] . '"';
				$el['settings']['tt_description'] = $r['case_type'] !== '' ? $r['name'] . ', ' . $r['case_type'] : $r['name'];
				$index++;
			}
		}
		if ( ! empty( $el['elements'] ) ) {
			replace_testimonials_in_elements( $el['elements'], $reviews, $index );
		}
	}
}

replace_testimonials_in_elements( $data, $reviews, $index );

$new_raw = wp_slash( wp_json_encode( $data ) );
update_post_meta( $page_id, '_elementor_data', $new_raw );

echo "Updated {$index} testimonials on Reviews page (ID: {$page_id}). Front page reviews section now reads from this page.\n";
