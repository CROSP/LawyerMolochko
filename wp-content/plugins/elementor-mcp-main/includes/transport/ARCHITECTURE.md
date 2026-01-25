# Transport Layer Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      MCP CLIENT LAYER                           │
│  (Claude Desktop, Web App, CLI Tool, Browser Extension)        │
└─────────────────┬───────────────────────────┬───────────────────┘
                  │                           │
                  │ STDIO                     │ HTTP/REST
                  │ (stdin/stdout)            │ (POST requests)
                  │                           │
┌─────────────────▼───────────────────────────▼───────────────────┐
│                   TRANSPORT LAYER                               │
│                                                                 │
│  ┌──────────────────────┐      ┌──────────────────────┐       │
│  │  STDIO Transport     │      │   HTTP Transport     │       │
│  │                      │      │                      │       │
│  │  • Read from STDIN   │      │  • REST API endpoint │       │
│  │  • Write to STDOUT   │      │  • CORS handling     │       │
│  │  • Log to STDERR     │      │  • Authentication    │       │
│  │  • EOF handling      │      │  • Rate limiting     │       │
│  │  • Line-delimited    │      │  • Stateless         │       │
│  │    JSON protocol     │      │                      │       │
│  └──────────┬───────────┘      └──────────┬───────────┘       │
│             │                              │                   │
│             └──────────────┬───────────────┘                   │
│                            │                                   │
│                  ┌─────────▼──────────┐                        │
│                  │  Transport         │                        │
│                  │  Interface         │                        │
│                  │                    │                        │
│                  │  • read_message()  │                        │
│                  │  • send_message()  │                        │
│                  │  • log()           │                        │
│                  │  • is_connected()  │                        │
│                  └─────────┬──────────┘                        │
└────────────────────────────┼────────────────────────────────────┘
                             │
                             │ JSON-RPC 2.0 Messages
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                     MCP SERVER CORE                             │
│                                                                 │
│  • Request routing                                              │
│  • Tool registry                                                │
│  • Error handling                                               │
│  • Response formatting                                          │
└─────────────────────────────────────────────────────────────────┘
```

## STDIO Transport Data Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                        CLAUDE DESKTOP                            │
└───────┬──────────────────────────────────────────────┬───────────┘
        │                                              │
        │ (1) Send JSON-RPC Request                   │ (6) Receive Response
        │                                              │
        ▼                                              │
  ┌──────────┐                                         │
  │  STDIN   │─────────┐                              │
  └──────────┘         │                              │
                       │                              │
                ┌──────▼──────────────────────┐       │
                │  STDIO Transport            │       │
                │                             │       │
                │  read_message()             │       │
                │  • fgets(STDIN)             │       │
                │  • json_decode()            │       │
                │  • validate                 │       │
                └──────┬──────────────────────┘       │
                       │                              │
        (2) Parsed     │                              │
        Message        │                              │
                       ▼                              │
                ┌─────────────────┐                   │
                │  MCP Server     │                   │
                │  • Route        │                   │
                │  • Process      │                   │
                │  • Execute      │                   │
                └──────┬──────────┘                   │
                       │                              │
        (3) Generate   │                              │
        Response       │                              │
                       ▼                              │
                ┌──────────────────────────┐          │
                │  STDIO Transport         │          │
                │                          │          │
                │  send_message()          │          │
                │  • json_encode()         │          │
                │  • fwrite(STDOUT)        │          │
                │  • fflush()              │──────────┘
                └───────┬──────────────────┘
                        │
  ┌─────────────────────┘
  │
  ▼
┌──────────┐
│  STDOUT  │
└──────────┘


PARALLEL LOGGING CHANNEL:

┌──────────────────────────────────────────────────────────────────┐
│  STDERR (Logs - Never Mixed with STDOUT!)                       │
│                                                                  │
│  [2025-11-22 13:45:00] [MCP] [INFO] STDIO transport initialized │
│  [2025-11-22 13:45:01] [MCP] [DEBUG] Received message: {...}    │
│  [2025-11-22 13:45:01] [MCP] [DEBUG] Sent message: {...}        │
└──────────────────────────────────────────────────────────────────┘
```

