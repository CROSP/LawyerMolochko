<?php
/**
 * List Widgets Tool
 *
 * Gets all available Elementor widget types with their metadata.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Widgets;

use ElementorMCP\Base_Tool;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List all Elementor widgets
 */
class List_Widgets extends Base_Tool {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name        = 'list_widgets';
		$this->description = 'Get all available Elementor widget types with their properties (name, title, icon, categories, keywords).';
		$this->input_schema = [
			'type'       => 'object',
			'properties' => [
				'category' => [
					'type'        => 'string',
					'description' => 'Filter widgets by category (e.g., "basic", "general", "pro-elements"). Leave empty for all widgets.',
				],
				'search' => [
					'type'        => 'string',
					'description' => 'Search widgets by name, title, or keywords.',
				],
			],
		];
	}

	/**
	 * Execute the tool
	 *
	 * @param array $args Tool arguments.
	 * @return array
	 */
	public function execute( $args = [] ) {
		$verify = $this->verify_elementor();
		if ( is_wp_error( $verify ) ) {
			return $this->error( $verify );
		}

		try {
			$widgets_manager = Plugin::$instance->widgets_manager;
			$widget_types    = $widgets_manager->get_widget_types();

			if ( empty( $widget_types ) ) {
				return $this->success(
					[],
					__( 'No widgets found.', 'elementor-mcp' )
				);
			}

			$widgets_data = [];

			foreach ( $widget_types as $widget_name => $widget ) {
				// Get widget metadata.
				$widget_info = [
					'name'       => $widget->get_name(),
					'title'      => $widget->get_title(),
					'icon'       => $widget->get_icon(),
					'categories' => $widget->get_categories(),
					'keywords'   => $widget->get_keywords(),
					'type'       => $widget->get_type(),
				];

				// Add optional properties if they exist.
				if ( method_exists( $widget, 'show_in_panel' ) ) {
					$widget_info['show_in_panel'] = $widget->show_in_panel();
				}

				if ( method_exists( $widget, 'hide_on_search' ) ) {
					$widget_info['hide_on_search'] = $widget->hide_on_search();
				}

				// Skip is_dynamic_content as it may be protected in some widget classes

				// Apply filters if provided.
				$category_filter = $args['category'] ?? '';
				$search_filter   = $args['search'] ?? '';

				// Filter by category.
				if ( ! empty( $category_filter ) ) {
					if ( ! in_array( $category_filter, $widget_info['categories'], true ) ) {
						continue;
					}
				}

				// Filter by search term.
				if ( ! empty( $search_filter ) ) {
					$search_term = strtolower( $search_filter );
					$searchable  = strtolower(
						$widget_info['name'] . ' ' .
						$widget_info['title'] . ' ' .
						implode( ' ', $widget_info['keywords'] )
					);

					if ( false === strpos( $searchable, $search_term ) ) {
						continue;
					}
				}

				$widgets_data[ $widget_name ] = $widget_info;
			}

			return $this->success(
				$widgets_data,
				sprintf(
					/* translators: %d: number of widgets */
					__( 'Found %d widgets.', 'elementor-mcp' ),
					count( $widgets_data )
				)
			);

		} catch ( \Exception $e ) {
			return $this->error(
				$e->getMessage(),
				'widget_list_error'
			);
		}
	}
}
