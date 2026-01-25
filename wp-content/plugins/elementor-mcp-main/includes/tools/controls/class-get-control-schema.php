<?php
/**
 * Get Control Schema Tool
 *
 * Retrieves detailed schema and configuration for a specific control type.
 * Provides all available options, defaults, and validation rules.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Controls;

use ElementorMCP\Tools\Base_Tool;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get Control Schema Tool
 *
 * Retrieves comprehensive schema information for a specific control type,
 * including configuration options, default values, and validation rules.
 *
 * @since 1.0.0
 */
class Get_Control_Schema extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'get_control_schema';
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
		return 'Get detailed schema and configuration for a specific Elementor control type. Returns control definition, default values, available options, and usage examples.';
	}

	/**
	 * Get input schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JSON schema for input validation.
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'control_type' => array(
					'type'        => 'string',
					'description' => 'The control type to get schema for (e.g., "text", "color", "slider", "dimensions").',
				),
			),
			'required' => array( 'control_type' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Retrieves detailed schema for a specific control type including
	 * all configuration options, defaults, and capabilities.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Tool arguments.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error(
				'Elementor is not active or loaded.',
				'elementor_not_active'
			);
		}

		// Check permissions
		if ( ! $this->check_permissions( 'edit_posts' ) ) {
			return $this->format_error(
				'Insufficient permissions to access control schema.',
				'permission_denied'
			);
		}

		$control_type = $args['control_type'];

		try {
			$controls_manager = Plugin::$instance->controls_manager;
			$control = $controls_manager->get_control( $control_type );

			if ( ! $control ) {
				return $this->format_error(
					sprintf( 'Control type "%s" not found.', $control_type ),
					'control_not_found',
					array( 'control_type' => $control_type )
				);
			}

			$schema = array(
				'type'  => $control_type,
				'class' => get_class( $control ),
			);

			// Get control settings
			if ( method_exists( $control, 'get_settings' ) ) {
				$schema['settings'] = $control->get_settings();
			}

			// Get default value for data controls
			$is_data_control = is_a( $control, '\Elementor\Base_Data_Control' );
			$schema['is_data_control'] = $is_data_control;

			if ( $is_data_control && method_exists( $control, 'get_default_value' ) ) {
				$schema['default_value'] = $control->get_default_value();
			}

			// Get type constant
			if ( method_exists( $control, 'get_type' ) ) {
				$schema['type_constant'] = $control->get_type();
			}

			// Get available properties/options based on control type
			$schema['available_properties'] = $this->get_control_properties( $control_type, $control );

			// Get usage examples
			$schema['usage_examples'] = $this->get_usage_examples( $control_type );

			// Get control-specific features
			$schema['features'] = $this->get_control_features( $control );

			return $this->format_success(
				$schema,
				sprintf( 'Schema retrieved for control type: %s', $control_type )
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'execution_error',
				array( 'trace' => $e->getTraceAsString() )
			);
		}
	}

	/**
	 * Get control-specific properties.
	 *
	 * Returns common properties available for the control type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $control_type Control type.
	 * @param object $control      Control instance.
	 * @return array Available properties.
	 */
	private function get_control_properties( $control_type, $control ) {
		// Common properties for all data controls
		$common_properties = array(
			'type'        => 'Control type identifier',
			'label'       => 'Control label shown in the panel',
			'description' => 'Help text shown below the control',
			'default'     => 'Default value for the control',
			'separator'   => 'Separator before/after control (before, after, none)',
			'show_label'  => 'Whether to show the label (true/false)',
			'label_block' => 'Whether label should be on separate line (true/false)',
			'condition'   => 'Conditions for showing this control',
			'conditions'  => 'Advanced conditions using logic operators',
			'tab'         => 'Tab where control appears (content, style, advanced)',
			'section'     => 'Section ID this control belongs to',
		);

		// Control-specific properties
		$specific_properties = array();

		switch ( $control_type ) {
			case 'text':
			case 'textarea':
				$specific_properties = array(
					'placeholder'  => 'Placeholder text',
					'input_type'   => 'HTML input type (text, email, url, etc.)',
					'dynamic'      => 'Enable dynamic content support',
					'ai'           => 'AI text generation settings',
				);
				break;

			case 'number':
				$specific_properties = array(
					'min'   => 'Minimum value',
					'max'   => 'Maximum value',
					'step'  => 'Step increment',
					'placeholder' => 'Placeholder text',
				);
				break;

			case 'color':
				$specific_properties = array(
					'alpha'     => 'Enable alpha/transparency (true/false)',
					'global'    => 'Enable global colors support',
					'selectors' => 'CSS selectors to apply color to',
				);
				break;

			case 'select':
			case 'select2':
				$specific_properties = array(
					'options'  => 'Array of value => label options',
					'multiple' => 'Allow multiple selection (select2 only)',
					'label_block' => 'Whether to show as block',
				);
				break;

			case 'slider':
				$specific_properties = array(
					'size_units' => 'Available units (px, em, %, vh, etc.)',
					'range'      => 'Min/max values for each unit',
					'default'    => 'Default value with unit',
					'selectors'  => 'CSS selectors to apply value to',
				);
				break;

			case 'dimensions':
				$specific_properties = array(
					'size_units' => 'Available units (px, em, %, vh, etc.)',
					'allowed_dimensions' => 'Which dimensions to show (top, right, bottom, left)',
					'placeholder' => 'Placeholder for each dimension',
					'selectors'  => 'CSS selectors to apply values to',
				);
				break;

			case 'choose':
				$specific_properties = array(
					'options'   => 'Array of options with icon and title',
					'toggle'    => 'Allow deselecting (true/false)',
					'selectors' => 'CSS selectors to apply value to',
				);
				break;

			case 'media':
				$specific_properties = array(
					'media_type' => 'Allowed media types (image, video)',
					'dynamic'    => 'Enable dynamic media support',
				);
				break;

			case 'repeater':
				$specific_properties = array(
					'fields'       => 'Array of field definitions',
					'default'      => 'Default rows',
					'title_field'  => 'Field to use for row title',
					'prevent_empty' => 'Prevent empty repeater',
				);
				break;

			case 'wysiwyg':
				$specific_properties = array(
					'dynamic' => 'Enable dynamic content support',
					'ai'      => 'AI text generation settings',
				);
				break;

			case 'url':
				$specific_properties = array(
					'placeholder'   => 'URL placeholder',
					'options'       => 'Additional link options (is_external, nofollow)',
					'show_external' => 'Show "open in new window" option',
					'dynamic'       => 'Enable dynamic URL support',
				);
				break;

			case 'code':
				$specific_properties = array(
					'language' => 'Code language (html, css, javascript)',
					'rows'     => 'Number of rows to display',
				);
				break;

			case 'icons':
				$specific_properties = array(
					'fa4compatibility' => 'Font Awesome 4 compatibility mode',
					'skin'             => 'Icon picker skin',
					'exclude_inline_options' => 'Inline options to exclude',
				);
				break;

			case 'gallery':
				$specific_properties = array(
					'default' => 'Default gallery images',
				);
				break;

			case 'switcher':
				$specific_properties = array(
					'label_on'  => 'Label for ON state',
					'label_off' => 'Label for OFF state',
					'return_value' => 'Value when switched ON',
				);
				break;
		}

		return array_merge( $common_properties, $specific_properties );
	}

	/**
	 * Get usage examples for control type.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $control_type Control type.
	 * @return array Usage examples.
	 */
	private function get_usage_examples( $control_type ) {
		$examples = array();

		switch ( $control_type ) {
			case 'text':
				$examples[] = array(
					'title'       => 'Basic text control',
					'code'        => '$this->add_control(\'title\', [\'type\' => Controls_Manager::TEXT, \'label\' => \'Title\', \'default\' => \'Heading\']);',
					'description' => 'Simple text input with default value',
				);
				break;

			case 'color':
				$examples[] = array(
					'title'       => 'Color with CSS selector',
					'code'        => '$this->add_control(\'text_color\', [\'type\' => Controls_Manager::COLOR, \'label\' => \'Color\', \'selectors\' => [\'{{WRAPPER}} .element\' => \'color: {{VALUE}}\']]);',
					'description' => 'Color control that automatically applies to CSS',
				);
				break;

			case 'slider':
				$examples[] = array(
					'title'       => 'Slider with units',
					'code'        => '$this->add_control(\'size\', [\'type\' => Controls_Manager::SLIDER, \'size_units\' => [\'px\', \'em\'], \'range\' => [\'px\' => [\'min\' => 0, \'max\' => 100]]]);',
					'description' => 'Slider with multiple size units',
				);
				break;

			case 'dimensions':
				$examples[] = array(
					'title'       => 'Padding/Margin control',
					'code'        => '$this->add_control(\'padding\', [\'type\' => Controls_Manager::DIMENSIONS, \'size_units\' => [\'px\', \'em\'], \'selectors\' => [\'{{WRAPPER}}\' => \'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}\']]);',
					'description' => 'Dimensions for padding or margin',
				);
				break;
		}

		return $examples;
	}

	/**
	 * Get control features.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param object $control Control instance.
	 * @return array Control features.
	 */
	private function get_control_features( $control ) {
		$features = array();

		// Check for global support
		if ( method_exists( $control, 'get_supported_global_values' ) ) {
			$features['supports_global'] = true;
			$features['global_types'] = $control->get_supported_global_values();
		}

		// Check for dynamic support
		if ( method_exists( $control, 'get_supported_dynamic_tags' ) ) {
			$features['supports_dynamic'] = true;
		}

		// Check for responsive support
		if ( property_exists( $control, 'responsive' ) || method_exists( $control, 'is_responsive' ) ) {
			$features['responsive_capable'] = true;
		}

		return $features;
	}
}
