# MCP Troubleshooting Steps

Since MCP tools are still not available after restart, try these steps:

## 1. Check Cursor MCP Status
- Open Cursor
- Go to: **Help → Toggle Developer Tools** (or `Cmd+Option+I` on Mac)
- Click on **Console** tab
- Look for any errors related to "MCP" or "Elementor"
- Share any error messages you see

## 2. Verify MCP Configuration Location
The config file should be at: `~/.cursor/mcp.json`

Verify it exists:
```bash
ls -la ~/.cursor/mcp.json
```

## 3. Try Simplifying the Config
Sometimes Cursor has issues with too many environment variables. The current config should be fine, but if needed we can simplify.

## 4. Check if MCP Server Starts
Try running the MCP server manually to see if it starts:
```bash
cd /Users/oleksandr.molochko/Development/Temp/lawyermolochko
WORDPRESS_BASE_URL="https://lawyermolochko.ddev.site:8443" \
WORDPRESS_USERNAME="admin" \
WORDPRESS_APPLICATION_PASSWORD="VQyDBhUyvbZYgGgo7KBQH69i" \
NODE_TLS_REJECT_UNAUTHORIZED=0 \
npx -y elementor-mcp
```

It should start and wait for stdin (this is normal for MCP servers). Press Ctrl+C to stop.

## 5. Alternative: Use WordPress MCP Instead
You already have `wordpress-mcp` configured with JWT token. We could use that for WordPress operations while we debug Elementor MCP.

## Current Status:
- ✅ WordPress REST API working
- ✅ Configuration file correct
- ✅ Package name correct (elementor-mcp v1.0.1)
- ❌ MCP tools not loading in Cursor

**Next step:** Check Cursor Developer Console for errors!
