# Elementor MCP Setup Instructions for DDEV

## ✅ Configuration Complete

The Elementor MCP plugin has been configured for your DDEV environment. The WordPress root path is set to auto-detect DDEV container paths (`/var/www/html`).

## Step 1: Activate the Plugin in WordPress

1. Log in to your WordPress admin dashboard
2. Go to **Plugins** → **Installed Plugins**
3. Find **"Elementor MCP"** in the list
4. Click **"Activate"**

**Note:** Make sure Elementor is already installed and activated before activating Elementor MCP.

## Step 2: Configure Claude Desktop for DDEV

Since you're using DDEV, the MCP server needs to run inside the DDEV container using `ddev exec`.

### For macOS:

1. Open or create the Claude Desktop config file:
   ```bash
   nano ~/Library/Application\ Support/Claude/claude_desktop_config.json
   ```

2. Add the Elementor MCP server configuration. Your file should look like this:

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
   - The `cwd` (current working directory) must be set to your DDEV project root
   - The path inside `args` uses the container path `/var/www/html/...`
   - If you already have other MCP servers configured, add the `"elementor"` entry to the existing `"mcpServers"` object

3. Save the file (Ctrl+O, then Enter, then Ctrl+X in nano)

4. **Restart Claude Desktop** completely (quit and reopen the application)

### Alternative: Using ddev exec with full path

If the above doesn't work, you can also try this format:

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

## Step 3: Verify Installation

After restarting Claude Desktop, you can test if Elementor MCP is working by asking Claude:

- "What Elementor widgets are available?"
- "Show me all Elementor pages"
- "List Elementor templates"

If Claude can respond to these queries, the MCP server is working correctly.

## Troubleshooting

### Plugin won't activate
- Ensure Elementor is installed and activated first
- Check PHP version is 7.4 or higher: `php -v`
- Check WordPress version is 6.6 or higher

### Claude Desktop can't connect
- **For DDEV**: Make sure DDEV is running: `ddev start`
- Verify the `cwd` path in `claude_desktop_config.json` points to your DDEV project root
- Verify the path inside `args` uses container path `/var/www/html/...`
- Test DDEV exec manually: `ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php`
- Check the log file: `wp-content/plugins/elementor-mcp-main/logs/mcp-server.log`
- Verify DDEV is accessible: `ddev describe`

### WordPress bootstrap errors
- Ensure your WordPress database is properly configured
- Check that `wp-config.php` is correctly set up
- Verify DDEV or your local development environment is running

### Test MCP Server Manually (DDEV)

You can test the MCP server inside the DDEV container:

```bash
# Make sure DDEV is running
ddev start

# Test the MCP server
ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php
```

If it starts without errors, the server is working. Press Ctrl+C to stop it.

**Note:** The server must run inside the DDEV container to access WordPress and the database.

## Configuration Options

The main configuration file is located at:
`wp-content/plugins/elementor-mcp-main/config.php`

Key settings you can adjust:
- **Log Level**: Change `ELEMENTOR_MCP_LOG_LEVEL` (options: 'debug', 'info', 'warning', 'error')
- **Log File**: Change `ELEMENTOR_MCP_LOG_FILE` path
- **WordPress Root**: Already configured for auto-detection

## Next Steps

Once Elementor MCP is set up, you can:
- Ask Claude to analyze your Elementor pages
- Get information about available widgets
- Query Elementor templates
- Analyze page structures

For more information, see the [README.md](README.md) file.

