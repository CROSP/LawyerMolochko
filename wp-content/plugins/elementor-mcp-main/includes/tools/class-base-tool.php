<?php
/**
 * Base Tool Abstract Class
 *
 * Provides the foundation for all MCP tools following Elementor's architecture patterns.
 * Mirrors the design patterns from Widget_Base and Controls_Stack.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract Base Tool Class
 *
 * All MCP tools must extend this class and implement the required abstract methods.
 * This provides a consistent interface and shared functionality across all tools.
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Base_Tool {

	/**
	 * Get tool name.
	 *
	 * Retrieve the tool name used for identification in the MCP protocol.
	 * This should be a unique identifier in snake_case format.
	 *
	 * @since 1.0.0
	 * @access public
	 * @abstract
	 *
	 * @return string The tool name.
	 */
	abstract public function get_name();

	/**
	 * Get tool description.
	 *
	 * Retrieve a human-readable description of what the tool does.
	 * This is displayed in the MCP tools list.
	 *
	 * @since 1.0.0
	 * @access public
	 * @abstract
	 *
	 * @return string The tool description.
	 */
	abstract public function get_description();

	/**
	 * Get input schema.
	 *
	 * Define the JSON schema for the tool's input parameters.
	 * This is used for validation and documentation.
	 *
	 * @since 1.0.0
	 * @access public
	 * @abstract
	 *
	 * @return array JSON schema array for input validation.
	 */
	abstract public function get_input_schema();

	/**
	 * Execute the tool.
	 *
	 * Main execution method that performs the tool's operation.
	 * This method receives validated input and returns the result.
	 *
	 * @since 1.0.0
	 * @access public
	 * @abstract
	 *
	 * @param array $args Input arguments for the tool.
	 * @return array Tool execution result.
	 */
	abstract public function execute( $args );

	/**
	 * Verify Elementor is active.
	 *
	 * Check if Elementor plugin is loaded and active.
	 * Tools should call this before performing Elementor-specific operations.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return bool True if Elementor is active, false otherwise.
	 */
	protected function verify_elementor_active() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check user permissions.
	 *
	 * Verify that the current user has the required capability.
	 * Uses WordPress capability system for permission checks.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $capability WordPress capability to check. Default is 'edit_posts'.
	 * @return bool True if user has capability, false otherwise.
	 */
	protected function check_permissions( $capability = 'edit_posts' ) {
		return current_user_can( $capability );
	}

	/**
	 * Validate input.
	 *
	 * Validate input arguments against the tool's JSON schema.
	 * Returns true if valid, or WP_Error if validation fails.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Input arguments to validate.
	 * @return true|\WP_Error True if valid, WP_Error otherwise.
	 */
	protected function validate_input( $args ) {
		$schema = $this->get_input_schema();

		// Use Schema_Validator for validation
		$validator = new Schema_Validator();
		$is_valid = $validator->validate( $args, $schema );

		if ( ! $is_valid ) {
			$errors = $validator->get_validation_errors();
			return new \WP_Error(
				'invalid_input',
				'Input validation failed',
				array( 'errors' => $errors )
			);
		}

		return true;
	}

	/**
	 * Format error response.
	 *
	 * Create a standardized error response following MCP protocol.
	 * Ensures consistent error formatting across all tools.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message Error message.
	 * @param string $code    Error code. Default is 'error'.
	 * @param array  $data    Optional. Additional error data.
	 * @return array Formatted error response.
	 */
	protected function format_error( $message, $code = 'error', $data = array() ) {
		return array(
			'success' => false,
			'error'   => array(
				'code'    => $code,
				'message' => $message,
				'data'    => $data,
			),
		);
	}

	/**
	 * Format success response.
	 *
	 * Create a standardized success response following MCP protocol.
	 * Ensures consistent response formatting across all tools.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param mixed  $data    Result data to return.
	 * @param string $message Optional success message.
	 * @return array Formatted success response.
	 */
	protected function format_success( $data, $message = '' ) {
		$response = array(
			'success' => true,
			'data'    => $data,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return $response;
	}

	/**
	 * Get tool unique name.
	 *
	 * Some tools may need unique names for specific use cases.
	 * By default, returns the regular name.
	 * Similar to Controls_Stack::get_unique_name().
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Unique tool name.
	 */
	public function get_unique_name() {
		return $this->get_name();
	}

	/**
	 * Get tool type.
	 *
	 * Retrieve the tool type for categorization.
	 * Can be overridden by child classes to provide specific types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The tool type.
	 */
	public function get_type() {
		return 'tool';
	}

	/**
	 * Before execute hook.
	 *
	 * Fires before the tool execution.
	 * Can be used for logging, validation, or setup.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args Input arguments.
	 */
	protected function before_execute( $args ) {
		/**
		 * Before tool execution.
		 *
		 * Fires before a tool is executed.
		 *
		 * @since 1.0.0
		 *
		 * @param Base_Tool $this The tool instance.
		 * @param array     $args Input arguments.
		 */
		do_action( 'elementor_mcp/tool/before_execute', $this, $args );

		/**
		 * Before specific tool execution.
		 *
		 * Fires before a specific tool is executed.
		 * The dynamic portion of the hook name, `$tool_name`, refers to the tool name.
		 *
		 * @since 1.0.0
		 *
		 * @param Base_Tool $this The tool instance.
		 * @param array     $args Input arguments.
		 */
		do_action( "elementor_mcp/tool/{$this->get_name()}/before_execute", $this, $args );
	}

	/**
	 * After execute hook.
	 *
	 * Fires after the tool execution.
	 * Can be used for logging, cleanup, or post-processing.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array $args   Input arguments.
	 * @param array $result Execution result.
	 */
	protected function after_execute( $args, $result ) {
		/**
		 * After tool execution.
		 *
		 * Fires after a tool is executed.
		 *
		 * @since 1.0.0
		 *
		 * @param Base_Tool $this   The tool instance.
		 * @param array     $args   Input arguments.
		 * @param array     $result Execution result.
		 */
		do_action( 'elementor_mcp/tool/after_execute', $this, $args, $result );

		/**
		 * After specific tool execution.
		 *
		 * Fires after a specific tool is executed.
		 * The dynamic portion of the hook name, `$tool_name`, refers to the tool name.
		 *
		 * @since 1.0.0
		 *
		 * @param Base_Tool $this   The tool instance.
		 * @param array     $args   Input arguments.
		 * @param array     $result Execution result.
		 */
		do_action( "elementor_mcp/tool/{$this->get_name()}/after_execute", $this, $args, $result );
	}

	/**
	 * Run the tool with validation and hooks.
	 *
	 * This method wraps execute() with validation, permission checks, and hooks.
	 * External callers should use this method instead of execute() directly.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments.
	 * @return array Execution result.
	 */
	public function run( $args ) {
		// Validate input
		$validation = $this->validate_input( $args );
		if ( is_wp_error( $validation ) ) {
			return $this->format_error(
				$validation->get_error_message(),
				$validation->get_error_code(),
				$validation->get_error_data()
			);
		}

		// Before execute hook
		$this->before_execute( $args );

		// Execute the tool
		try {
			$result = $this->execute( $args );
		} catch ( \Exception $e ) {
			return $this->format_error(
				$e->getMessage(),
				'execution_error',
				array( 'trace' => $e->getTraceAsString() )
			);
		}

		// After execute hook
		$this->after_execute( $args, $result );

		return $result;
	}

	/**
	 * Get tool configuration.
	 *
	 * Retrieve the tool configuration for MCP protocol.
	 * This formats the tool data for the tools/list response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Tool configuration array.
	 */
	public function get_config() {
		return array(
			'name'        => $this->get_name(),
			'description' => $this->get_description(),
			'inputSchema' => $this->get_input_schema(),
		);
	}

	/**
	 * Get tool schema.
	 *
	 * Alias for get_config() to maintain compatibility with MCP_Server.
	 * The MCP_Server expects a get_schema() method.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Tool schema array.
	 */
	public function get_schema() {
		return array(
			'description' => $this->get_description(),
			'inputSchema' => $this->get_input_schema(),
		);
	}
}