## HTTP Transport Data Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                        WEB CLIENT                                │
│              (Browser, Postman, curl, fetch())                   │
└───────┬──────────────────────────────────────────────────────────┘
        │
        │ (1) POST /wp-json/elementor-mcp/v1/rpc
        │     Headers: X-MCP-API-Key: xxx
        │     Body: {"jsonrpc":"2.0",...}
        │
        ▼
┌────────────────────────────────────────────────────────────┐
│                    WORDPRESS REST API                      │
│                                                            │
│  • Route matching                                          │
│  • Authentication (API key validation)                     │
│  • Rate limiting check                                     │
│  • CORS headers                                            │
└───────┬────────────────────────────────────────────────────┘
        │
        │ (2) Validated Request
        │
        ▼
┌────────────────────────────────────────────────────────────┐
│                   HTTP Transport                           │
│                                                            │
│  handle_rest_request()                                     │
│  • Parse request body                                      │
│  • Validate JSON                                           │
│  • Store in current_request                                │
└───────┬────────────────────────────────────────────────────┘
        │
        │ (3) read_message()
        │     Returns current_request
        │
        ▼
┌────────────────────────────────────────────────────────────┐
│                    MCP Server                              │
│                                                            │
│  • Route to tool handler                                   │
│  • Execute tool                                            │
│  • Generate response                                       │
└───────┬────────────────────────────────────────────────────┘
        │
        │ (4) Response array
        │
        ▼
┌────────────────────────────────────────────────────────────┐
│                   HTTP Transport                           │
│                                                            │
│  send_message()                                            │
│  • json_encode()                                           │
│  • Set Content-Type header                                 │
│  • echo output                                             │
└───────┬────────────────────────────────────────────────────┘
        │
        │ (5) HTTP Response
        │     200 OK
        │     {"jsonrpc":"2.0","result":{...}}
        │
        ▼
┌────────────────────────────────────────────────────────────┐
│                    WEB CLIENT                              │
└────────────────────────────────────────────────────────────┘


LOGGING:

┌────────────────────────────────────────────────────────────┐
│  WordPress Error Log (wp-content/debug.log)               │
│                                                            │
│  [22-Nov-2025 13:45:00] [HTTP Transport] [INFO] ...       │
│  [22-Nov-2025 13:45:01] [HTTP Transport] [DEBUG] ...      │
└────────────────────────────────────────────────────────────┘
```

## Message Format Comparison

### STDIO Transport

**Input (STDIN):**
```
{"jsonrpc":"2.0","method":"tools/list","id":1}\n
{"jsonrpc":"2.0","method":"tools/call","params":{"name":"echo"},"id":2}\n
```

**Output (STDOUT):**
```
{"jsonrpc":"2.0","result":{"tools":[...]},"id":1}\n
{"jsonrpc":"2.0","result":{"content":[...]},"id":2}\n
```

**Logs (STDERR):**
```
[2025-11-22 13:45:00] [MCP] [INFO] STDIO transport initialized
[2025-11-22 13:45:01] [MCP] [DEBUG] Received message: {"jsonrpc":"2.0","method":"tools/list","id":1}
```

**Key Points:**
- One message per line
- Newline delimiter
- No multi-line JSON
- Immediate flushing

### HTTP Transport

**Request:**
```http
POST /wp-json/elementor-mcp/v1/rpc HTTP/1.1
Host: localhost
Content-Type: application/json
X-MCP-API-Key: abc123...

{"jsonrpc":"2.0","method":"tools/list","id":1}
```

**Response:**
```http
HTTP/1.1 200 OK
Content-Type: application/json
Access-Control-Allow-Origin: *

