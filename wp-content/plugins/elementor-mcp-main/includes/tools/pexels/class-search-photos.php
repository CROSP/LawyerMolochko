<?php
/**
 * Search Photos Tool
 *
 * Search for photos on Pexels.
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
 * Search Photos Tool
 *
 * @since 1.0.0
 */
class Search_Photos extends Base_Tool {

	/**
	 * Get tool name
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'pexels_search_photos';
	}

	/**
	 * Get tool description
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_description() {
		return 'Search for high-quality stock photos on Pexels by keyword. Returns photos with various sizes, photographer info, and download URLs.';
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
				'query'       => array(
					'type'        => 'string',
					'description' => 'Search query (e.g., "sunset", "office", "technology")',
					'minLength'   => 1,
				),
				'per_page'    => array(
					'type'        => 'integer',
					'description' => 'Number of results per page (1-80)',
					'minimum'     => 1,
					'maximum'     => 80,
					'default'     => 15,
				),
				'page'        => array(
					'type'        => 'integer',
					'description' => 'Page number for pagination',
					'minimum'     => 1,
					'default'     => 1,
				),
				'orientation' => array(
					'type'        => 'string',
					'description' => 'Filter by orientation',
					'enum'        => array( 'landscape', 'portrait', 'square' ),
				),
				'size'        => array(
					'type'        => 'string',
					'description' => 'Filter by minimum size',
					'enum'        => array( 'large', 'medium', 'small' ),
				),
				'color'       => array(
					'type'        => 'string',
					'description' => 'Filter by color (e.g., "red", "orange", "yellow", "green", "turquoise", "blue", "violet", "pink", "brown", "black", "gray", "white")',
				),
				'locale'      => array(
					'type'        => 'string',
					'description' => 'Locale for the search (e.g., "en-US", "pt-BR", "es-ES", "fr-FR")',
					'default'     => 'en-US',
				),
			),
			'required'   => array( 'query' ),
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

		$query = $args['query'];
		unset( $args['query'] );

		$result = $client->search_photos( $query, $args );

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				$result->get_error_code(),
				$result->get_error_data()
			);
		}

		// Format response for better readability
		$formatted_photos = array();
		if ( isset( $result['photos'] ) && is_array( $result['photos'] ) ) {
			foreach ( $result['photos'] as $photo ) {
				$formatted_photos[] = array(
					'id'           => $photo['id'],
					'width'        => $photo['width'],
					'height'       => $photo['height'],
					'photographer' => $photo['photographer'],
					'alt'          => isset( $photo['alt'] ) ? $photo['alt'] : '',
					'url'          => $photo['url'],
					'sizes'        => array(
						'original'  => $photo['src']['original'],
						'large2x'   => $photo['src']['large2x'],
						'large'     => $photo['src']['large'],
						'medium'    => $photo['src']['medium'],
						'small'     => $photo['src']['small'],
						'portrait'  => $photo['src']['portrait'],
						'landscape' => $photo['src']['landscape'],
						'tiny'      => $photo['src']['tiny'],
					),
					'avg_color'    => isset( $photo['avg_color'] ) ? $photo['avg_color'] : '',
				);
			}
		}

		return $this->format_success(
			array(
				'total_results' => isset( $result['total_results'] ) ? $result['total_results'] : 0,
				'page'          => isset( $result['page'] ) ? $result['page'] : 1,
				'per_page'      => isset( $result['per_page'] ) ? $result['per_page'] : 15,
				'photos'        => $formatted_photos,
			),
			sprintf( 'Found %d photos for query: %s', count( $formatted_photos ), $query )
		);
	}
}
