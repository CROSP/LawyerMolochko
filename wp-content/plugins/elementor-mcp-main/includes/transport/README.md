# Transport Layer Documentation

## Overview

The transport layer handles all communication between the MCP server and clients (Claude Desktop, HTTP clients, etc.). It provides a clean abstraction for reading/writing JSON-RPC messages across different communication channels.

## Architecture

### Transport Interface

All transport implementations must implement the `Transport_Interface`:

```php
interface Transport_Interface {
    public function read_message();           // Read incoming message
    public function send_message($message);   // Send outgoing message
    public function log($message, $level);    // Log debugging info
    public function is_connected();           // Check connection status
    public function initialize();             // Setup transport
    public function shutdown();               // Clean up resources
    public function get_type();               // Get transport type
    public function set_config($key, $value); // Set configuration
    public function get_config($key, $default); // Get configuration
}
```

## Available Transports

### 1. STDIO Transport (Primary)

**Purpose:** Communication via standard input/output streams
**Use Case:** Claude Desktop integration, CLI tools
**File:** `class-stdio-transport.php`

#### Critical Design Principles

1. **Stream Separation:**
   - STDIN: Read JSON-RPC messages
   - STDOUT: Write JSON-RPC responses (ONLY)
   - STDERR: Write logs and debugging info (NEVER mix with STDOUT)

2. **Message Format:**
   - Line-delimited JSON (one message per line)
   - Each message ends with `\n`
   - UTF-8 encoding

3. **Connection Handling:**
   - EOF on STDIN signals graceful shutdown
   - Non-blocking reads supported via `read_timeout` config
   - Automatic connection state detection

#### Configuration Options

```php
$transport = new STDIO_Transport([
    'read_timeout' => 0,          // Seconds (0 = blocking)
    'write_buffer_size' => 8192,  // Write buffer size
    'log_prefix' => '[MCP]',      // Log prefix
    'log_timestamp' => true,      // Include timestamp
    'log_level' => 'info',        // Minimum level: debug|info|warning|error
]);
```

#### Usage Example

```php
// Initialize
$transport = new STDIO_Transport();
$transport->initialize();

// Read a message
$request = $transport->read_message();
// Returns: ['jsonrpc' => '2.0', 'method' => 'tools/list', 'id' => 1]

// Send a response
$transport->send_message([
    'jsonrpc' => '2.0',
    'result' => ['tools' => [...]],
    'id' => 1
]);

// Log (goes to STDERR)
$transport->log('Processing request', 'info');

// Check connection
if (!$transport->is_connected()) {
    $transport->shutdown();
    exit(0);
}
```

#### Testing STDIO Transport

**Test 1: Echo Server**

```bash
# Run from project root
php tests/transport/test-stdio-echo.php
```

Then send JSON on STDIN:
```json
{"jsonrpc":"2.0","method":"test","id":1}
```

Expected output (STDOUT):
```json
{"jsonrpc":"2.0","result":{"received":"test"},"id":1}
```

Expected logs (STDERR):
```
2025-11-22 14:30:00 [MCP] [INFO] STDIO transport initialized
2025-11-22 14:30:01 [MCP] [DEBUG] Received message: {"jsonrpc":"2.0","method":"test","id":1}...
2025-11-22 14:30:01 [MCP] [DEBUG] Sent message: {"jsonrpc":"2.0","result":{"received":"test"},"id":1}...
```

**Test 2: Claude Desktop Integration**

Add to Claude Desktop config (`claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "elementor": {
      "command": "php",
      "args": [
        "/absolute/path/to/elementor-mcp/mcp-server.php"
      ],
      "env": {
        "WP_ROOT": "/absolute/path/to/wordpress"
      }
    }
  }
}
```

### 2. HTTP Transport (Optional)

**Purpose:** REST API access to MCP server
**Use Case:** Web applications, browser extensions, external tools
**File:** `class-http-transport.php`

#### Features

- RESTful JSON-RPC endpoint
- CORS support for browser clients
- API key authentication
- Rate limiting
- Stateless operation

#### Configuration Options

```php
$transport = new HTTP_Transport([
    'endpoint' => '/wp-json/elementor-mcp/v1/rpc',
    'cors_enabled' => true,
    'cors_origins' => ['*'], // Or specific domains
    'auth_required' => true,
    'auth_header' => 'X-MCP-API-Key',
    'rate_limit' => 100,        // Requests per minute
    'rate_limit_window' => 60,  // Seconds
]);
```

#### WordPress Integration

```php
// Initialize in WordPress plugin
add_action('plugins_loaded', function() {
    $transport = new HTTP_Transport();
    $transport->initialize(); // Registers REST routes
});
```

#### API Endpoint

**POST** `/wp-json/elementor-mcp/v1/rpc`

**Headers:**
- `Content-Type: application/json`
- `X-MCP-API-Key: your-api-key-here` (if auth enabled)

**Request Body:**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/list",
  "id": 1
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "result": {
    "tools": [...]
  },
  "id": 1
}
```

#### Testing HTTP Transport

```bash
# Test with curl
curl -X POST http://localhost/wp-json/elementor-mcp/v1/rpc \
  -H "Content-Type: application/json" \
  -H "X-MCP-API-Key: your-key" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'
