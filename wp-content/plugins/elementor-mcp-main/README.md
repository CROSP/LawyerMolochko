# Elementor MCP - Model Context Protocol Server for Elementor

A WordPress plugin that provides a Model Context Protocol (MCP) server interface for Elementor, enabling AI assistants like Claude to interact with Elementor's page builder functionality.

## What This Plugin Does

Elementor MCP bridges the gap between AI assistants and Elementor, WordPress's leading page builder. It implements the Model Context Protocol (MCP) to provide:

- **Widget Discovery**: Query and explore available Elementor widgets
- **Template Management**: Access and analyze Elementor templates
- **Page Structure Analysis**: Inspect page layouts and widget configurations
- **Settings Retrieval**: Get widget and control settings
- **Schema Information**: Access Elementor's data schemas and structures

This enables AI assistants to understand and work with Elementor sites through a standardized protocol.

## Requirements

### WordPress Environment
- **PHP**: 7.4 or higher
- **WordPress**: 6.6 or higher
- **Elementor**: 3.0.0 or higher (must be installed and activated)

### For MCP Server Operation
- **Node.js**: Not required (uses PHP stdio)
- **Claude Desktop**: Latest version (for AI assistant integration)

## Installation

### 1. Manual Installation

1. Download or clone this repository
2. Place the `elementor-mcp` folder in your WordPress `wp-content/plugins/` directory
3. Run `composer install` in the plugin directory (if using Composer dependencies)
4. Activate the plugin through the WordPress admin panel

### 2. Composer Installation (Development)

```bash
cd wp-content/plugins
git clone https://github.com/yourusername/elementor-mcp.git
cd elementor-mcp
composer install
```

### 3. Claude Code Installation

```bash
claude mcp add wpcursor-elementor --transport http https://your-wpsite.com/wp-json/elementor-mcp/v1/rpc
```
Then activate through WordPress admin.

## Configuration

### Claude Desktop Integration

Add this configuration to your Claude Desktop config file:

**macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`

**Windows**: `%APPDATA%\Claude\claude_desktop_config.json`

```json
{
  "mcpServers": {
    "elementor": {
      "command": "php",
      "args": [
        "/path/to/wordpress/wp-content/plugins/elementor-mcp/server.php",
        "--site-url=https://yoursite.com"
      ]
    }
  }
}
```

Replace `/path/to/wordpress/` and `https://yoursite.com` with your actual paths.

## Basic Usage

### From Claude Desktop

Once configured, you can interact with Elementor through natural language:

```
"Show me all available Elementor widgets"
"What widgets are used on the homepage?"
"Get the settings for the heading widget"
"List all Elementor templates"
```

### Available MCP Tools

The plugin exposes these MCP tools (to be implemented):

- `get_widgets` - List all registered Elementor widgets
- `get_widget_controls` - Get controls for a specific widget
- `get_templates` - List Elementor templates
- `get_page_structure` - Analyze a page's Elementor structure
- `get_settings` - Retrieve Elementor settings

## Architecture

```
elementor-mcp/
├── elementor-mcp.php           # Main plugin file
├── composer.json               # PHP dependencies
├── includes/
│   ├── class-plugin.php        # Main plugin class (singleton)
│   ├── class-autoloader.php    # PSR-4 autoloader
│   ├── server/                 # MCP server implementation
│   ├── managers/               # Manager classes
│   ├── tools/                  # MCP tool implementations
│   └── transport/              # Communication layer (stdio/SSE)
└── README.md                   # This file
```

## Development

### Running Tests

```bash
composer test
```

### Code Standards

```bash
# Check code standards
composer lint

# Auto-fix code standards
composer lint:fix
```

### Building

The plugin uses WordPress and Elementor hooks, no build process is required for basic functionality.

## Key Implementation Details

### Initialization Sequence

1. WordPress loads `elementor-mcp.php`
2. Plugin checks for PHP 7.4+ and WordPress 6.6+
3. Waits for `elementor/loaded` action hook
4. Initializes `Elementor_MCP\Plugin` singleton
5. Registers managers and components
6. Sets up MCP server if in CLI mode

### Hook Integration

- **`plugins_loaded` (priority 11)**: Main initialization
- **`elementor/loaded`**: Dependency check
- **`elementor_mcp/loaded`**: Plugin fully loaded
- **`elementor_mcp/init`**: Components initialized

### Singleton Pattern

Following Elementor's architecture, the plugin uses a singleton pattern:

```php
// Access plugin instance
$plugin = Elementor_MCP\Plugin::instance();

// Access Elementor
$elementor = $plugin->get_elementor();
```

## Security Considerations

- Plugin validates all input from MCP protocol
- WordPress nonces used for state-changing operations
- Capability checks for sensitive data access
- JSON schema validation for protocol messages

## Troubleshooting

### Plugin doesn't activate
- Ensure Elementor is installed and activated first
- Check PHP version is 7.4 or higher
- Verify WordPress version is 6.6 or higher

### MCP server not responding
- Check Claude Desktop config file syntax
- Verify file paths are absolute and correct
- Check PHP CLI is available: `php --version`
- Review WordPress error logs

### Widgets not showing
- Ensure Elementor is properly loaded
- Check widget registration hooks are firing
- Verify plugin initialization sequence

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Follow WordPress coding standards
4. Add tests for new functionality
5. Submit a pull request

## License

This plugin is licensed under GPL v3 or later, same as WordPress and Elementor.

## Credits

- Built on top of [Elementor](https://elementor.com/)
- Implements [Model Context Protocol](https://modelcontextprotocol.io/)
- Designed for [Claude Desktop](https://claude.ai/desktop)

## Roadmap

- [ ] Core MCP server implementation
- [ ] Widget discovery tools
- [ ] Template management tools
- [ ] Page structure analysis
- [ ] Settings retrieval
- [ ] SSE transport support
- [ ] Advanced filtering and search
- [ ] Widget creation capabilities
- [ ] Template import/export via MCP

## Support

For issues and questions:
- GitHub Issues: [Create an issue](https://github.com/yourusername/elementor-mcp/issues)
- WordPress Forums: [Plugin support](https://wordpress.org/support/plugin/elementor-mcp)

---

**Note**: This plugin is in active development. The MCP specification and implementation are subject to change.
"# elementor-mcp" 