{"jsonrpc":"2.0","result":{"tools":[...]},"id":1}
```

**Key Points:**
- Standard HTTP request/response
- Headers for authentication
- CORS headers if enabled
- Pretty-printed JSON allowed

## Transport Selection Matrix

| Criterion              | STDIO Transport | HTTP Transport |
|------------------------|----------------|----------------|
| **Primary Use Case**   | Claude Desktop | Web apps, APIs |
| **Connection Type**    | Persistent     | Stateless      |
| **Authentication**     | Process isolation | API keys    |
| **Rate Limiting**      | N/A            | Yes            |
| **Concurrent Clients** | One            | Multiple       |
| **Network Required**   | No             | Yes            |
| **Latency**            | Very low (~1ms)| Moderate (~50ms)|
| **Security**           | OS-level       | HTTPS + auth   |
| **Debugging**          | STDERR logs    | Error log      |
| **Best For**           | Local AI tools | Remote clients |

## Stream Isolation (STDIO)

Critical concept for STDIO transport:

```
┌───────────────────────────────────────────────────────┐
│                   PROCESS STDIO                       │
│                                                       │
│  STDIN (fd 0)  ──────────┐                          │
│                          │                          │
│                          ▼                          │
│                   ┌──────────────┐                  │
│                   │  Transport   │                  │
│                   │              │                  │
│                   │  read_msg()  │                  │
│                   │  send_msg()  │                  │
│                   │  log()       │                  │
│                   └──────────────┘                  │
│                          │                          │
│                          │                          │
│  STDOUT (fd 1) ◄─────────┤                          │
│  JSON ONLY!              │                          │
│                          │                          │
│  STDERR (fd 2) ◄─────────┘                          │
│  LOGS ONLY!                                         │
│                                                     │
└─────────────────────────────────────────────────────┘

CRITICAL RULES:

✓ DO:
  - Write JSON-RPC to STDOUT via send_message()
  - Write logs to STDERR via log()
  - Flush after every write

✗ DON'T:
  - Use echo, print, var_dump (goes to STDOUT!)
  - Mix logs with JSON responses
  - Write non-JSON to STDOUT
  - Skip flushing
