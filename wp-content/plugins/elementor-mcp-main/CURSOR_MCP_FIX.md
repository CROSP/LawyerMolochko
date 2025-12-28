# Cursor MCP Configuration Fix

## Problem

Cursor was running `ddev exec` from the wrong directory (`/Users/oleksandr.molochko`), causing this error:

```
Failed to exec command: could not find a project in /Users/oleksandr.molochko. 
Have you run 'ddev config'? Please specify a project name or change directories: 
no .ddev/config.yaml file was found in this directory or any parent
```

## Solution

Created a wrapper script (`mcp-server-wrapper.sh`) that:
1. Automatically changes to the correct project directory
2. Verifies `.ddev/config.yaml` exists
3. Then runs `ddev exec` from the correct location

## Updated Configuration

Your `.cursor/mcp.json` has been updated to use the wrapper script:

```json
{
  "mcpServers": {
    "elementor": {
      "command": "/Users/oleksandr.molochko/Development/Temp/lawyermolochko/wp-content/plugins/elementor-mcp-main/mcp-server-wrapper.sh"
    }
  }
}
```

## Next Steps

1. **Restart Cursor** completely (quit and reopen)
2. The MCP server should now connect successfully
3. You can verify it's working by checking the MCP logs in Cursor

## Testing

The wrapper script has been tested and works from any directory:

```bash
# Test from home directory
cd ~
/Users/oleksandr.molochko/Development/Temp/lawyermolochko/wp-content/plugins/elementor-mcp-main/mcp-server-wrapper.sh
```

The script will automatically:
- Find the project root
- Change to the correct directory
- Run ddev exec from there

## Alternative: If wrapper doesn't work

If for some reason the wrapper doesn't work, you can also use a shell command:

```json
{
  "mcpServers": {
    "elementor": {
      "command": "sh",
      "args": [
        "-c",
        "cd /Users/oleksandr.molochko/Development/Temp/lawyermolochko && ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php"
      ]
    }
  }
}
```

But the wrapper script is preferred as it's more robust.

