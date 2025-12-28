# Elementor MCP Server Migration

## ✅ Migration Complete

Successfully migrated from PHP-based Elementor MCP to JavaScript-based `@aguaitech/Elementor-MCP`.

## What Changed

### Old Setup (Removed)
- **Type**: PHP-based MCP server running inside DDEV
- **Location**: `wp-content/plugins/elementor-mcp-main/`
- **Method**: Direct PHP execution via `ddev exec`
- **Complexity**: Required wrapper scripts, DDEV-specific configuration

### New Setup (Active)
- **Type**: JavaScript-based MCP server via npm/npx
- **Package**: `@aguaitech/Elementor-MCP` (elementor-mcp)
- **Method**: WordPress REST API with Application Passwords
- **Simplicity**: No DDEV wrapper needed, works from anywhere

## Configuration

### MCP Config (`~/.cursor/mcp.json`)

```json
{
  "mcpServers": {
    "Elementor MCP": {
      "command": "npx",
      "args": ["-y", "elementor-mcp"],
      "env": {
        "WP_URL": "https://lawyermolochko.ddev.site:8443",
        "WP_APP_USER": "admin",
        "WP_APP_PASSWORD": "rJtC9nPsIC54LE0buyUjryzj"
      }
    }
  }
}
```

### Credentials

- **WordPress URL**: `https://lawyermolochko.ddev.site:8443`
- **Username**: `admin`
- **Application Password**: `rJtC9nPsIC54LE0buyUjryzj`
  - Created via: `wp user application-password create 1 'Elementor MCP'`
  - **Note**: Keep spaces in the password when using it (format: `XXXX XXXX XXXX XXXX`)

## Benefits of New Setup

1. ✅ **Simpler**: No DDEV wrapper scripts needed
2. ✅ **Portable**: Works from any directory
3. ✅ **Standard**: Uses WordPress REST API (standard WordPress feature)
4. ✅ **Maintained**: Active project with community support
5. ✅ **Cross-platform**: Works on Mac, Linux, and Windows

## Next Steps

1. **Restart Cursor** completely (quit and reopen)
2. The new MCP server will automatically install via `npx` on first use
3. Test by asking: "What Elementor widgets are available?"

## Application Password Management

If you need to manage the application password:

```bash
# List application passwords
ddev exec wp user application-password list 1

# Create a new one
ddev exec wp user application-password create 1 'Elementor MCP'

# Revoke one
ddev exec wp user application-password revoke 1 <uuid>
```

## Old Files (Can be removed)

The following files/directories from the old setup can be safely removed:

- `wp-content/plugins/elementor-mcp-main/` (entire directory)
- Any wrapper scripts or test files

## Troubleshooting

### If MCP doesn't connect:

1. **Check Node.js is installed**: `node --version`
2. **Verify WordPress URL is accessible**: Visit `https://lawyermolochko.ddev.site:8443` in browser
3. **Test REST API**: Visit `https://lawyermolochko.ddev.site:8443/wp-json/wp/v2/`
4. **Check application password**: Verify it's still active in WordPress admin

### Application Password Format

When using the password in config, WordPress formats it with spaces:
- Format: `XXXX XXXX XXXX XXXX` (4 groups of 4 characters)
- The password shown above is already in the correct format

## References

- **Package**: [@aguaitech/Elementor-MCP](https://github.com/aguaitech/Elementor-MCP)
- **Installation**: `npx -y elementor-mcp`
- **Documentation**: See GitHub repository README