```

## Error Handling Flow

### STDIO Transport

```
Read Message
    │
    ├─► EOF detected
    │   └─► Return null
    │       └─► Server shutdown
    │
    ├─► Invalid JSON
    │   ├─► Log error to STDERR
    │   └─► Send error response
    │       {"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}
    │
    └─► Valid message
        └─► Return to server

Send Message
    │
    ├─► Encoding fails
    │   ├─► Log error to STDERR
    │   └─► Throw exception
    │
    ├─► Write fails
    │   ├─► Log error to STDERR
    │   └─► Throw exception
    │
    └─► Success
        └─► Flush immediately
```

### HTTP Transport

```
REST Request
    │
    ├─► Rate limit exceeded
    │   └─► 429 Too Many Requests
    │       {"jsonrpc":"2.0","error":{"code":-32000,"message":"Rate limit exceeded"},"id":null}
    │
    ├─► Invalid API key
    │   └─► 401 Unauthorized
    │       {"error":"Invalid API key"}
    │
    ├─► Invalid JSON
    │   └─► 400 Bad Request
    │       {"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}
    │
    ├─► Server error
    │   └─► 500 Internal Server Error
    │       {"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":null}
    │
    └─► Success
        └─► 200 OK
            {"jsonrpc":"2.0","result":{...},"id":1}
```

## Configuration Patterns

### Development Configuration

```php
// STDIO - Verbose logging
$stdio = new STDIO_Transport([
    'log_level' => 'debug',
    'log_timestamp' => true,
    'log_prefix' => '[DEV-MCP]',
]);

// HTTP - Permissive CORS, no auth
$http = new HTTP_Transport([
    'cors_enabled' => true,
    'cors_origins' => ['*'],
    'auth_required' => false,
    'rate_limit' => PHP_INT_MAX,
]);
```

### Production Configuration

```php
// STDIO - Minimal logging
$stdio = new STDIO_Transport([
    'log_level' => 'warning',
    'log_timestamp' => true,
    'log_prefix' => '[MCP]',
]);

// HTTP - Strict security
$http = new HTTP_Transport([
    'cors_enabled' => true,
    'cors_origins' => ['https://your-app.com'],
    'auth_required' => true,
    'rate_limit' => 100,
    'rate_limit_window' => 60,
]);
```

## Performance Optimization

### STDIO Bottlenecks

```
Read Performance:
┌─────────────────────────────────────────┐
│ Blocking read (default)                 │
│ • Pro: Simple, no busy-waiting          │
│ • Con: Can't timeout                    │
│ • Latency: ~1ms                         │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Non-blocking read (optional)            │
│ • Pro: Timeout support                  │
│ • Con: More complex, higher CPU         │
│ • Latency: ~1-10ms                      │
└─────────────────────────────────────────┘

Write Performance:
┌─────────────────────────────────────────┐
│ Flushing after every write              │
│ • Required for line-delimited protocol  │
│ • Latency: ~0.1ms                       │
│ • Throughput: ~100k msgs/sec            │
└─────────────────────────────────────────┘
```

### HTTP Bottlenecks

```
┌─────────────────────────────────────────┐
│ WordPress Bootstrap                     │
│ • Time: 50-200ms                        │
│ • Unavoidable overhead                  │
│ • Can cache some data                   │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ JSON Parsing                            │
│ • Time: <1ms for typical messages       │
│ • Can be significant for large payloads │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Rate Limiting                           │
│ • Time: <1ms                            │
│ • Lookup in array                       │
└─────────────────────────────────────────┘
```

## Testing Architecture

```
┌───────────────────────────────────────────────────────────┐
│                   TEST SUITE                              │
└───────────────────────────────────────────────────────────┘
         │
         ├─► Unit Tests (PHPUnit)
         │   ├─► Transport interface compliance
         │   ├─► Configuration methods
         │   ├─► Error handling
         │   └─► Message encoding/decoding
         │
         ├─► Integration Tests
         │   ├─► test-stdio-echo.php
         │   │   └─► Basic message flow
         │   │
         │   └─► test-stdio-interactive.php
         │       └─► Full MCP protocol
         │
         ├─► Automated Tests
         │   ├─► run-tests.sh (Linux/Mac)
         │   └─► run-tests.bat (Windows)
         │
         └─► Manual Tests
             ├─► Claude Desktop integration
             ├─► HTTP endpoint testing (curl/Postman)
             └─► Load testing
```

## Deployment Scenarios

### Scenario 1: Claude Desktop (Local)

```
┌──────────────────┐
│ Claude Desktop   │
│                  │
│ Config:          │
│ {                │
│   "command": "php",
│   "args": [      │
│     "mcp.php"    │
│   ]              │
│ }                │
└────────┬─────────┘
         │
         │ STDIO (local pipes)
         │
         ▼
┌────────────────────┐
│ MCP Server (PHP)   │
│ STDIO Transport    │
│                    │
│ WordPress loaded   │
│ Elementor loaded   │
└────────────────────┘
```

### Scenario 2: Remote Web API

```
┌──────────────┐
│ Browser/App  │
└──────┬───────┘
       │
       │ HTTPS
       │
       ▼
┌──────────────────┐
│ Web Server       │
│ (Apache/Nginx)   │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ WordPress        │
│ REST API         │
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ HTTP Transport   │
│ MCP Server       │
└──────────────────┘
```

### Scenario 3: Hybrid (Both)

```
Local:                      Remote:
┌──────────────┐           ┌──────────────┐
│ Claude       │           │ Web App      │
│ Desktop      │           │              │
└──────┬───────┘           └──────┬───────┘
       │                          │
       │ STDIO                    │ HTTPS
       │                          │
       ▼                          ▼
┌────────────────────────────────────────┐
│        MCP Server                      │
│                                        │
│  ┌──────────┐      ┌──────────┐      │
│  │  STDIO   │      │   HTTP   │      │
│  │ Transport│      │ Transport│      │
│  └────┬─────┘      └────┬─────┘      │
│       │                 │             │
│       └────────┬────────┘             │
│                │                      │
│          ┌─────▼─────┐                │
│          │  Unified  │                │
│          │  Handler  │                │
│          └───────────┘                │
└────────────────────────────────────────┘
```

This architecture provides maximum flexibility for different client types.
