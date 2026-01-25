# Elementor MCP Tool System

## Architecture Overview

The tool system provides a robust, extensible framework for creating MCP (Model Context Protocol) tools that interact with Elementor. It follows Elementor's architectural patterns for consistency and maintainability.

## Design Patterns Used

### 1. Abstract Base Class Pattern (from Widget_Base)
- **Base_Tool**: Abstract class that all tools must extend
- Forces implementation of required methods: `get_name()`, `get_description()`, `get_input_schema()`, `execute()`
- Provides common functionality like validation, error handling, and hooks

### 2. Registry Pattern (from Controls_Manager)
- **Tool_Manager**: Central registry for all tools
- Manages registration, retrieval, and lifecycle of tools
- Auto-discovery of tools in subdirectories
- Singleton-like access through manager instance

### 3. Hook System (from Controls_Stack)
- WordPress action hooks at key points
- `before_execute` and `after_execute` hooks for each tool
- Global and tool-specific hooks for extensibility
- Allows third-party developers to extend tool behavior

### 4. Validation Pattern
- **Schema_Validator**: JSON schema validation
- Fallback validation when library not available
- Consistent error reporting
- Type checking and constraint validation

## File Structure

```
includes/tools/
├── class-base-tool.php           # Abstract base class for all tools
├── class-tool-manager.php        # Tool registry and manager
├── class-schema-validator.php    # JSON schema validation
├── elementor/                    # Elementor-specific tools
│   └── class-get-info-tool.php  # Example tool
├── template/                     # Template-related tools
└── widget/                       # Widget-related tools
```

## Creating a New Tool

### Step 1: Create Tool Class

Create a new file in the appropriate subdirectory (e.g., `elementor/class-my-tool.php`):

```php
<?php
namespace ElementorMCP\Tools;

class My_Tool extends Base_Tool {

    public function get_name() {
        return 'my_tool_name';
    }

    public function get_description() {
        return 'Description of what this tool does';
    }

    public function get_input_schema() {
        return array(
            'type' => 'object',
            'properties' => array(
                'param1' => array(
                    'type' => 'string',
                    'description' => 'First parameter',
                ),
            ),
            'required' => array( 'param1' ),
        );
    }

    public function execute( $args ) {
        // Verify Elementor is active
        if ( ! $this->verify_elementor_active() ) {
            return $this->format_error(
                'Elementor is not active',
                'elementor_not_active'
            );
        }

        // Check permissions
        if ( ! $this->check_permissions( 'edit_posts' ) ) {
            return $this->format_error(
                'Insufficient permissions',
                'permission_denied'
            );
        }

        // Your tool logic here
        $result = array(
            'message' => 'Tool executed successfully',
        );

        return $this->format_success( $result );
    }
}
```

### Step 2: Auto-Discovery

The Tool_Manager automatically discovers and registers tools in these directories:
- `elementor/` - Elementor core functionality tools
- `template/` - Template management tools
- `widget/` - Widget manipulation tools

Files must follow naming convention: `class-*-tool.php`

### Step 3: Manual Registration (Optional)

If you need to register a tool manually:

```php
add_action( 'elementor_mcp/tools/manager_init', function( $manager ) {
    $manager->register_tool( new My_Custom_Tool() );
} );
```

## Base Tool Methods

### Required Abstract Methods

- **get_name()**: Unique tool identifier (snake_case)
- **get_description()**: Human-readable description
- **get_input_schema()**: JSON schema for input validation
- **execute($args)**: Main execution logic

### Helper Methods

- **verify_elementor_active()**: Check if Elementor is loaded
- **check_permissions($capability)**: Verify user capabilities
- **validate_input($args)**: Validate against schema
- **format_error($message, $code, $data)**: Standard error response
- **format_success($data, $message)**: Standard success response

### Lifecycle Methods

- **before_execute($args)**: Called before execution
- **after_execute($args, $result)**: Called after execution
- **run($args)**: Public entry point with validation and hooks

## Schema Validation

### JSON Schema Format

The `get_input_schema()` method should return a valid JSON schema:

```php
return array(
    'type' => 'object',
    'properties' => array(
        'post_id' => array(
            'type' => 'integer',
            'description' => 'Post ID to process',
            'minimum' => 1,
        ),
        'action' => array(
            'type' => 'string',
            'enum' => array( 'create', 'update', 'delete' ),
            'description' => 'Action to perform',
        ),
    ),
    'required' => array( 'post_id', 'action' ),
    'additionalProperties' => false,
);
```

