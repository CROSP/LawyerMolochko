<?php
/**
 * Set API Key Tool
 *
 * Configure the Pexels API key.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Pexels;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set API Key Tool
 *
 * @since 1.0.0
 */
class Set_Api_Key extends Base_Tool {

	/**
	 * Get tool name
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'pexels_set_api_key';
	}

	/**
	 * Get tool description
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_description() {
		return 'Set or update the Pexels API key used for accessing Pexels services. Get your free API key at https://www.pexels.com/api/';
	}

	/**
	 * Get input schema
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'api_key' => array(
					'type'        => 'string',
					'description' => 'Pexels API key',
					'minLength'   => 10,
				),
			),
			'required'   => array( 'api_key' ),
		);
	}

	/**
	 * Execute tool
	 *
	 * @since 1.0.0
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args ) {
		// Check permissions
		if ( ! $this->check_permissions( 'manage_options' ) ) {
			return $this->format_error( 'Permission denied. User must have manage_options capability.', 'permission_denied' );
		}

		$api_key = sanitize_text_field( $args['api_key'] );

		// Update the option
		$updated = update_option( 'elementor_mcp_pexels_api_key', $api_key );

		if ( $updated || get_option( 'elementor_mcp_pexels_api_key' ) === $api_key ) {
			return $this->format_success(
				array(
					'api_key_set' => true,
					'key_preview' => substr( $api_key, 0, 10 ) . '...' . substr( $api_key, -4 ),
				),
				'Pexels API key has been successfully configured.'
			);
		}

		return $this->format_error( 'Failed to save API key.', 'update_failed' );
	}
}
