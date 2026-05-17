<?php
/**
 * One-time: translate taxonomy term RO copies (case_study_category).
 * Updates Romanian term names so they display in Romanian on /ro/.
 * Run after RO term copies exist (Polylang).
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/translate-taxonomies-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/translate-taxonomies-ro.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

if ( ! function_exists( 'pll_get_term' ) || ! function_exists( 'pll_default_language' ) ) {
	echo "Polylang is required.\n";
	exit( 1 );
}

if ( ! taxonomy_exists( 'case_study_category' ) ) {
	echo "Taxonomy case_study_category not found.\n";
	exit( 1 );
}

// UA term name => RO term name (and optional slug for RO)
$ro_names = array(
	'Військове право'   => 'Drept militar',
	'Мобілізація'       => 'Mobilizare',
	'ДТП'               => 'Accident rutier',
	'Кримінальні справи' => 'Cauze penale',
	'Сімейні спори'     => 'Dispute familiale',
	'Трудові спори'     => 'Dispute muncitorești',
	'ТЦК / відстрочка'  => 'TCC / amânare',
	'Спадкування'       => 'Succesiune',
);

$args = array(
	'taxonomy'   => 'case_study_category',
	'hide_empty' => false,
	'fields'     => 'all',
);
// Prefer terms in default language if Polylang adds 'lang' to get_terms
$pll_default = pll_default_language( 'slug' );
if ( $pll_default ) {
	$ua_terms = get_terms( array_merge( $args, array( 'lang' => $pll_default ) ) );
} else {
	$ua_terms = array();
}
if ( is_wp_error( $ua_terms ) ) {
	$ua_terms = array();
}
if ( empty( $ua_terms ) ) {
	$ua_terms = get_terms( $args );
}
if ( is_wp_error( $ua_terms ) ) {
	$ua_terms = array();
}

$updated = 0;
foreach ( $ua_terms as $term ) {
	if ( is_wp_error( $term ) || ! isset( $term->term_id ) ) {
		continue;
	}
	$ua_name = $term->name;
	if ( ! isset( $ro_names[ $ua_name ] ) ) {
		continue;
	}
	$ro_term_id = (int) pll_get_term( $term->term_id, 'ro' );
	if ( ! $ro_term_id ) {
		continue;
	}
	$ro_name = $ro_names[ $ua_name ];
	$ro_slug = sanitize_title( $ro_name );
	$result = wp_update_term( $ro_term_id, 'case_study_category', array(
		'name' => $ro_name,
		'slug' => $ro_slug,
	) );
	if ( ! is_wp_error( $result ) ) {
		$updated++;
		echo "Translated term: {$ua_name} → {$ro_name} (RO term_id: {$ro_term_id}).\n";
	}
}

echo "Done. Updated {$updated} taxonomy term(s) to Romanian.\n";
