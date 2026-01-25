# Tool System Architecture

## Directory Structure

```
includes/tools/
├── Core System (Base Architecture)
│   ├── class-base-tool.php          # Abstract base class for all tools
│   ├── class-tool-manager.php       # Tool registry and lifecycle manager
│   └── class-schema-validator.php   # JSON schema validation
│
├── Documentation
│   ├── README.md                    # User guide and API reference
│   ├── IMPLEMENTATION.md            # Design decisions and patterns
│   ├── AGENT4-SUMMARY.md           # Implementation summary
│   ├── ARCHITECTURE.md             # This file
│   └── example-usage.php           # Code examples
│
├── Tool Categories (Auto-Discovered)
│   ├── elementor/
│   │   └── class-get-info-tool.php # Example: Get Elementor info
│   │
│   ├── widgets/                     # Widget management tools (Agent 3)
│   │   ├── class-list-widgets.php
│   │   ├── class-get-widget-schema.php
│   │   ├── class-create-widget-instance.php
│   │   ├── class-get-widget-controls.php
│   │   └── README.md
│   │
│   └── template/                    # Template tools (future)
│       └── (To be implemented by Agent 5)
```

## Class Hierarchy

```
Base_Tool (Abstract)
├── Get_Info_Tool (elementor/)
├── List_Widgets (widgets/)
├── Get_Widget_Schema (widgets/)
├── Create_Widget_Instance (widgets/)
├── Get_Widget_Controls (widgets/)
└── [Future tools extend Base_Tool]
```

## Component Interaction Flow

```
┌─────────────────────────────────────────────────────────────┐
│                         MCP Client                           │
│                    (Claude, VS Code, etc.)                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ MCP Protocol
                     │ (tools/list, tools/call)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                     Transport Layer                          │
│              (SSE Server - Other Agents)                     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ PHP Method Calls
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    Tool_Manager                              │
│  ┌────────────────────────────────────────────────────┐     │
│  │ • register_tool($tool)                             │     │
│  │ • get_tool($name)                                  │     │
│  │ • execute_tool($name, $args)                       │     │
│  │ • get_tools_list()                                 │     │
│  │ • auto_discover_tools()                            │     │
│  └────────────────────────────────────────────────────┘     │
│                                                              │
│  Registry:                                                   │
│  ┌────────────────────────────────────────────────────┐     │
│  │ 'get_elementor_info' => Get_Info_Tool              │     │
│  │ 'list_widgets'       => List_Widgets               │     │
│  │ 'get_widget_schema'  => Get_Widget_Schema          │     │
│  │ ...                                                 │     │
│  └────────────────────────────────────────────────────┘     │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Instantiate & Execute
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                      Base_Tool                               │
│  ┌────────────────────────────────────────────────────┐     │
│  │ run($args) {                                       │     │
│  │   1. validate_input($args)                         │     │
│  │   2. before_execute($args)                         │     │
│  │   3. execute($args)         ← Tool implements      │     │
│  │   4. after_execute($args, $result)                 │     │
│  │ }                                                   │     │
│  └────────────────────────────────────────────────────┘     │
│                                                              │
│  Helpers:                                                    │
│  • verify_elementor_active()                                 │
│  • check_permissions($capability)                            │
│  • format_success($data)                                     │
│  • format_error($message, $code)                             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ Uses
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                  Schema_Validator                            │
│  ┌────────────────────────────────────────────────────┐     │
│  │ validate($data, $schema)                           │     │
│  │   ↓                                                 │     │
│  │   Uses justinrainbow/json-schema if available      │     │
│  │   OR                                                │     │
│  │   Falls back to basic validation                   │     │
│  │   ↓                                                 │     │
│  │ Returns: bool                                       │     │
│  │ Stores: errors array                               │     │
│  └────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

## Request Flow Example

### Step 1: MCP Client Requests Tool List

```
Client → Transport: { "method": "tools/list" }
Transport → Tool_Manager: get_tools_list()
Tool_Manager → Each Tool: get_config()
Each Tool → Tool_Manager: { name, description, inputSchema }
Tool_Manager → Transport: [ {tool1}, {tool2}, ... ]
Transport → Client: MCP formatted tool list
```

### Step 2: MCP Client Calls Tool

```
Client → Transport: {
    "method": "tools/call",
    "params": {
        "name": "get_elementor_info",
        "arguments": { "include_widgets": true }
    }
}

Transport → Tool_Manager: execute_tool('get_elementor_info', $args)
Tool_Manager → Base_Tool: run($args)
    Base_Tool → Schema_Validator: validate($args, $schema)
    Schema_Validator → Base_Tool: true/false
    Base_Tool → Hook: do_action('elementor_mcp/tool/before_execute')
    Base_Tool → Concrete Tool: execute($args)
        Concrete Tool → Elementor API: Get data
        Elementor API → Concrete Tool: Data
    Concrete Tool → Base_Tool: format_success($data)
    Base_Tool → Hook: do_action('elementor_mcp/tool/after_execute')
