<?php
/**
 * One-time: assign case study posts to term case-study-category/tczk-vidstrochka (ТЦК / відстрочка).
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/assign-case-studies-to-term-tczk-vidstrochka.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/assign-case-studies-to-term-tczk-vidstrochka.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! taxonomy_exists( 'case_study_category' ) || ! post_type_exists( 'molochko-case-study' ) ) {
	echo "Taxonomy or CPT not found.\n";
	exit( 1 );
}

$term = get_term_by( 'slug', 'tczk-vidstrochka', 'case_study_category' );
if ( ! $term || is_wp_error( $term ) ) {
	echo "Term with slug 'tczk-vidstrochka' not found.\n";
	exit( 1 );
}

// Case study titles that belong to TCC/відстрочка (UA and RO)
$titles_tcc = array(
	'Оскарження розшуку ТЦК та відновлення прав',
	'Оскарження рішення ВЛК: звільнення з військової служби',
	'Відстрочка від мобілізації: законні підстави',
	'Повістка ТЦК та неявка: консультація та супровід',
	'Contestarea declanșării în căutare de TCC și restabilirea drepturilor',
	'Contestarea deciziei VLC: eliberare din serviciul militar',
	'Amânarea de la mobilizare: temeiuri legale',
	'Citație TCC și neprezentare: consultație și însoțire',
);

$posts = get_posts( array(
	'post_type'      => 'molochko-case-study',
	'post_status'    => 'any',
	'posts_per_page' => -1,
	'fields'         => 'ids',
) );

$assigned = 0;
foreach ( $posts as $post_id ) {
	$title = get_the_title( $post_id );
	if ( ! in_array( $title, $titles_tcc, true ) ) {
		continue;
	}
	$term_id_to_assign = (int) $term->term_id;
	if ( function_exists( 'pll_get_post_language' ) && function_exists( 'pll_get_term' ) ) {
		$post_lang = pll_get_post_language( $post_id );
		if ( $post_lang ) {
			$translated_term_id = (int) pll_get_term( $term->term_id, $post_lang );
			if ( $translated_term_id ) {
				$term_id_to_assign = $translated_term_id;
			}
		}
	}
	wp_set_post_terms( $post_id, array( $term_id_to_assign ), 'case_study_category', false );
	$assigned++;
	echo "Assigned post ID {$post_id} ({$title}) to term ID {$term_id_to_assign}.\n";
}

echo "Done. Assigned {$assigned} case study post(s) to term tczk-vidstrochka.\n";
