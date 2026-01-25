<?php
/**
 * RAG Search Tool
 *
 * Searches the RAG service for relevant templates, sections,
 * and copywriting examples.
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
 * RAG Search Tool
 *
 * Provides smart search across indexed templates and user pages.
 */
class Rag_Search extends Base_Tool {

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
		return 'rag_search';
	}

	/**
	 * Get tool description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Search the RAG database for relevant Elementor sections, templates, design patterns, and copywriting examples. Use this to find inspiration and reference material for page generation.';
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
				'query' => [
					'type'        => 'string',
					'description' => 'Natural language search query describing what you need (e.g., "hero section for restaurant", "pricing table with 3 plans", "testimonial carousel").',
				],
				'industry' => [
					'type'        => 'string',
					'description' => 'Target industry to filter results (restaurant, travel, coaching, consulting, fitness, marketing, real-estate, saas, ecommerce, portfolio, agency).',
				],
				'section_types' => [
					'type'        => 'array',
					'items'       => [ 'type' => 'string' ],
					'description' => 'Filter by section types (hero, features, pricing, testimonial, contact, cta, team, gallery, faq, stats, footer).',
				],
				'search_type' => [
					'type'        => 'string',
					'enum'        => [ 'all', 'sections', 'templates', 'copywriting' ],
					'description' => 'Type of search: "all" for comprehensive search, "sections" for section examples, "templates" for full templates, "copywriting" for text examples.',
					'default'     => 'all',
				],
				'max_results' => [
					'type'        => 'integer',
					'minimum'     => 1,
					'maximum'     => 50,
					'description' => 'Maximum number of results to return.',
					'default'     => 10,
				],
			],
			'required'   => [ 'query' ],
		];
	}

	/**
	 * Execute the tool.
	 *
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args = [] ) {
		$query         = $args['query'] ?? '';
		$industry      = $args['industry'] ?? null;
		$section_types = $args['section_types'] ?? null;
		$search_type   = $args['search_type'] ?? 'all';
		$max_results   = $args['max_results'] ?? 10;

		if ( empty( $query ) ) {
			return $this->format_error( 'Query is required', 'missing_query' );
		}

		// Check if RAG service is available
		if ( ! $this->client->is_available() ) {
			return $this->format_error(
				'RAG service is not available. Please ensure the Smart RAG service is running.',
				'service_unavailable'
			);
		}

		try {
			$result = null;

			switch ( $search_type ) {
				case 'sections':
					$result = $this->client->search_sections(
						$query,
						$section_types[0] ?? null,
						$industry,
						$max_results
					);
					break;

				case 'copywriting':
					$result = $this->client->search_copywriting(
						$query,
						null, // content_type
						$section_types[0] ?? null,
						$max_results
					);
					break;

				case 'templates':
				case 'all':
				default:
					$result = $this->client->search(
						$query,
						$industry,
						$section_types,
						$max_results,
						true // include user pages
					);
					break;
			}

			if ( is_wp_error( $result ) ) {
				return $this->format_error(
					$result->get_error_message(),
					'search_failed'
				);
			}

			// Format results for AI consumption
			$formatted = $this->format_results( $result, $search_type );

			return $this->format_success(
				$formatted,
				sprintf( 'Found %d relevant results for your query.', $formatted['total_results'] ?? 0 )
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'search_error'
			);
		}
	}

	/**
	 * Format search results for AI consumption.
	 *
	 * @param array  $result      Raw search results.
	 * @param string $search_type Type of search performed.
	 * @return array Formatted results.
	 */
	private function format_results( $result, $search_type ) {
		$formatted = [
			'query'         => $result['query'] ?? '',
			'total_results' => $result['total_results'] ?? $result['count'] ?? 0,
		];

		// Include query analysis if available
		if ( isset( $result['analysis'] ) ) {
			$formatted['detected_intent']   = $result['analysis']['intent'] ?? 'general';
			$formatted['detected_industry'] = $result['analysis']['industry'] ?? 'general';
		}

		// Format sections
		if ( ! empty( $result['sections'] ) ) {
			$formatted['sections'] = array_map( function( $section ) {
				return [
					'id'           => $section['id'] ?? '',
					'type'         => $section['metadata']['section_type'] ?? 'general',
					'widgets'      => $section['metadata']['widget_types'] ?? '',
					'columns'      => $section['metadata']['column_count'] ?? 1,
					'kit'          => $section['metadata']['kit'] ?? '',
					'description'  => substr( $section['document'] ?? '', 0, 300 ),
					'score'        => round( $section['score'] ?? 0, 3 ),
				];
			}, array_slice( $result['sections'], 0, 10 ) );
		}

		// Format templates
		if ( ! empty( $result['templates'] ) ) {
			$formatted['templates'] = array_map( function( $template ) {
				return [
					'id'            => $template['id'] ?? '',
					'title'         => $template['metadata']['title'] ?? '',
					'type'          => $template['metadata']['template_type'] ?? '',
					'sections'      => $template['metadata']['section_count'] ?? 0,
					'section_types' => $template['metadata']['section_types'] ?? '',
					'kit'           => $template['metadata']['kit'] ?? '',
					'score'         => round( $template['score'] ?? 0, 3 ),
				];
			}, array_slice( $result['templates'], 0, 5 ) );
		}

		// Format design patterns
		if ( ! empty( $result['patterns'] ) ) {
			$formatted['design_patterns'] = array_map( function( $pattern ) {
				return [
					'type' => $pattern['metadata']['pattern_type'] ?? '',
					'kit'  => $pattern['metadata']['kit'] ?? '',
					'data' => array_filter( $pattern['metadata'] ?? [], function( $key ) {
						return ! in_array( $key, [ 'pattern_type', 'kit' ], true );
					}, ARRAY_FILTER_USE_KEY ),
				];
			}, array_slice( $result['patterns'], 0, 5 ) );
		}

		// Format copywriting
		if ( ! empty( $result['copywriting'] ) || ! empty( $result['results'] ) ) {
			$copy_results = $result['copywriting'] ?? $result['results'] ?? [];
			$formatted['copywriting'] = array_map( function( $copy ) {
				return [
					'type'         => $copy['metadata']['content_type'] ?? 'text',
					'text'         => $copy['metadata']['text'] ?? $copy['document'] ?? '',
					'section_type' => $copy['metadata']['section_type'] ?? '',
					'kit'          => $copy['metadata']['kit'] ?? '',
				];
			}, array_slice( $copy_results, 0, 15 ) );
		}

		// Format user pages
		if ( ! empty( $result['user_pages'] ) ) {
			$formatted['user_pages'] = array_map( function( $page ) {
				return [
					'id'       => $page['id'] ?? '',
					'title'    => $page['metadata']['title'] ?? 'Untitled',
					'sections' => $page['metadata']['section_types'] ?? '',
				];
			}, array_slice( $result['user_pages'], 0, 3 ) );
		}

		return $formatted;
	}
}
