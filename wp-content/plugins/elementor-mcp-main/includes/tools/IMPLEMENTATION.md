# Tool System Implementation Guide

## Design Patterns Analysis

### 1. Abstract Base Class Pattern (Elementor Widget_Base)

**Source Reference**: `elementor/includes/base/widget-base.php`

**What We Learned**:
- All widgets extend an abstract base class that enforces required methods
- Base class provides common functionality (render, settings, controls)
- Abstract methods: `get_name()`, `get_title()`, `get_icon()`, `get_categories()`
- Protected helper methods for common operations

**How We Applied It**:
```php
abstract class Base_Tool {
    // Required abstract methods
    abstract public function get_name();
    abstract public function get_description();
    abstract public function get_input_schema();
    abstract public function execute( $args );

    // Helper methods available to all tools
    protected function verify_elementor_active() { }
    protected function check_permissions( $capability ) { }
    protected function format_error( $message, $code ) { }
    protected function format_success( $data ) { }
}
```

### 2. Registration & Management Pattern (Elementor Controls_Stack)

**Source Reference**: `elementor/includes/base/controls-stack.php`

**What We Learned**:
- Controls are registered through a manager
- Manager maintains registry of available controls
- Controls can be added, removed, and updated
- Stack-based approach for organizing controls in sections
- Injection points for adding controls at specific locations

**How We Applied It**:
```php
class Tool_Manager {
    private $tools = array();           // Registry
    private $tool_instances = array();  // Instance cache

    public function register_tool( Base_Tool $tool_instance ) {
        // Similar to register_widget_type()
    }

    public function get_tool( $tool_name ) {
        // Similar to get_widget_types()
        // Lazy instantiation with caching
    }

    public function get_all_tools() {
        // Similar to get_controls()
    }
}
```

### 3. Hooks & Extensibility Pattern

**What We Learned from Elementor**:
- WordPress action hooks at key lifecycle points
- Before/after hooks for major operations
- Both general hooks and specific hooks (using dynamic hook names)
- Filters for modifying data before use

**How We Applied It**:
```php
// In Base_Tool
protected function before_execute( $args ) {
    do_action( 'elementor_mcp/tool/before_execute', $this, $args );
    do_action( "elementor_mcp/tool/{$this->get_name()}/before_execute", $this, $args );
}

// In Tool_Manager
public function register_tool( $tool_instance ) {
    // ... registration logic ...
    do_action( 'elementor_mcp/tools/tool_registered', $tool_name, $tool_instance );
}
```

### 4. Validation Pattern

**Elementor Approach**:
- Controls have validators and sanitizers
- Settings are validated before being saved
- Schema-based validation for structured data

**Our Implementation**:
```php
class Schema_Validator {
    public function validate( $data, $schema ) {
        // JSON schema validation
        // Falls back to basic validation if library unavailable
    }

    private function fallback_validate( $data, $schema ) {
        // Required fields, type checking, constraints
    }
}
```

## Architecture Decisions

### 1. Why Separate Base_Tool, Tool_Manager, and Schema_Validator?

**Separation of Concerns**:
- **Base_Tool**: Tool-specific logic and interface
- **Tool_Manager**: Tool lifecycle and registry management
- **Schema_Validator**: Reusable validation logic

This mirrors Elementor's separation:
- Widget_Base vs Widgets_Manager
- Controls_Stack vs Controls_Manager
- Base_Data_Control (has validation logic)

### 2. Why Use JSON Schema?

**Standards-Based**:
- MCP protocol uses JSON schema for tool definitions
- Industry standard for API validation
- Self-documenting (schema IS documentation)
- Supports complex validation rules
- Compatible with many languages/tools

### 3. Why the `run()` Wrapper Method?

**Lifecycle Management**:
```php
public function run( $args ) {
    $validation = $this->validate_input( $args );
    if ( is_wp_error( $validation ) ) {
        return $this->format_error( ... );
    }

    $this->before_execute( $args );
    $result = $this->execute( $args );
    $this->after_execute( $args, $result );

    return $result;
}
```

This ensures:
- Validation always happens
- Hooks always fire
- Error handling is consistent
- Similar to how Elementor wraps `render()` with `render_content()`

### 4. Auto-Discovery vs Manual Registration?

**Both Supported**:

**Auto-Discovery** (default):
- Scans predefined directories
- Follows naming convention
- Zero configuration needed
- Similar to WordPress plugin/theme discovery

**Manual Registration** (advanced):
- For tools outside standard directories
- For conditional registration
- For third-party extensions

## Key Implementation Details

### 1. Tool Naming Convention

```
File: class-get-info-tool.php
Class: Get_Info_Tool
Name: get_elementor_info
```

**Rules**:
- Files: `class-{name}-tool.php`
- Classes: `{Name}_Tool` (PascalCase)
- Tool names: `{name}` (snake_case)

### 2. Response Format Standardization

**Success**:
```php
array(
    'success' => true,
    'data'    => $result_data,
    'message' => 'Optional message',
)
```

**Error**:
```php
array(
    'success' => false,
    'error'   => array(
        'code'    => 'error_code',
        'message' => 'Error description',
        'data'    => array(), // Optional context
    ),
)
```

This ensures MCP clients can consistently parse responses.

### 3. Permission Checking

**Multiple Levels**:
1. Tool validates input (schema)
2. Tool checks Elementor is active
3. Tool checks user capabilities
4. Tool executes operation

Similar to Elementor's permission system:
- User role checks
- Capability checks
- Context-aware permissions

### 4. Error Handling Strategy

