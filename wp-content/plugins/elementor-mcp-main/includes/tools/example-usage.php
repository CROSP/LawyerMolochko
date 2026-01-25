<?php
/**
 * Example Usage of Tool System
 *
 * This file demonstrates how to use the tool system.
 * NOT meant to be loaded - for documentation purposes only.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

// This file is for documentation only - do not include in production

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================================
// EXAMPLE 1: Basic Tool Manager Usage
// ============================================================================

// Initialize the tool manager
$tool_manager = new \ElementorMCP\Tools\Tool_Manager();

// Get all registered tools
$all_tools = $tool_manager->get_all_tools();
echo 'Registered tools: ' . count( $all_tools );

// Get tools list for MCP (formatted for protocol)
$tools_list = $tool_manager->get_tools_list();
/*
Returns:
[
    [
        'name' => 'get_elementor_info',
        'description' => 'Get information about Elementor...',
        'inputSchema' => [...]
    ],
    ...
]
*/

// ============================================================================
// EXAMPLE 2: Executing a Tool
// ============================================================================

// Execute a tool with arguments
$result = $tool_manager->execute_tool( 'get_elementor_info', array(
	'include_widgets' => true,
	'include_settings' => true,
) );

// Check result
if ( $result['success'] ) {
	// Success - access data
	$elementor_version = $result['data']['version'];
	$widget_count = $result['data']['widgets']['count'];

	echo "Elementor Version: {$elementor_version}";
	echo "Widget Count: {$widget_count}";
} else {
	// Error - handle error
	$error_code = $result['error']['code'];
	$error_message = $result['error']['message'];

	echo "Error ({$error_code}): {$error_message}";
}

// ============================================================================
// EXAMPLE 3: Creating a Custom Tool
// ============================================================================

class List_Templates_Tool extends \ElementorMCP\Tools\Base_Tool {

	public function get_name() {
		return 'list_templates';
	}

	public function get_description() {
		return 'List all Elementor templates with filtering options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'type' => array(
					'type'        => 'string',
					'description' => 'Template type to filter by',
					'enum'        => array( 'page', 'section', 'widget' ),
				),
				'limit' => array(
					'type'        => 'integer',
					'description' => 'Maximum number of templates to return',
					'minimum'     => 1,
					'maximum'     => 100,
					'default'     => 10,
				),
			),
			'additionalProperties' => false,
		);
	}

	public function execute( $args ) {
		// Verify Elementor
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error(
				'Elementor is not active',
				'elementor_not_active'
			);
		}

		// Check permissions
		if ( ! $this->check_permissions( 'edit_posts' ) ) {
			return $this->format_error(
				'You do not have permission to list templates',
				'permission_denied'
			);
		}

		// Query templates
		$query_args = array(
			'post_type'      => 'elementor_library',
			'posts_per_page' => isset( $args['limit'] ) ? $args['limit'] : 10,
			'post_status'    => 'publish',
		);

		if ( ! empty( $args['type'] ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'   => '_elementor_template_type',
					'value' => $args['type'],
				),
			);
		}

		$templates = get_posts( $query_args );

		// Format results
		$result = array();
		foreach ( $templates as $template ) {
			$result[] = array(
				'id'    => $template->ID,
				'title' => $template->post_title,
				'type'  => get_post_meta( $template->ID, '_elementor_template_type', true ),
				'date'  => $template->post_date,
			);
		}

		return $this->format_success(
			array(
				'templates' => $result,
				'count'     => count( $result ),
			),
			sprintf( 'Found %d templates', count( $result ) )
		);
	}
}

// ============================================================================
// EXAMPLE 4: Registering a Custom Tool
// ============================================================================

// Register during manager initialization
add_action( 'elementor_mcp/tools/manager_init', function( $manager ) {
	$manager->register_tool( new List_Templates_Tool() );
} );

// Or register manually if you have the manager instance
$tool_manager->register_tool( new List_Templates_Tool() );

// ============================================================================
// EXAMPLE 5: Using Hooks
// ============================================================================

// Log all tool executions
add_action( 'elementor_mcp/tool/before_execute', function( $tool, $args ) {
	error_log( sprintf(
		'Executing tool: %s with args: %s',
		$tool->get_name(),
		wp_json_encode( $args )
	) );
}, 10, 2 );

