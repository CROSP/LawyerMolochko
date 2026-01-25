# MCP fix applied

## What was broken
- **Elementor MCP**: "The MCP server errored"
- **WordPress MCP**: "Connection Failed" (JWT expired)

## What was changed

### 1. New application password
- Created: `M1JhdPjdcuQT3T6UDsj8l4mv` (User → Profile → Application Passwords, or via WP-CLI)
- Both MCP servers now use this password

### 2. WordPress MCP
- Switched from **JWT_TOKEN** (expired) to **WP_API_USERNAME** + **WP_API_PASSWORD**
- Uses the new app password
- Added **NODE_TLS_REJECT_UNAUTHORIZED=0** for local HTTPS
- Kept full path to `npx`

### 3. Elementor MCP
- New app password: **M1JhdPjdcuQT3T6UDsj8l4mv**
- **Full path to npx** (same as WordPress MCP): `/Users/oleksandr.molochko/.nvm/versions/node/v20.19.5/bin/npx`
- **Removed** `ELEMENTOR_MCP_MODE` (can cause crashes if unsupported)
- Kept **NODE_TLS_REJECT_UNAUTHORIZED=0**

## What you need to do

1. Quit Cursor completely: **Cmd+Q** (Mac)
2. Wait ~5 seconds
3. Open Cursor again
4. Wait ~30 seconds for MCP to start
5. Test: e.g. “Get page with ID 222” or “List WordPress pages”

## If it still fails

1. **Cursor**: **Help → Toggle Developer Tools → Console** and look for MCP/Elementor/wordpress errors
2. **New JWT for WordPress MCP (optional)**  
   In WP Admin: **Settings → MCP → Authentication Tokens**, create a token and use it as `JWT_TOKEN` instead of `WP_API_USERNAME`/`WP_API_PASSWORD` if you prefer JWT

---
**Config file:** `~/.cursor/mcp.json`