```

#### Managing API Keys

```php
// Add API key (in WordPress admin or via code)
$keys = get_option('elementor_mcp_api_keys', []);
$keys[] = 'your-secret-key-here';
update_option('elementor_mcp_api_keys', $keys);
```

## Data Flow

### STDIO Transport Flow

```
Claude Desktop
    |
    | (1) Send JSON-RPC request via STDIN
    v
STDIO Transport
    |
    | (2) read_message() -> Parse JSON
    v
MCP Server
    |
    | (3) Process request
    v
STDIO Transport
    |
    | (4) send_message() -> Write JSON to STDOUT
    v
Claude Desktop
```

**Logs:** STDERR (parallel channel, never mixed with STDOUT)

### HTTP Transport Flow

```
Web Client
    |
    | (1) POST /wp-json/elementor-mcp/v1/rpc
    v
WordPress REST API
    |
    | (2) Authentication & Rate Limiting
    v
HTTP Transport
    |
    | (3) read_message() -> Parse request body
    v
MCP Server
    |
    | (4) Process request
    v
HTTP Transport
    |
    | (5) send_message() -> HTTP response
    v
Web Client
```

**Logs:** WordPress error log

## Error Handling

### STDIO Errors

1. **EOF on STDIN:** Clean shutdown
2. **Invalid JSON:** Log error, attempt to send error response
3. **Write failure:** Log error, attempt shutdown
4. **Stream not available:** Throw exception during initialize()

### HTTP Errors

1. **Invalid JSON:** Return 400 Bad Request with JSON-RPC error
2. **Authentication failure:** Return 401/403 with error
3. **Rate limit exceeded:** Return 429 with error
4. **Server error:** Return 500 with JSON-RPC error

## Performance Considerations

### STDIO Transport

- **Blocking vs Non-blocking:** Use blocking reads for simplicity, non-blocking for timeouts
- **Buffer size:** Default 8192 bytes, tune based on message size
- **Flushing:** Always flush after writing to ensure immediate delivery

### HTTP Transport

- **Rate limiting:** Protects server from abuse
- **Caching:** Consider response caching for idempotent operations
- **Keep-alive:** Not applicable (stateless)

## Security Considerations

### STDIO Transport

- **Input validation:** Always validate JSON structure
- **No authentication:** Assumes secure environment (local process)
- **Process isolation:** Runs in separate PHP process

### HTTP Transport

- **Authentication required:** Use API keys or JWT tokens
- **CORS configuration:** Restrict origins in production
- **Rate limiting:** Prevent abuse
- **Input validation:** Strict JSON-RPC validation
- **HTTPS recommended:** Encrypt transport layer

## Debugging

### Enable Verbose Logging (STDIO)

```php
$transport->set_config('log_level', 'debug');
```

### Monitor STDERR

```bash
php mcp-server.php 2> error.log
```

### Test Message Flow

```bash
# Send test message
echo '{"jsonrpc":"2.0","method":"initialize","id":1}' | php mcp-server.php

# With error logging
echo '{"jsonrpc":"2.0","method":"initialize","id":1}' | php mcp-server.php 2> >(tee error.log >&2)
```

### HTTP Debug Headers

```php
$transport->set_config('log_requests', true);
$transport->set_config('log_responses', true);
```

## Common Issues

### STDIO: "Stream not available"

**Cause:** Running in non-CLI environment
**Solution:** STDIO transport only works in CLI mode (php-cli)

### STDIO: "Mixed output on STDOUT"

**Cause:** Code writing to STDOUT outside transport
**Solution:** Never use `echo`, `print`, `var_dump` - use `log()` instead

### HTTP: "CORS error"

**Cause:** Origin not allowed
**Solution:** Add origin to `cors_origins` config or use `['*']` for testing

### HTTP: "401 Unauthorized"

**Cause:** Missing or invalid API key
**Solution:** Check header name and key value

## Testing Checklist

### STDIO Transport

- [ ] Can read valid JSON messages
- [ ] Can send valid JSON responses
- [ ] Handles EOF gracefully
- [ ] Logs go to STDERR only
- [ ] Non-blocking reads work (if enabled)
- [ ] Multiple messages work sequentially
- [ ] Invalid JSON triggers error
- [ ] Connection state detected correctly

### HTTP Transport

- [ ] REST endpoint registered
- [ ] Can receive POST requests
- [ ] Can send JSON responses
- [ ] CORS headers sent correctly
- [ ] Authentication works
- [ ] Rate limiting works
- [ ] Invalid JSON returns 400
- [ ] Auth failure returns 401/403

## Future Enhancements

### Planned

- WebSocket transport for real-time bidirectional communication
- Unix socket transport for local IPC
- Message queuing for async processing
- Transport auto-detection based on environment
- Compression support for large messages
- Binary message format (MessagePack)

### Under Consideration

- gRPC transport
- Named pipes (Windows)
- ZeroMQ transport
- Redis pub/sub transport

## References

- [MCP Protocol Specification](https://modelcontextprotocol.io/docs)
- [JSON-RPC 2.0 Specification](https://www.jsonrpc.org/specification)
- [PHP Stream Functions](https://www.php.net/manual/en/ref.stream.php)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
