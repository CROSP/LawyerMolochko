# Transport Layer Testing & Implementation Notes

## Quick Start

### Running Tests

**Linux/Mac:**
```bash
cd tests/transport
chmod +x run-tests.sh
./run-tests.sh
```

**Windows:**
```cmd
cd tests\transport
run-tests.bat
```

### Manual Testing

**Echo Server:**
```bash
php tests/transport/test-stdio-echo.php
```

Type JSON messages and see them echoed back:
```json
{"jsonrpc":"2.0","method":"test","id":1}
```

**Interactive MCP Server:**
```bash
php tests/transport/test-stdio-interactive.php
```

Try the sample messages from `test-messages.json`.

## Critical Implementation Details

### STDIO Transport

#### Stream Separation

The most critical aspect of STDIO transport is proper stream separation:

```
STDIN  (input)   ← JSON-RPC requests from client
STDOUT (output)  ← JSON-RPC responses to client (ONLY!)
STDERR (logging) ← Debug logs, errors, diagnostics (NEVER mixed with STDOUT)
```

**Why this matters:**
- Claude Desktop and MCP clients parse STDOUT as JSON
- Any non-JSON output to STDOUT breaks the protocol
- Logs, debug info, errors must go to STDERR

#### Common Mistakes to Avoid

1. **DON'T use echo/print/var_dump in your code**
   ```php
   // WRONG - this goes to STDOUT
   echo "Processing request...";
   var_dump($data);

   // RIGHT - use transport's log method
   $transport->log("Processing request", 'info');
   $transport->log(print_r($data, true), 'debug');
   ```

2. **DON'T write to STDOUT directly**
   ```php
   // WRONG
   fwrite(STDOUT, "some text\n");

   // RIGHT
   $transport->send_message(['jsonrpc' => '2.0', 'result' => ...]);
   ```

3. **DON'T mix WordPress output with transport**
   ```php
   // WRONG - WordPress might echo things
   $post = get_post(123);
   // If WordPress echoes anything, it breaks STDIO

   // RIGHT - capture and suppress output
   ob_start();
   $post = get_post(123);
   ob_end_clean();
   ```

#### Message Format

Every message must be:
- Valid JSON
- On a single line
- Terminated with `\n`

**Valid message:**
```json
{"jsonrpc":"2.0","method":"tools/list","id":1}
```

**Invalid (multi-line):**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/list",
  "id": 1
}
```

#### EOF Handling

When STDIN reaches EOF (Ctrl+D on Unix, Ctrl+Z on Windows):
1. `read_message()` returns `null`
2. Set connection state to `false`
3. Call `shutdown()`
4. Exit gracefully

```php
while ($transport->is_connected()) {
    $message = $transport->read_message();

    if ($message === null) {
        $transport->log('Connection closed', 'info');
        break;
    }

    // Process message...
}

$transport->shutdown();
exit(0);
```

### HTTP Transport

#### WordPress Integration

The HTTP transport integrates with WordPress REST API:

```php
// In your plugin initialization
add_action('plugins_loaded', function() {
    $transport = new HTTP_Transport([
        'cors_enabled' => true,
        'auth_required' => true,
    ]);
    $transport->initialize();
});
```

#### API Key Management

**Add an API key:**
```php
$keys = get_option('elementor_mcp_api_keys', []);
$new_key = bin2hex(random_bytes(32)); // Generate secure key
$keys[] = $new_key;
update_option('elementor_mcp_api_keys', $keys);
echo "API Key: " . $new_key;
```

**Test with curl:**
```bash
curl -X POST http://localhost/wp-json/elementor-mcp/v1/rpc \
  -H "Content-Type: application/json" \
  -H "X-MCP-API-Key: your-api-key-here" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'
