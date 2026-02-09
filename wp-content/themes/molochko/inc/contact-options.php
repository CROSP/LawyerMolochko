<?php
/**
 * Site contact data from ACF Options page "Site Contact Details".
 * Field names: phone_number, email, working_hours (and optionally address, consultation_url).
 * Use molochko_get_contact_option() throughout the site.
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** ACF stores all options page values under 'option' (see ACF docs: Get values from an options page). */
define( 'MOLOCHKO_CONTACT_OPTIONS_SLUG', 'option' );

/** Map our internal key => ACF field name on Site Contact Details. */
define( 'MOLOCHKO_CONTACT_FIELD_MAP', serialize( array(
	'header_phone'            => 'phone_number',
	'header_email'            => 'email',
	'header_working_hours'     => 'working_hours',
	'header_address'          => 'address',
	'header_address_url'      => 'address_url',
	'header_consultation_url'  => 'consultation_url',
) ) );

/**
 * Get a contact option from ACF Options page "Site Contact Details".
 * Uses your field names: phone_number, email, working_hours, etc.
 *
 * @param string $name Internal key: header_phone, header_email, header_working_hours, header_address, header_address_url, header_consultation_url.
 * @return string
 */
function molochko_get_contact_option( $name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$map = unserialize( MOLOCHKO_CONTACT_FIELD_MAP );
	$acf_name = isset( $map[ $name ] ) ? $map[ $name ] : $name;
	$value = get_field( $acf_name, MOLOCHKO_CONTACT_OPTIONS_SLUG );
	return is_string( $value ) ? trim( $value ) : '';
}
