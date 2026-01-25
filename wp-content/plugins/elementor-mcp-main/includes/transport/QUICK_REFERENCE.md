# Transport Layer Quick Reference

## At a Glance

### STDIO Transport
- **Use for:** Claude Desktop, CLI tools
- **Protocol:** Line-delimited JSON via stdin/stdout
- **Logs:** STDERR only (never STDOUT!)
- **Connection:** Persistent until EOF
- **Setup time:** < 1 second

### HTTP Transport
- **Use for:** Web apps, REST APIs, remote clients
- **Protocol:** HTTP POST with JSON body
- **Logs:** WordPress error log
- **Connection:** Stateless (per-request)
- **Setup time:** WordPress bootstrap (~100ms)

## Code Snippets

### Create STDIO Transport
```php
use ElementorMCP\Transport\STDIO_Transport;

$transport = new STDIO_Transport([
    'log_level' => 'info',  // debug|info|warning|error
]);
$transport->initialize();
```

### Create HTTP Transport
```php
use ElementorMCP\Transport\HTTP_Transport;

$transport = new HTTP_Transport([
    'cors_enabled' => true,
    'auth_required' => true,
]);
$transport->initialize();
```

### Read Message
```php
$message = $transport->read_message();
// Returns: ['jsonrpc' => '2.0', 'method' => 'tools/list', 'id' => 1]
// Returns: null on EOF/disconnect
```

### Send Message
```php
$transport->send_message([
    'jsonrpc' => '2.0',
    'result' => ['tools' => [...]],
    'id' => 1,
]);
```

### Log Message
```php
$transport->log('Processing request', 'info');
$transport->log('Debug data: ' . print_r($data, true), 'debug');
$transport->log('Warning: slow query', 'warning');
$transport->log('Error: failed to connect', 'error');
```

### Check Connection
```php
if (!$transport->is_connected()) {
    $transport->shutdown();
    exit(0);
}
```

### Server Loop
```php
while ($transport->is_connected()) {
    $request = $transport->read_message();

    if ($request === null) break;

    $response = handle_request($request);

    $transport->send_message($response);
}
$transport->shutdown();
```

## Configuration

### STDIO Options
```php
[
    'read_timeout' => 0,          // 0 = blocking, >0 = timeout in seconds
    'write_buffer_size' => 8192,  // Write buffer size in bytes
    'log_prefix' => '[MCP]',      // Prefix for log messages
    'log_timestamp' => true,      // Include timestamp in logs
    'log_level' => 'info',        // Minimum level to log
]
```

### HTTP Options
```php
[
    'endpoint' => '/wp-json/elementor-mcp/v1/rpc',
    'cors_enabled' => true,
    'cors_origins' => ['*'],           // Use specific domains in production
    'auth_required' => true,
    'auth_header' => 'X-MCP-API-Key',
    'rate_limit' => 100,               // Requests per window
    'rate_limit_window' => 60,         // Window in seconds
    'log_requests' => true,
    'log_responses' => true,
]
```

## Testing Commands

### Run All Tests
```bash
# Linux/Mac
cd tests/transport
./run-tests.sh

# Windows
cd tests\transport
run-tests.bat
```

### Manual Echo Test
```bash
php tests/transport/test-stdio-echo.php
# Then type: {"jsonrpc":"2.0","method":"test","id":1}
```

### Interactive MCP Test
```bash
php tests/transport/test-stdio-interactive.php
# Then paste messages from test-messages.json
```

### Test with Piped Input
```bash
echo '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' | \
  php tests/transport/test-stdio-interactive.php
```

### Test HTTP Endpoint
```bash
curl -X POST http://localhost/wp-json/elementor-mcp/v1/rpc \
  -H "Content-Type: application/json" \
  -H "X-MCP-API-Key: your-api-key" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'
```

## Claude Desktop Config

**Location:**
- Mac: `~/Library/Application Support/Claude/claude_desktop_config.json`
- Windows: `%APPDATA%\Claude\claude_desktop_config.json`

**Config:**
```json
{
  "mcpServers": {
    "elementor": {
      "command": "php",
      "args": [
        "E:/Projects/WPCursor/elementor-mcp/mcp-server.php"
      ],
      "env": {
        "WP_ROOT": "E:/path/to/wordpress"
      }
    }
  }
}
```

## Common Errors

### "Invalid JSON"
**Cause:** Multi-line JSON or non-JSON output to STDOUT
**Fix:** Ensure only single-line JSON goes to STDOUT, use log() for debug output

### "Connection closed unexpectedly"
**Cause:** PHP error/warning output to STDOUT
**Fix:** Disable WP_DEBUG_DISPLAY, use error_reporting(0) in production

### "CORS error"
**Cause:** Origin not in cors_origins list
**Fix:** Add origin to config or use `['*']` for development