```

#### CORS Configuration

**Allow all origins (development only):**
```php
$transport->set_config('cors_origins', ['*']);
```

**Restrict to specific domains (production):**
```php
$transport->set_config('cors_origins', [
    'https://your-app.com',
    'https://admin.your-app.com'
]);
```

#### Rate Limiting

Default: 100 requests per minute per client (identified by API key or IP)

**Adjust limits:**
```php
$transport->set_config('rate_limit', 200);       // 200 requests
$transport->set_config('rate_limit_window', 60); // per 60 seconds
```

## Testing Strategies

### Unit Tests

Test individual transport methods in isolation:

```php
use PHPUnit\Framework\TestCase;
use ElementorMCP\Transport\STDIO_Transport;

class STDIO_Transport_Test extends TestCase {
    public function test_initialization() {
        $transport = new STDIO_Transport();
        $transport->initialize();

        $this->assertEquals('stdio', $transport->get_type());
        $this->assertTrue($transport->is_connected());
    }

    public function test_configuration() {
        $transport = new STDIO_Transport([
            'log_level' => 'debug',
            'read_timeout' => 5,
        ]);

        $this->assertEquals('debug', $transport->get_config('log_level'));
        $this->assertEquals(5, $transport->get_config('read_timeout'));
    }
}
```

### Integration Tests

Test complete message flow:

```bash
# Test script that sends messages and validates responses
php tests/transport/integration-test.php
```

### Manual Testing with Claude Desktop

1. **Configure Claude Desktop**

Edit config file:
- Mac: `~/Library/Application Support/Claude/claude_desktop_config.json`
- Windows: `%APPDATA%\Claude\claude_desktop_config.json`

```json
{
  "mcpServers": {
    "elementor-test": {
      "command": "php",
      "args": [
        "E:/Projects/WPCursor/elementor-mcp/tests/transport/test-stdio-interactive.php"
      ]
    }
  }
}
```

2. **Restart Claude Desktop**

3. **Test in Claude**
   - Check server appears in MCP servers list
   - Try calling tools
   - Check logs in STDERR output

### Debugging

#### Enable Verbose Logging

```php
$transport->set_config('log_level', 'debug');
```

#### Redirect STDERR to File

```bash
php mcp-server.php 2> debug.log
```

#### Monitor Both STDOUT and STDERR

```bash
php mcp-server.php 2> >(tee error.log >&2) | tee output.log
```

This gives you:
- `output.log` - STDOUT (JSON responses)
- `error.log` - STDERR (logs)
- Console - both streams

#### Validate JSON Output

```bash
php mcp-server.php < input.txt | jq .
```

If `jq` fails, STDOUT contains invalid JSON (bug!).

## Performance Considerations

### STDIO Transport

**Buffer Sizes:**
- Default write buffer: 8192 bytes
- For large messages (>8KB), increase buffer:
  ```php
  $transport->set_config('write_buffer_size', 65536); // 64KB
  ```

**Blocking vs Non-blocking:**
- Blocking (default): Simple, waits indefinitely for input
- Non-blocking: Requires timeout, more complex but allows health checks

**Flushing:**
- STDIO transport flushes after every write (required for line-delimited protocol)
- No need to manually flush

### HTTP Transport

**Rate Limiting:**
- Default: 100 req/min is conservative
- Adjust based on your server capacity
- Consider implementing per-user limits

**Response Caching:**
- For idempotent operations (GET-like), consider caching
- Use WordPress transients:
  ```php
  $cache_key = 'mcp_tools_list';
  $result = get_transient($cache_key);
  if ($result === false) {
      $result = /* compute result */;
      set_transient($cache_key, $result, 300); // 5 min
  }
  ```

## Security Considerations

### STDIO Transport

**Assumptions:**
- Runs in trusted local environment
- No authentication needed (process isolation)
- Input validation still required (malicious JSON)

**Risks:**
- If STDIO server accessible remotely (SSH tunneling), ensure SSH is secured
- Validate all input data before processing

### HTTP Transport

**Requirements:**
- HTTPS in production (prevent MITM)
- Strong API keys (32+ bytes, random)
- Rate limiting (prevent DoS)
- CORS restrictions (prevent unauthorized origins)
- Input validation and sanitization

**API Key Storage:**
```php
// WRONG - don't store in code
$api_key = 'hardcoded-key';