**Defensive Programming**:
```php
// Check prerequisites
if ( ! $this->verify_elementor_active() ) {
    return $this->format_error( ... );
}

// Validate permissions
if ( ! $this->check_permissions( 'edit_posts' ) ) {
    return $this->format_error( ... );
}

// Try-catch for risky operations
try {
    $result = $this->risky_operation();
} catch ( \Exception $e ) {
    return $this->format_error( ... );
}
```

## Performance Optimizations

### 1. Lazy Loading

Tools are only instantiated when needed:
```php
public function get_tool( $tool_name ) {
    // Check cache first
    if ( isset( $this->tool_instances[ $tool_name ] ) ) {
        return $this->tool_instances[ $tool_name ];
    }

    // Create and cache
    $tool_instance = new $tool_class();
    $this->tool_instances[ $tool_name ] = $tool_instance;
    return $tool_instance;
}
```

### 2. Auto-Discovery Once

Auto-discovery only runs on `init` action, not on every request.
Results are stored in the registry.

### 3. Validation Fallback

If `justinrainbow/json-schema` is not available, falls back to basic validation.
Prevents dependency issues while maintaining functionality.

## Security Considerations

### 1. Input Validation

**Every tool input is validated**:
- Against JSON schema
- Required fields checked
- Type validation
- Constraint validation

### 2. Permission Checks

**Capability-based access**:
- Each tool defines required capability
- Checked before execution
- Uses WordPress capability system

### 3. Sanitization

Tools should sanitize:
- User inputs
- Database queries
- Output data

### 4. Direct Access Prevention

All files include:
```php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
```

## Extensibility Points

### 1. Hooks for Third-Party Developers

```php
// Modify tools list
add_filter( 'elementor_mcp/tools/list', function( $tools ) {
    // Add, remove, or modify tools
    return $tools;
} );

// Add custom behavior before tool execution
add_action( 'elementor_mcp/tool/before_execute', function( $tool, $args ) {
    // Logging, validation, etc.
}, 10, 2 );

// Modify tool results
add_action( 'elementor_mcp/tool/my_tool/after_execute', function( $tool, $args, $result ) {
    // Post-process results
}, 10, 3 );
```

### 2. Custom Tool Directories

Developers can add their own auto-discovery paths:
```php
add_action( 'elementor_mcp/tools/auto_discover_complete', function( $manager ) {
    $custom_dir = MY_PLUGIN_PATH . '/tools';
    // Scan custom directory and register
} );
```

### 3. Tool Inheritance

Create tool hierarchies:
```php
abstract class Elementor_Tool extends Base_Tool {
    // Common Elementor tool functionality
}

class Get_Widget_Tool extends Elementor_Tool {
    // Inherits Elementor-specific helpers
}
```

## Testing Strategy

### 1. Unit Tests

Test individual components:
- Schema_Validator validation logic
- Base_Tool helper methods
- Tool_Manager registration

### 2. Integration Tests

Test tool execution:
- Full lifecycle (register → execute → result)
- Hook firing
- Error handling

### 3. Example Tool as Test Case

`Get_Info_Tool` serves as:
- Implementation example
- Basic functionality test
- Pattern demonstration

## Future Enhancements

### 1. Tool Categories

Group tools by category:
```php
public function get_category() {
    return 'elementor'; // or 'template', 'widget', etc.
}
```

### 2. Tool Dependencies

Tools that depend on other tools:
```php
public function get_dependencies() {
    return array( 'get_elementor_info' );
}
```

### 3. Rate Limiting

Prevent abuse:
```php
protected function check_rate_limit() {
    // Transient-based rate limiting
}
```

### 4. Caching Layer

Cache tool results:
```php
protected function get_cached_result( $cache_key ) {
    return get_transient( $cache_key );
}
```

### 5. Async Execution

For long-running tools:
```php
public function execute_async( $args ) {
    // Use WordPress cron or background processing
}
```

## Integration with Other Components

### 1. MCP Transport Layer

Tool_Manager integrates with transport:
```php
// In SSE handler
$manager = new Tool_Manager();

if ( 'tools/list' === $method ) {
    return $manager->get_tools_list();
}

if ( 'tools/call' === $method ) {
    return $manager->execute_tool( $name, $args );
}
```

### 2. Logging Integration

Tools can integrate with logging:
```php
add_action( 'elementor_mcp/tool/after_execute', function( $tool, $args, $result ) {
    $logger = new Logger();
    $logger->log( $tool->get_name(), $args, $result );
} );
```

### 3. Metrics & Analytics

Track tool usage:
```php
add_action( 'elementor_mcp/tool/after_execute', function( $tool, $args, $result ) {
    // Send to analytics
    track_tool_usage( $tool->get_name(), $result['success'] );
} );
```

## Comparison with Elementor Patterns

| Elementor Pattern | Our Implementation | Purpose |
|-------------------|-------------------|---------|
| Widget_Base | Base_Tool | Abstract base class |
| Widgets_Manager | Tool_Manager | Registry & lifecycle |
| Controls_Stack | N/A (tools simpler) | Complex nested structure |
| get_name() | get_name() | Unique identifier |
| get_title() | get_description() | Human-readable name |
| render() | execute() | Main functionality |
| register_controls() | get_input_schema() | Define inputs |
| get_stack() | get_tools_list() | Get all items |
| add_control() | register_tool() | Add to registry |

## Summary

The tool system provides:

1. **Consistency**: Same patterns as Elementor
2. **Extensibility**: Hooks and filters throughout
3. **Validation**: Schema-based input validation
4. **Security**: Permission checks and sanitization
5. **Performance**: Lazy loading and caching
6. **Developer-Friendly**: Clear patterns and examples
7. **Standards-Based**: JSON schema, MCP protocol
8. **Maintainable**: Separation of concerns, clear architecture

This creates a solid foundation for building the 50+ tools needed for the Elementor MCP server.