### "401 Unauthorized"
**Cause:** Missing or invalid API key
**Fix:** Include X-MCP-API-Key header with valid key

### "429 Rate Limit Exceeded"
**Cause:** Too many requests
**Fix:** Increase rate_limit or reduce request frequency

## JSON-RPC Format

### Request
```json
{
  "jsonrpc": "2.0",
  "method": "method_name",
  "params": {},
  "id": 1
}
```

### Response (Success)
```json
{
  "jsonrpc": "2.0",
  "result": {},
  "id": 1
}
```

### Response (Error)
```json
{
  "jsonrpc": "2.0",
  "error": {
    "code": -32600,
    "message": "Invalid Request"
  },
  "id": 1
}
```

### Notification (No Response)
```json
{
  "jsonrpc": "2.0",
  "method": "notification_method",
  "params": {}
}
```
Note: No `id` field = notification, server won't respond

## Error Codes

| Code   | Message            | Meaning                              |
|--------|-------------------|--------------------------------------|
| -32700 | Parse error       | Invalid JSON                         |
| -32600 | Invalid Request   | Not valid JSON-RPC                   |
| -32601 | Method not found  | Method doesn't exist                 |
| -32602 | Invalid params    | Invalid method parameters            |
| -32603 | Internal error    | Server-side error                    |
| -32000 | Server error      | Custom server error (e.g., rate limit)|

## Debug Checklist

STDIO Transport:
- [ ] Logs go to STDERR only
- [ ] Responses go to STDOUT only
- [ ] Each message on single line
- [ ] Messages end with \n
- [ ] Streams flushed after write
- [ ] EOF handled gracefully
- [ ] No echo/print/var_dump in code
- [ ] WP_DEBUG_DISPLAY disabled

HTTP Transport:
- [ ] Endpoint registered
- [ ] CORS headers sent
- [ ] API key validated
- [ ] Rate limiting active
- [ ] HTTPS in production
- [ ] Error responses include JSON-RPC error
- [ ] Logs go to error log

## Performance Tips

### STDIO
- Use blocking reads (default) for simplicity
- Flush after every write (required)
- Keep messages small (<1MB)
- Don't buffer responses

### HTTP
- Cache idempotent tool results
- Use WordPress transients
- Minimize WordPress queries
- Consider object caching
- Monitor rate limits

## Security Checklist

### STDIO
- [ ] Validate all input JSON
- [ ] Check message structure
- [ ] Sanitize parameters
- [ ] Don't expose to network

### HTTP
- [ ] Use HTTPS in production
- [ ] Generate strong API keys (32+ bytes)
- [ ] Rotate keys regularly
- [ ] Restrict CORS origins
- [ ] Enable rate limiting
- [ ] Log failed auth attempts
- [ ] Validate API key on every request
- [ ] Check WordPress capabilities

## File Locations

```
includes/transport/
├── class-transport-interface.php    # Interface
├── class-stdio-transport.php        # STDIO implementation
├── class-http-transport.php         # HTTP implementation
├── README.md                        # Full documentation
├── TESTING_NOTES.md                 # Testing guide
├── IMPLEMENTATION_SUMMARY.md        # Implementation details
├── ARCHITECTURE.md                  # Architecture diagrams
└── QUICK_REFERENCE.md               # This file

tests/transport/
├── test-stdio-echo.php              # Echo server test
├── test-stdio-interactive.php       # Interactive MCP test
├── test-messages.json               # Sample messages
├── run-tests.sh                     # Test runner (Unix)
└── run-tests.bat                    # Test runner (Windows)
```

## Next Steps

1. **Test transports independently** - Run test suite
2. **Build MCP Server Core** - Use transport as foundation
3. **Implement tool handlers** - Create actual MCP tools
4. **Integrate with WordPress** - Bootstrap WP environment
5. **Test with Claude Desktop** - End-to-end validation
6. **Security hardening** - Audit and lock down
7. **Performance optimization** - Profile and optimize
8. **Documentation** - Complete user docs

## Getting Help

- Read: `README.md` for comprehensive documentation
- Read: `TESTING_NOTES.md` for testing strategies
- Read: `ARCHITECTURE.md` for design details
- Debug: Enable debug logging and check STDERR
- Test: Run automated test suite
- Validate: Pipe output through `jq` to check JSON

## Key Takeaways

1. **STDIO is for local AI tools** - Claude Desktop, CLI
2. **HTTP is for web clients** - Browsers, APIs, remote
3. **Never mix logs with messages** - STDERR vs STDOUT
4. **Always flush after write** - Required for line-delimited
5. **Handle EOF gracefully** - Clean shutdown
6. **Validate everything** - Trust no input
7. **Test early and often** - Automated tests catch issues
8. **Security matters** - Even for local tools
