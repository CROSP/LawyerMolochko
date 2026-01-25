<?php
/**
 * RAG Context Tool
 *
 * Retrieves comprehensive context for AI-powered page/section generation.
 * Returns structured context including design patterns, reference sections,
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
 * RAG Context Tool
 *
 * Provides rich context for page and section generation.
 */
class Rag_Context extends Base_Tool {

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
		return 'rag_context';
	}

	/**
	 * Get tool description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Get rich context for AI-powered Elementor page/section generation. Returns design patterns, reference sections, copywriting examples, and user style context. Use this BEFORE generating any Elementor content to ensure context-rich output.';
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
					'description' => 'Description of what you want to create (e.g., "landing page for Italian restaurant", "hero section with video background", "pricing page for SaaS product").',
				],
				'context_type' => [
					'type'        => 'string',
					'enum'        => [ 'page', 'section', 'general' ],
					'description' => 'Type of context needed: "page" for full page generation, "section" for specific section, "general" for exploration.',
					'default'     => 'general',
				],
				'section_type' => [
					'type'        => 'string',
					'description' => 'Required when context_type is "section". The section type (hero, features, pricing, testimonial, contact, cta, team, gallery, faq, stats).',
				],
				'industry' => [
					'type'        => 'string',
					'description' => 'Target industry for context (restaurant, travel, coaching, consulting, fitness, marketing, real-estate, saas, ecommerce, portfolio, agency).',
				],
				'include_json' => [
					'type'        => 'boolean',
					'description' => 'Whether to include raw Elementor JSON for structural reference. Set to true when you need actual element structures.',
					'default'     => false,
				],
				'format' => [
					'type'        => 'string',
					'enum'        => [ 'structured', 'prompt' ],
					'description' => 'Output format: "structured" returns organized JSON, "prompt" returns a formatted text prompt.',
					'default'     => 'structured',
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
		$query        = $args['query'] ?? '';
		$context_type = $args['context_type'] ?? 'general';
		$section_type = $args['section_type'] ?? null;
		$industry     = $args['industry'] ?? null;
		$include_json = $args['include_json'] ?? false;
		$format       = $args['format'] ?? 'structured';

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

			switch ( $context_type ) {
				case 'section':
					if ( empty( $section_type ) ) {
						return $this->format_error(
							'section_type is required when context_type is "section"',
							'missing_section_type'
						);
					}
					$result = $this->client->get_section_context(
						$section_type,
						$query,
						$industry
					);
					break;

				case 'page':
					$result = $this->client->get_page_context(
						$query,
						$industry,
						null // page_type
					);
					break;

				case 'general':
				default:
					$result = $this->client->get_context(
						$query,
						$industry,
						null, // section_types
						$include_json,
						$format === 'prompt' ? 'prompt' : 'json'
					);
					break;
			}

			if ( is_wp_error( $result ) ) {
				return $this->format_error(
					$result->get_error_message(),
					'context_failed'
				);
			}

			// Format response based on requested format
			if ( 'prompt' === $format && ! empty( $result['prompt'] ) ) {
				return $this->format_success(
					[
						'prompt'   => $result['prompt'],
						'metadata' => $result['metadata'] ?? [],
					],
					'Context generated as prompt format.'
				);
			}

			// Return structured context
			$formatted = $this->format_context( $result, $context_type );

			return $this->format_success(
				$formatted,
				sprintf(
					'Generated %s context with %d reference sections and %d copywriting examples.',
					$context_type,
					count( $formatted['reference_sections'] ?? [] ),
					count( $formatted['copywriting'] ?? [] )
				)
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'context_error'
			);
		}
	}

	/**
	 * Format context for AI consumption.
	 *
	 * @param array  $result       Raw context results.
	 * @param string $context_type Type of context requested.
	 * @return array Formatted context.
	 */
	private function format_context( $result, $context_type ) {
		$context = $result['context'] ?? $result;

		$formatted = [
			'task_context' => $context['system_context'] ?? '',
			'metadata'     => $result['metadata'] ?? [],
		];

		// Design patterns
		if ( ! empty( $context['design_patterns'] ) ) {
			$formatted['design_patterns'] = $context['design_patterns'];
		}

		// Reference sections
		if ( ! empty( $context['reference_sections'] ) ) {
			$formatted['reference_sections'] = array_map( function( $section ) {
				return [
					'type'        => $section['type'] ?? 'general',
					'widgets'     => $section['widgets'] ?? '',
					'layout'      => $section['layout'] ?? '1-column',
					'features'    => $section['features'] ?? [],
					'description' => $section['description'] ?? '',
					'kit'         => $section['kit'] ?? '',
				];
			}, array_slice( $context['reference_sections'], 0, 10 ) );
		}

		// For section context, include sections_by_type if available
		if ( ! empty( $context['sections_by_type'] ) ) {
			$formatted['sections_by_type'] = [];
			foreach ( $context['sections_by_type'] as $type => $sections ) {
				$formatted['sections_by_type'][ $type ] = array_map( function( $section ) {
					return [
						'type'        => $section['type'] ?? 'general',
						'widgets'     => $section['widgets'] ?? '',
						'description' => $section['description'] ?? '',
					];
				}, array_slice( $sections, 0, 5 ) );
			}
		}

		// Reference templates (for page context)
		if ( ! empty( $context['reference_templates'] ) ) {
			$formatted['reference_templates'] = array_map( function( $template ) {
				return [
					'title'         => $template['title'] ?? '',
					'type'          => $template['type'] ?? '',
					'section_count' => $template['section_count'] ?? 0,
					'sections'      => $template['section_types'] ?? '',
					'kit'           => $template['kit'] ?? '',
				];
			}, array_slice( $context['reference_templates'], 0, 5 ) );
		}

		// Copywriting examples
		if ( ! empty( $context['copywriting_examples'] ) ) {
			$formatted['copywriting'] = array_map( function( $copy ) {
				return [
					'type'         => $copy['type'] ?? 'text',
					'text'         => $copy['text'] ?? '',
					'section_type' => $copy['section_type'] ?? '',
				];
			}, array_slice( $context['copywriting_examples'], 0, 15 ) );
		}

		// User's existing pages for style reference
		if ( ! empty( $context['user_context'] ) ) {
			$formatted['user_style_reference'] = array_map( function( $page ) {
				return [
					'title'    => $page['title'] ?? 'Untitled',
					'sections' => $page['sections'] ?? '',
				];
			}, array_slice( $context['user_context'], 0, 3 ) );
		}

		return $formatted;
	}
}
