<?php
/**
 * RAG Index Tool
 *
 * Manages indexing of pages and kits in the RAG database.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Rag;

use ElementorMCP\Tools\Base_Tool;
use Elementor_MCP\Integrations\RAG_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RAG Index Tool
 *
 * Provides indexing management for the RAG database.
 */
class Rag_Index extends Base_Tool {

	/**
	 * RAG client instance.
	 *
	 * @var RAG_Client
	 */
	private $client;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load RAG client
		require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/integrations/class-rag-client.php';
		$this->client = RAG_Client::get_instance();
	}

	/**
	 * Get tool name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'rag_index';
	}

	/**
	 * Get tool description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Manage the RAG index: index pages, check status, or trigger bulk indexing. Use this to ensure the RAG database has the latest content.';
	}

	/**
	 * Get input schema.
	 *
	 * @return array
	 */
	public function get_input_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'action' => [
					'type'        => 'string',
					'enum'        => [ 'status', 'index_page', 'index_all_pages', 'index_kit', 'index_all_kits', 'list_pages' ],
					'description' => 'Action to perform: "status" to check index status, "index_page" to index a specific page, "index_all_pages" to index all Elementor pages, "index_kit" to index a training kit, "index_all_kits" to index all training kits, "list_pages" to list indexed pages.',
				],
				'post_id' => [
					'type'        => 'integer',
					'description' => 'WordPress post ID to index (required for "index_page" action).',
				],
				'kit_name' => [
					'type'        => 'string',
					'description' => 'Kit name to index (required for "index_kit" action). Available kits: restaurant, travel, coaching, consulting, fitness, marketing, real-estate, saas, etc.',
				],
			],
			'required'   => [ 'action' ],
		];
	}

	/**
	 * Execute the tool.
	 *
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args = [] ) {
		$action   = $args['action'] ?? '';
		$post_id  = $args['post_id'] ?? null;
		$kit_name = $args['kit_name'] ?? null;

		if ( empty( $action ) ) {
			return $this->format_error( 'Action is required', 'missing_action' );
		}

		// Check if RAG service is available
		if ( ! $this->client->is_available() ) {
			return $this->format_error(
				'RAG service is not available. Please ensure the Smart RAG service is running.',
				'service_unavailable'
			);
		}

		try {
			switch ( $action ) {
				case 'status':
					return $this->get_status();

				case 'index_page':
					if ( empty( $post_id ) ) {
						return $this->format_error( 'post_id is required for index_page action', 'missing_post_id' );
					}
					return $this->index_page( $post_id );

				case 'index_all_pages':
					return $this->index_all_pages();

				case 'index_kit':
					if ( empty( $kit_name ) ) {
						return $this->format_error( 'kit_name is required for index_kit action', 'missing_kit_name' );
					}
					return $this->index_kit( $kit_name );

				case 'index_all_kits':
					return $this->index_all_kits();

				case 'list_pages':
					return $this->list_indexed_pages();

				default:
					return $this->format_error( "Unknown action: {$action}", 'invalid_action' );
			}
		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'index_error'
			);
		}
	}

	/**
	 * Get index status.
	 *
	 * @return array
	 */
	private function get_status() {
		$result = $this->client->get_index_status();

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				'status_failed'
			);
		}

		return $this->format_success(
			[
				'collections'     => $result['collections'] ?? [],
				'total_documents' => $result['total_documents'] ?? 0,
			],
			sprintf( 'RAG index has %d total documents.', $result['total_documents'] ?? 0 )
		);
	}

	/**
	 * Index a specific page.
	 *
	 * @param int $post_id Post ID to index.
	 * @return array
	 */
	private function index_page( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return $this->format_error( "Post {$post_id} not found", 'post_not_found' );
		}

		$elements_data = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $elements_data ) ) {
			return $this->format_error( "Post {$post_id} has no Elementor data", 'no_elementor_data' );
		}

		if ( is_string( $elements_data ) ) {
			$elements_data = json_decode( $elements_data, true );
		}

		$result = $this->client->index_page(
			$post_id,
			$post->post_title,
			$elements_data,
			$post->post_type
		);

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				'index_failed'
			);
		}

		return $this->format_success(
			[
				'post_id'          => $post_id,
				'title'            => $post->post_title,
				'sections_indexed' => $result['sections_indexed'] ?? 0,
				'indexing_time_ms' => $result['indexing_time_ms'] ?? 0,
			],
			sprintf( 'Indexed page "%s" with %d sections.', $post->post_title, $result['sections_indexed'] ?? 0 )
		);
	}

	/**
	 * Index all Elementor pages.
	 *
	 * @return array
	 */
	private function index_all_pages() {
		// Load RAG hooks for bulk indexing
		require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/integrations/class-rag-hooks.php';
		$hooks = \Elementor_MCP\Integrations\RAG_Hooks::get_instance();

		$results = $hooks->bulk_index_all_pages();

		return $this->format_success(
			[
				'total'   => $results['total'],
				'indexed' => $results['indexed'],
				'failed'  => $results['failed'],
				'skipped' => $results['skipped'],
			],
			sprintf(
				'Bulk indexing complete: %d indexed, %d failed, %d skipped out of %d total.',
				$results['indexed'],
				$results['failed'],
				$results['skipped'],
				$results['total']
			)
		);
	}

	/**
	 * Index a training kit.
	 *
	 * @param string $kit_name Kit name to index.
	 * @return array
	 */
	private function index_kit( $kit_name ) {
		$result = $this->client->index_kit( $kit_name );

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				'kit_index_failed'
			);
		}

		return $this->format_success(
			[
				'kit_name'            => $result['kit_name'] ?? $kit_name,
				'templates_indexed'   => $result['stats']['templates_indexed'] ?? 0,
				'sections_indexed'    => $result['stats']['sections_indexed'] ?? 0,
				'patterns_indexed'    => $result['stats']['patterns_indexed'] ?? 0,
				'copywriting_indexed' => $result['stats']['copywriting_indexed'] ?? 0,
			],
			sprintf( 'Indexed kit "%s" successfully.', $kit_name )
		);
	}

	/**
	 * Index all training kits.
	 *
	 * @return array
	 */
	private function index_all_kits() {
		$result = $this->client->index_all_kits();

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				'kits_index_failed'
			);
		}

		return $this->format_success(
			[
				'kits_indexed'      => $result['kits_indexed'] ?? 0,
				'total_templates'   => $result['total_templates'] ?? 0,
				'total_sections'    => $result['total_sections'] ?? 0,
				'total_patterns'    => $result['total_patterns'] ?? 0,
				'total_copywriting' => $result['total_copywriting'] ?? 0,
			],
			sprintf( 'Indexed %d kits successfully.', $result['kits_indexed'] ?? 0 )
		);
	}

	/**
	 * List indexed pages.
	 *
	 * @return array
	 */
	private function list_indexed_pages() {
		$result = $this->client->get_indexed_pages();

		if ( is_wp_error( $result ) ) {
			return $this->format_error(
				$result->get_error_message(),
				'list_failed'
			);
		}

		$pages = $result['pages'] ?? [];

		return $this->format_success(
			[
				'pages' => array_map( function( $page ) {
					return [
						'post_id'       => $page['post_id'],
						'title'         => $page['title'],
						'section_count' => $page['section_count'],
					];
				}, $pages ),
				'count' => count( $pages ),
			],
			sprintf( 'Found %d indexed pages.', count( $pages ) )
		);
	}
}