// Modify results of specific tool
add_action( 'elementor_mcp/tool/get_elementor_info/after_execute', function( $tool, $args, $result ) {
	// Add custom data to result
	if ( $result['success'] ) {
		$result['data']['custom_field'] = 'custom_value';
	}
}, 10, 3 );

// Filter tools list before sending to client
add_filter( 'elementor_mcp/tools/list', function( $tools_list ) {
	// Remove sensitive tools for non-admin users
	if ( ! current_user_can( 'manage_options' ) ) {
		$tools_list = array_filter( $tools_list, function( $tool ) {
			return ! in_array( $tool['name'], array( 'dangerous_tool' ) );
		} );
	}
	return $tools_list;
} );

// ============================================================================
// EXAMPLE 6: Validation Examples
// ============================================================================

// Schema with various validation rules
public function get_input_schema() {
	return array(
		'type'       => 'object',
		'properties' => array(
			// String with length constraints
			'title' => array(
				'type'      => 'string',
				'minLength' => 3,
				'maxLength' => 100,
			),
			// Number with range
			'post_id' => array(
				'type'    => 'integer',
				'minimum' => 1,
			),
			// Enum (fixed values)
			'status' => array(
				'type' => 'string',
				'enum' => array( 'draft', 'publish', 'private' ),
			),
			// Array with item constraints
			'tags' => array(
				'type'     => 'array',
				'items'    => array( 'type' => 'string' ),
				'minItems' => 1,
				'maxItems' => 10,
			),
			// Nested object
			'meta' => array(
				'type'       => 'object',
				'properties' => array(
					'author' => array( 'type' => 'string' ),
					'date'   => array( 'type' => 'string' ),
				),
			),
		),
		'required'             => array( 'title', 'post_id' ),
		'additionalProperties' => false,
	);
}

// ============================================================================
// EXAMPLE 7: Error Handling
// ============================================================================

public function execute( $args ) {
	try {
		// Risky operation
		$result = $this->perform_complex_operation( $args );

		if ( false === $result ) {
			return $this->format_error(
				'Operation failed',
				'operation_failed',
				array( 'attempted_args' => $args )
			);
		}

		return $this->format_success( $result );

	} catch ( \Exception $e ) {
		return $this->format_error(
			$e->getMessage(),
			'exception',
			array( 'trace' => $e->getTraceAsString() )
		);
	}
}

// ============================================================================
// EXAMPLE 8: Getting Specific Tool
// ============================================================================

// Get a specific tool instance
$tool = $tool_manager->get_tool( 'get_elementor_info' );

if ( $tool ) {
	// Get tool configuration
	$config = $tool->get_config();

	// Execute directly (skips manager)
	$result = $tool->run( array( 'include_widgets' => true ) );

	// Get tool metadata
	$name = $tool->get_name();
	$description = $tool->get_description();
	$schema = $tool->get_input_schema();
}

// ============================================================================
// EXAMPLE 9: Checking Tool Registration
// ============================================================================

if ( $tool_manager->is_tool_registered( 'list_templates' ) ) {
	echo 'Template listing tool is available';
}

// Get all registered tool names
$tool_names = $tool_manager->get_tool_names();
foreach ( $tool_names as $name ) {
	echo "Available tool: {$name}\n";
}

// Get count
$count = $tool_manager->get_tool_count();
echo "Total tools registered: {$count}";

// ============================================================================
// EXAMPLE 10: Integration with MCP Transport
// ============================================================================

// In your MCP message handler
function handle_mcp_request( $message ) {
	$tool_manager = new \ElementorMCP\Tools\Tool_Manager();

	switch ( $message['method'] ) {
		case 'tools/list':
			return array(
				'tools' => $tool_manager->get_tools_list(),
			);

		case 'tools/call':
			$tool_name = $message['params']['name'];
			$arguments = $message['params']['arguments'] ?? array();

			return $tool_manager->execute_tool( $tool_name, $arguments );

		default:
			return array(
				'error' => array(
					'code'    => 'unknown_method',
					'message' => 'Unknown method: ' . $message['method'],
				),
			);
	}
}
