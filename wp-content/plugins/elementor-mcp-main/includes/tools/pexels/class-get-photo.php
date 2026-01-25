<?php
/**
 * Get Photo Tool
 *
 * Get details of a specific Pexels photo by ID.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Pexels;

use ElementorMCP\Tools\Base_Tool;
use ElementorMCP\Integrations\Pexels_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Photo Tool
 *
 * @since 1.0.0
 */
class Get_Photo extends Base_Tool {

	/**
	 * Get tool name
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'pexels_get_photo';
	}

	/**
	 * Get tool description
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_description() {
		return 'Get detailed information about a specific Pexels photo by its ID, including all available sizes and photographer information.';
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
				'photo_id' => array(
					'type'        => 'integer',
					'description' => 'Pexels photo ID',
					'minimum'     => 1,
				),
			),
			'required'   => array( 'photo_id' ),
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
		$client = new Pexels_Client();

		$result = $client->get_photo( $args['photo_id'] );

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				$result->get_error_code(),
				$result->get_error_data()
			);
		}

		// Format response
		$formatted_photo = array(
			'id'           => $result['id'],
			'width'        => $result['width'],
			'height'       => $result['height'],
			'photographer' => $result['photographer'],
			'photographer_url' => isset( $result['photographer_url'] ) ? $result['photographer_url'] : '',
			'photographer_id'  => isset( $result['photographer_id'] ) ? $result['photographer_id'] : null,
			'alt'          => isset( $result['alt'] ) ? $result['alt'] : '',
			'url'          => $result['url'],
			'sizes'        => array(
				'original'  => $result['src']['original'],
				'large2x'   => $result['src']['large2x'],
				'large'     => $result['src']['large'],
				'medium'    => $result['src']['medium'],
				'small'     => $result['src']['small'],
				'portrait'  => $result['src']['portrait'],
				'landscape' => $result['src']['landscape'],
				'tiny'      => $result['src']['tiny'],
			),
			'avg_color'    => isset( $result['avg_color'] ) ? $result['avg_color'] : '',
			'liked'        => isset( $result['liked'] ) ? $result['liked'] : false,
		);

		return $this->format_success(
			$formatted_photo,
			sprintf( 'Retrieved photo #%d by %s', $result['id'], $result['photographer'] )
		);
	}
}
