<?php
/**
 * Get Elementor Info Tool
 *
 * Example tool that retrieves Elementor installation information.
 * Demonstrates the tool implementation pattern.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Info Tool Class
 *
 * Retrieves information about the Elementor installation.
 * This serves as an example of how to implement a tool.
 *
 * @since 1.0.0
 */
class Get_Info_Tool extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_elementor_info';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Get information about the Elementor installation including version, active widgets, and configuration.';
	}

	/**
	 * Get input schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JSON schema for inputs.
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'include_widgets' => array(
					'type'        => 'boolean',
					'description' => 'Whether to include list of registered widgets',
					'default'     => false,
				),
				'include_settings' => array(
					'type'        => 'boolean',
					'description' => 'Whether to include Elementor settings',
					'default'     => false,
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Execute the tool.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments.
	 * @return array Execution result.
	 */
	public function execute( $args ) {
		// Check if Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error(
				'Elementor is not active or loaded',
				'elementor_not_active'
			);
		}

		// Check permissions
		if ( ! $this->check_permissions( 'manage_options' ) ) {
			return $this->format_error(
				'You do not have permission to view Elementor information',
				'permission_denied'
			);
		}

		// Gather basic information
		$info = array(
			'version'     => ELEMENTOR_VERSION,
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' ),
			'is_active'   => true,
		);

		// Include widgets if requested
		if ( ! empty( $args['include_widgets'] ) ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
			$widget_types = $widgets_manager->get_widget_types();

			$info['widgets'] = array(
				'count' => count( $widget_types ),
				'types' => array_keys( $widget_types ),
			);
		}

		// Include settings if requested
		if ( ! empty( $args['include_settings'] ) ) {
			$info['settings'] = array(
				'edit_mode'      => \Elementor\Plugin::instance()->editor->is_edit_mode(),
				'preview_mode'   => \Elementor\Plugin::instance()->preview->is_preview_mode(),
				'experiments'    => $this->get_active_experiments(),
			);
		}

		return $this->format_success(
			$info,
			'Successfully retrieved Elementor information'
		);
	}

	/**
	 * Get active experiments.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array Active experiments.
	 */
	private function get_active_experiments() {
		$experiments = array();
		$experiments_manager = \Elementor\Plugin::instance()->experiments;

		foreach ( $experiments_manager->get_features() as $feature_name => $feature_data ) {
			if ( $experiments_manager->is_feature_active( $feature_name ) ) {
				$experiments[] = $feature_name;
			}
		}

		return $experiments;
	}
}