// RIGHT - store in database or environment
$api_key = get_option('elementor_mcp_api_key');
// or
$api_key = getenv('ELEMENTOR_MCP_API_KEY');
```

**API Key Rotation:**
```php
function rotate_api_key($old_key) {
    $keys = get_option('elementor_mcp_api_keys', []);

    // Remove old key
    $keys = array_filter($keys, function($k) use ($old_key) {
        return $k !== $old_key;
    });

    // Add new key
    $new_key = bin2hex(random_bytes(32));
    $keys[] = $new_key;

    update_option('elementor_mcp_api_keys', $keys);

    return $new_key;
}
```

## Common Issues and Solutions

### Issue: "Invalid JSON" errors

**Symptoms:**
```
Failed to decode message: Syntax error
```

**Causes:**
- Multi-line JSON (must be single line)
- PHP warnings/notices leaking to STDOUT
- WordPress debug output

**Solutions:**
```php
// 1. Disable WordPress debug output in production
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

// 2. Suppress output buffering
ob_start();
// Your code
ob_end_clean();

// 3. Validate JSON before sending
$json = json_encode($message);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('JSON encoding failed');
}
```

### Issue: Messages not received by Claude Desktop

**Symptoms:**
- Server appears connected but doesn't respond
- Tools don't appear in Claude

**Causes:**
- Logs mixed with STDOUT
- Incorrect message format
- Missing newline delimiter

**Debug:**
```bash
# Test manually
echo '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' | php mcp-server.php

# Check output is valid JSON
echo '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' | php mcp-server.php | jq .
```

### Issue: Rate limiting too aggressive

**Symptoms:**
```json
{"jsonrpc":"2.0","error":{"code":-32000,"message":"Rate limit exceeded"},"id":null}
```

**Solution:**
```php
// Increase limits
$transport->set_config('rate_limit', 500);
$transport->set_config('rate_limit_window', 60);

// Or disable for testing
$transport->set_config('rate_limit', PHP_INT_MAX);
```

### Issue: CORS errors in browser

**Symptoms:**
```
Access to fetch blocked by CORS policy
```

**Solution:**
```php
// Development: allow all
$transport->set_config('cors_origins', ['*']);

// Production: specific origins
$transport->set_config('cors_origins', [
    'https://your-frontend.com'
]);
```

## Best Practices

1. **Always flush after writing to STDOUT**
   ```php
   fwrite(STDOUT, $json . "\n");
   fflush(STDOUT);
   ```

2. **Never suppress errors silently**
   ```php
   // WRONG
   @fwrite(STDOUT, $json);

   // RIGHT
   if (fwrite(STDOUT, $json) === false) {
       throw new Exception('Failed to write message');
   }
   ```

3. **Log everything important to STDERR**
   ```php
   $transport->log("Received method: {$method}", 'debug');
   $transport->log("Processing complete", 'info');
   $transport->log("Error: {$error}", 'error');
   ```

4. **Validate input at every layer**
   ```php
   // Transport layer: validate JSON structure
   if (!isset($message['jsonrpc'])) {
       throw new Exception('Invalid JSON-RPC');
   }

   // Application layer: validate business logic
   if (!isset($params['widget_name'])) {
       throw new Exception('Missing required parameter');
   }
   ```

5. **Handle shutdowns gracefully**
   ```php
   register_shutdown_function(function() use ($transport) {
       $transport->shutdown();
   });

   pcntl_signal(SIGTERM, function() use ($transport) {
       $transport->shutdown();
       exit(0);
   });
   ```

## Next Steps

1. **Implement MCP Server Core** - Build on top of transport layer
2. **Add Schema Validation** - Validate JSON-RPC message format
3. **Implement Tool Handlers** - Create actual MCP tools
4. **WordPress Integration** - Bootstrap WordPress and Elementor
5. **Error Handling** - Comprehensive error handling and recovery
6. **Performance Testing** - Load testing and optimization
7. **Security Audit** - Review security implications
8. **Documentation** - Complete user and developer docs
