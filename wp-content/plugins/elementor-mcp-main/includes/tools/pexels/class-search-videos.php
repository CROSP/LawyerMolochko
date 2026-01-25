<?php
/**
 * Search Videos Tool
 *
 * Search for videos on Pexels.
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
 * Search Videos Tool
 *
 * @since 1.0.0
 */
class Search_Videos extends Base_Tool {

	/**
	 * Get tool name
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'pexels_search_videos';
	}

	/**
	 * Get tool description
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_description() {
		return 'Search for high-quality stock videos on Pexels by keyword. Returns videos with various resolutions, durations, and download URLs.';
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
					'description' => 'Search query (e.g., "nature", "city", "people")',
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

		$result = $client->search_videos( $query, $args );

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				$result->get_error_code(),
				$result->get_error_data()
			);
		}

		// Format response for better readability
		$formatted_videos = array();
		if ( isset( $result['videos'] ) && is_array( $result['videos'] ) ) {
			foreach ( $result['videos'] as $video ) {
				$video_files = array();
				if ( isset( $video['video_files'] ) && is_array( $video['video_files'] ) ) {
					foreach ( $video['video_files'] as $file ) {
						$video_files[] = array(
							'id'         => $file['id'],
							'quality'    => $file['quality'],
							'file_type'  => $file['file_type'],
							'width'      => isset( $file['width'] ) ? $file['width'] : null,
							'height'     => isset( $file['height'] ) ? $file['height'] : null,
							'link'       => $file['link'],
						);
					}
				}

				$formatted_videos[] = array(
					'id'            => $video['id'],
					'width'         => $video['width'],
					'height'        => $video['height'],
					'duration'      => $video['duration'],
					'user'          => array(
						'name' => $video['user']['name'],
						'url'  => $video['user']['url'],
					),
					'url'           => $video['url'],
					'image'         => $video['image'],
					'video_files'   => $video_files,
					'video_pictures' => isset( $video['video_pictures'] ) ? $video['video_pictures'] : array(),
				);
			}
		}

		return $this->format_success(
			array(
				'total_results' => isset( $result['total_results'] ) ? $result['total_results'] : 0,
				'page'          => isset( $result['page'] ) ? $result['page'] : 1,
				'per_page'      => isset( $result['per_page'] ) ? $result['per_page'] : 15,
				'videos'        => $formatted_videos,
			),
			sprintf( 'Found %d videos for query: %s', count( $formatted_videos ), $query )
		);
	}
}