Base_Tool → Tool_Manager: { success: true, data: {...} }
Tool_Manager → Transport: Result
Transport → Client: MCP formatted response
```

## Data Flow Diagram

```
┌─────────────┐
│ MCP Client  │
└──────┬──────┘
       │
       │ 1. tools/list request
       ▼
┌─────────────┐     2. Get all tools     ┌──────────────┐
│  Transport  │ ───────────────────────> │ Tool_Manager │
└──────┬──────┘                           └──────┬───────┘
       │                                         │
       │ 3. Return tool configs                  │ Auto-discover
       ◄─────────────────────────────────────────┤
       │                                         │
       │ 4. tools/call request                   │
       ├────────────────────────────────────────>│
       │                                         │
       │                           5. Get tool   │
       │                                ┌────────▼────────┐
       │                                │   Base_Tool     │
       │                                │   .run($args)   │
       │                                └────────┬────────┘
       │                                         │
       │                           6. Validate   │
       │                                ┌────────▼────────┐
       │                                │ Schema_Validator│
       │                                └────────┬────────┘
       │                                         │
       │                           7. Execute    │
       │                                ┌────────▼────────┐
       │                                │ Concrete Tool   │
       │                                │ .execute($args) │
       │                                └────────┬────────┘
       │                                         │
       │                           8. Call API   │
       │                                ┌────────▼────────┐
       │                                │ Elementor API   │
       │                                └────────┬────────┘
       │                                         │
       │ 9. Return result                        │
       ◄─────────────────────────────────────────┘
       │
       │ 10. Send to client
       ▼
┌─────────────┐
│ MCP Client  │
└─────────────┘
```

## Hook System Architecture

```
WordPress Hook System
│
├── Manager Hooks
│   ├── elementor_mcp/tools/manager_init
│   │   └── Fired when Tool_Manager is initialized
│   │
│   ├── elementor_mcp/tools/auto_discover_complete
│   │   └── Fired after auto-discovery completes
│   │
│   ├── elementor_mcp/tools/tool_registered
│   │   └── Fired when a tool is registered
│   │
│   └── elementor_mcp/tools/tool_unregistered
│       └── Fired when a tool is unregistered
│
├── Global Tool Hooks
│   ├── elementor_mcp/tool/before_execute
│   │   └── Fired before ANY tool executes
│   │
│   └── elementor_mcp/tool/after_execute
│       └── Fired after ANY tool executes
│
├── Specific Tool Hooks
│   ├── elementor_mcp/tool/{tool_name}/before_execute
│   │   └── Fired before specific tool executes
│   │
│   └── elementor_mcp/tool/{tool_name}/after_execute
│       └── Fired after specific tool executes
│
└── Filters
    └── elementor_mcp/tools/list
        └── Filter tools list before sending to client
```

## Error Handling Flow

```
┌──────────────┐
│ Tool Request │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ Input Validation     │
│ (Schema_Validator)   │
└──────┬───────────────┘
       │
       ├─► Invalid ──> format_error('invalid_input')
       │
       ▼ Valid
┌──────────────────────┐
│ Elementor Check      │
│ (verify_elementor)   │
└──────┬───────────────┘
       │
       ├─► Not Active ──> format_error('elementor_not_active')
       │
       ▼ Active
┌──────────────────────┐
│ Permission Check     │
│ (check_permissions)  │
└──────┬───────────────┘
       │
       ├─► No Permission ──> format_error('permission_denied')
       │
       ▼ Permitted
┌──────────────────────┐
│ Execute Tool Logic   │
│ (execute method)     │
└──────┬───────────────┘
       │
       ├─► Error ──> format_error('execution_error')
       │
       ▼ Success
┌──────────────────────┐
│ Return Success       │
│ format_success()     │
└──────────────────────┘
```

## Validation Architecture

```
Schema Definition (JSON Schema)
│
├── Type Validation
│   ├── string
│   ├── integer
│   ├── number
│   ├── boolean
│   ├── array
│   └── object
│
├── Constraints
│   ├── String: minLength, maxLength, pattern
│   ├── Number: minimum, maximum
│   ├── Array: minItems, maxItems
│   └── Enum: Fixed list of values
│
├── Structure
│   ├── required: Required properties
│   ├── properties: Property definitions
│   └── additionalProperties: Allow/deny extras
│
└── Validation Engine
    │
    ├── justinrainbow/json-schema (if available)
    │   └── Full JSON schema v4 support
    │
    └── Fallback Validator (always available)
        └── Basic validation for common cases
```

## Auto-Discovery Process

```
Tool_Manager::auto_discover_tools()
│
├── Scan Directory: tools/elementor/
│   └── Find: class-*-tool.php
│       ├── Require file
│       ├── Extract class name from filename
│       │   class-get-info-tool.php → Get_Info_Tool
│       ├── Check class exists
│       ├── Check extends Base_Tool
│       └── Register tool instance
│
├── Scan Directory: tools/template/
│   └── (Same process)
│
├── Scan Directory: tools/widget/
│   └── (Same process)
│
└── Fire Hook: elementor_mcp/tools/auto_discover_complete
```

## Tool Lifecycle

```
1. REGISTRATION
   ├── Auto-discovery OR manual registration
   ├── Validate tool class
   ├── Store in registry: tools['name'] = 'ClassName'
   └── Fire: elementor_mcp/tools/tool_registered

