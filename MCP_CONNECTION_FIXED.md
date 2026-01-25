# MCP connection fixed

## What was wrong
- **Elementor MCP** (npx `elementor-mcp`): was failing with "The MCP server errored".
- **elementor-mcp-main** PHP server: `config.php` had wrong `ELEMENTOR_MCP_WP_ROOT` (`C:\xampp\htdocs\wpc-new`), so WordPress could not bootstrap in DDEV.

## What was changed

### 1. `config.php` (elementor-mcp-main)
- **Before:** `ELEMENTOR_MCP_WP_ROOT` = `'C:\xampp\htdocs\wpc-new'`
- **After:** `ELEMENTOR_MCP_WP_ROOT` = `'/var/www/html'` (DDEV project root in the container)

### 2. Cursor MCP config (`~/.cursor/mcp.json`) – Elementor MCP
- **Before:** npx `elementor-mcp` (external package, kept failing).
- **After:** local PHP MCP server from the **elementor-mcp-main** plugin via DDEV:

```json
"Elementor MCP": {
  "command": "ddev",
  "args": ["exec", "php", "/var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php"],
  "cwd": "/Users/oleksandr.molochko/Development/Temp/lawyermolochko"
}
```

- Runs `mcp-server.php` inside the DDEV container (STDIO MCP).
- Uses your existing Elementor + WordPress setup.
- No REST API or app password; it uses WordPress/Elementor in the same environment.

### 3. WordPress MCP
- Left as is; it was already working (tools and resources present).
- App password `M1JhdPjdcuQT3T6UDsj8l4mv` checked; REST API returns 200.

## What you need to do

1. **Start DDEV** (if it’s not running):
   ```bash
   ddev start
   ```

2. **Restart Cursor**
   - Quit Cursor completely (`Cmd+Q` on Mac).
   - Reopen Cursor.
   - Wait 20–30 seconds for MCP to start.

3. **Check `ddev` in PATH**
   - Cursor must be able to run `ddev` (e.g. from Terminal or an environment where `ddev` is installed).
   - If Cursor was opened from Finder/Spotlight and `ddev` is not in PATH, open Cursor from a terminal or ensure `ddev` is on the PATH used by Cursor.

## Quick test after restart

- **Elementor MCP:** e.g. “Get Elementor document for page 222” or “List Elementor widgets”.
- **WordPress MCP:** e.g. “Get site info” or “List WordPress posts”.

---

**Config file:** `~/.cursor/mcp.json`