### Supported Validations

- Type checking: string, integer, number, boolean, array, object
- String: minLength, maxLength, pattern
- Number: minimum, maximum
- Array: minItems, maxItems
- Enums: Fixed list of allowed values
- Required properties

## Response Format

### Success Response

```php
array(
    'success' => true,
    'data' => array(
        // Your result data
    ),
    'message' => 'Optional success message',
)
```

### Error Response

```php
array(
    'success' => false,
    'error' => array(
        'code' => 'error_code',
        'message' => 'Human-readable error message',
        'data' => array(
            // Optional additional error data
        ),
    ),
)
```

## Hooks Reference

### Global Hooks

```php
// Manager initialization
do_action( 'elementor_mcp/tools/manager_init', $manager );

// After auto-discovery
do_action( 'elementor_mcp/tools/auto_discover_complete', $manager );

// Tool registered
do_action( 'elementor_mcp/tools/tool_registered', $tool_name, $tool_instance );

// Tool unregistered
do_action( 'elementor_mcp/tools/tool_unregistered', $tool_name );

// Before any tool execution
do_action( 'elementor_mcp/tool/before_execute', $tool_instance, $args );

// After any tool execution
do_action( 'elementor_mcp/tool/after_execute', $tool_instance, $args, $result );
```

### Tool-Specific Hooks

```php
// Before specific tool execution
do_action( "elementor_mcp/tool/{$tool_name}/before_execute", $tool_instance, $args );

// After specific tool execution
do_action( "elementor_mcp/tool/{$tool_name}/after_execute", $tool_instance, $args, $result );
```

### Filters

```php
// Filter tools list before sending to MCP
apply_filters( 'elementor_mcp/tools/list', $tools_list );
```

## Usage Examples

### Getting Tool Manager Instance

```php
$manager = new \ElementorMCP\Tools\Tool_Manager();
```

### Listing All Tools

```php
$tools_list = $manager->get_tools_list();
// Returns array formatted for MCP tools/list response
```

### Executing a Tool

```php
$result = $manager->execute_tool( 'get_elementor_info', array(
    'include_widgets' => true,
    'include_settings' => false,
) );

if ( $result['success'] ) {
    // Handle success
    $data = $result['data'];
} else {
    // Handle error
    $error = $result['error'];
}
```

### Checking if Tool Exists

```php
if ( $manager->is_tool_registered( 'my_tool' ) ) {
    $tool = $manager->get_tool( 'my_tool' );
}
```

## Best Practices

1. **Always validate input** - Use the schema validation system
2. **Check permissions** - Use `check_permissions()` before operations
3. **Verify Elementor** - Use `verify_elementor_active()` when needed
4. **Use standard responses** - Always use `format_success()` and `format_error()`
5. **Document your schema** - Include descriptions in JSON schema
6. **Handle errors gracefully** - Wrap risky operations in try-catch
7. **Follow naming conventions** - Use snake_case for tool names
8. **Add hooks** - Allow extensibility through WordPress action hooks

## Error Codes Convention

Use consistent error codes across tools:

- `elementor_not_active` - Elementor not loaded
- `permission_denied` - User lacks required capability
- `invalid_input` - Input validation failed
- `not_found` - Requested resource not found
- `execution_error` - Error during tool execution
- `invalid_tool` - Tool implementation error

## Integration with MCP Transport

The Tool_Manager integrates with the MCP transport layer:

```php
// In transport handler
$manager = new Tool_Manager();

// Handle tools/list request
$tools = $manager->get_tools_list();

// Handle tools/call request
$result = $manager->execute_tool( $tool_name, $arguments );
```

## Performance Considerations

1. **Lazy Loading** - Tools are only instantiated when needed
2. **Instance Caching** - Tool instances are cached after first use
3. **Auto-discovery** - Only runs once on init
4. **Validation** - Validation happens before execution to fail fast

## Dependencies

- **Required**: WordPress 5.0+, Elementor 3.0+
- **Optional**: `justinrainbow/json-schema` for advanced validation
- **Fallback**: Basic validation when library not available

## Security

1. **Capability Checks** - All tools should verify user permissions
2. **Input Validation** - All inputs validated against schema
3. **ABSPATH Check** - All files check for direct access
4. **Nonce Verification** - Should be implemented at transport layer
5. **Sanitization** - Sanitize all inputs and outputs appropriately
