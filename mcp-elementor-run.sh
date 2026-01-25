#!/bin/bash
# Elementor MCP launcher for Cursor - uses full paths so it works when Cursor's PATH is minimal
export PATH="/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin"
cd "/Users/oleksandr.molochko/Development/Temp/lawyermolochko"
exec /opt/homebrew/bin/ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php
