<?php
/**
 * Apply Romanian translations from polylang-strings-ro map to Polylang string translations.
 * Ensures CF7 form strings and other map entries get RO translations even if not yet registered.
 *
 * Run: wp eval-file wp-content/themes/molochko/inc/apply-polylang-strings-ro.php
 * Or:  ddev exec wp eval-file wp-content/themes/molochko/inc/apply-polylang-strings-ro.php
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/../../../../wp-load.php';
}

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
$sources = array_values( array_unique( array_merge( $sources, array_keys( $map ) ) ) );

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

echo "Updated Romanian Polylang strings: " . count( $ro_strings ) . " entries (" . $mapped . " translated from map). CF7 form labels should now show in Romanian on /ro/ pages.\n";