2. RETRIEVAL (Lazy Loading)
   ├── Check instance cache
   │   ├── Found → return cached instance
   │   └── Not found → continue
   ├── Instantiate tool class
   ├── Cache instance
   └── Return instance

3. EXECUTION
   ├── Get tool instance
   ├── Call run($args)
   │   ├── Validate input
   │   ├── Fire: before_execute
   │   ├── Execute tool logic
   │   └── Fire: after_execute
   └── Return result

4. UNREGISTRATION (Optional)
   ├── Remove from registry
   ├── Remove from cache
   └── Fire: elementor_mcp/tools/tool_unregistered
```

## Performance Optimizations

### 1. Instance Caching
```
First call:  get_tool('name') → Instantiate → Cache → Return (5ms)
Second call: get_tool('name') → Return cached (0.1ms)
```

### 2. Lazy Loading
```
Tool registered:   Store class name only (no instantiation)
Tool requested:    Instantiate on-demand
Tool not used:     Never instantiated (saves memory)
```

### 3. Auto-Discovery Once
```
On plugin init:    Scan directories, register all tools (50ms)
On each request:   Use cached registry (0ms)
```

### 4. Validation Caching
```
Same schema:       Results can be cached per tool
Different args:    Re-validate (but fast: <2ms)
```

## Security Layers

```
┌────────────────────────────────────────┐
│         MCP Request                    │
└───────────────┬────────────────────────┘
                │
                ▼
┌────────────────────────────────────────┐
│    Layer 1: Input Validation           │
│    • Schema validation                 │
│    • Type checking                     │
│    • Constraint validation             │
└───────────────┬────────────────────────┘
                │
                ▼
┌────────────────────────────────────────┐
│    Layer 2: Elementor Verification     │
│    • Check plugin loaded               │
│    • Verify API available              │
└───────────────┬────────────────────────┘
                │
                ▼
┌────────────────────────────────────────┐
│    Layer 3: Permission Check           │
│    • WordPress capability system       │
│    • User role verification            │
└───────────────┬────────────────────────┘
                │
                ▼
┌────────────────────────────────────────┐
│    Layer 4: Sanitization               │
│    • Sanitize inputs                   │
│    • Escape outputs                    │
└───────────────┬────────────────────────┘
                │
                ▼
┌────────────────────────────────────────┐
│    Layer 5: Error Handling             │
│    • No sensitive data in errors       │
│    • Safe error messages               │
└────────────────────────────────────────┘
```

## Extension Points

### 1. Custom Tool Directories
```php
add_action('elementor_mcp/tools/auto_discover_complete', function($manager) {
    $custom_tools = MY_PLUGIN_PATH . '/custom-tools';
    // Scan and register
});
```

### 2. Tool Modification
```php
add_filter('elementor_mcp/tools/list', function($tools) {
    // Add, remove, or modify tools
    return $tools;
});
```

### 3. Execution Hooks
```php
add_action('elementor_mcp/tool/before_execute', function($tool, $args) {
    // Logging, analytics, etc.
});
```

### 4. Custom Base Classes
```php
abstract class Custom_Tool_Base extends Base_Tool {
    // Add project-specific helpers
}

class My_Tool extends Custom_Tool_Base {
    // Inherits custom helpers
}
```

## Comparison: Elementor vs Our Architecture

| Component | Elementor | Our Implementation |
|-----------|-----------|-------------------|
| **Base Class** | Widget_Base | Base_Tool |
| **Manager** | Widgets_Manager | Tool_Manager |
| **Registry** | _widget_types array | tools array |
| **Caching** | Widget instances | Tool instances |
| **Auto-Discovery** | No (manual hooks) | Yes (directory scan) |
| **Validation** | Controls system | JSON Schema |
| **Hooks** | elementor/* | elementor_mcp/* |
| **Execution** | render() | execute() |
| **Config** | get_controls() | get_input_schema() |
| **Response** | HTML output | JSON response |

## Future Enhancements

### 1. Tool Categories
```php
public function get_category() {
    return 'widget'; // or 'template', 'document', etc.
}
```

### 2. Tool Dependencies
```php
public function get_dependencies() {
    return array('get_elementor_info');
}
```

### 3. Async Execution
```php
public function execute_async($args) {
    // Background processing
}
```

### 4. Result Caching
```php
public function get_cache_ttl() {
    return 300; // 5 minutes
}
```

### 5. Rate Limiting
```php
public function get_rate_limit() {
    return array('requests' => 10, 'period' => 60);
}
```

## Summary

The tool system provides:

- **Consistent Architecture**: All tools follow the same pattern
- **Easy Extensibility**: WordPress hooks throughout
- **Performance**: Lazy loading and caching
- **Security**: Multiple validation layers
- **Developer Experience**: Clear patterns and documentation
- **Standards Compliance**: JSON Schema, MCP protocol
- **Future-Proof**: Easy to extend and modify

This architecture ensures that creating new tools is straightforward while maintaining quality, security, and performance standards throughout the system.
