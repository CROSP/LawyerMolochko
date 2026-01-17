# Elementor MCP - DDEV Quick Setup Guide

## ✅ Configuration Status

- ✅ WordPress root auto-detects DDEV container path (`/var/www/html`)
- ✅ MCP server tested and working inside DDEV container
- ✅ Elementor 3.32.5 detected and loaded

## Quick Setup

### 1. Activate Plugin
WordPress Admin → Plugins → Activate "Elementor MCP"

### 2. Claude Desktop Configuration

Edit: `~/Library/Application Support/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "elementor": {
      "command": "ddev",
      "args": [
        "exec",
        "php",
        "/var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php"
      ],
      "cwd": "/Users/oleksandr.molochko/Development/Temp/lawyermolochko"
    }
  }
}
```

**Important:**
- `cwd` must be your DDEV project root directory
- Path in `args` uses container path `/var/www/html/...`
- Make sure DDEV is running: `ddev start`

### 3. Restart Claude Desktop

Quit and reopen Claude Desktop after editing the config.

## Testing

Test the MCP server manually:

```bash
# Ensure DDEV is running
ddev start

# Test MCP server
ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php
```

Press Ctrl+C to stop the test.

## Troubleshooting

### DDEV not running
```bash
ddev start
```

### Check DDEV status
```bash
ddev describe
```

### View MCP server logs
```bash
# From host machine
cat wp-content/plugins/elementor-mcp-main/logs/mcp-server.log

# Or from inside container
ddev exec cat /var/www/html/wp-content/plugins/elementor-mcp-main/logs/mcp-server.log
```

### Verify Elementor is active
```bash
ddev exec wp plugin list | grep elementor
```

## Project Info

- **DDEV Project**: lawyermolochko
- **Container PHP**: 8.2.29
- **WordPress**: Running in DDEV
- **Elementor**: 3.32.5
- **MCP Server Path**: `/var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php`

## Notes

- The MCP server must run inside the DDEV container to access WordPress and database
- All paths in Claude Desktop config use container paths (`/var/www/html/...`)
- The `cwd` in Claude Desktop config should be your host machine project root




