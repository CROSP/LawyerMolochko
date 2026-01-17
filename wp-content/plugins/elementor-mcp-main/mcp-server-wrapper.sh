#!/bin/bash
#
# Wrapper script for Elementor MCP Server in DDEV
# This ensures ddev exec runs from the correct project directory
#

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Calculate project root (3 levels up from plugin directory)
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

# Change to project root directory
cd "$PROJECT_ROOT" || {
    echo "Error: Could not change to project directory: $PROJECT_ROOT" >&2
    exit 1
}

# Verify .ddev/config.yaml exists
if [ ! -f ".ddev/config.yaml" ]; then
    echo "Error: .ddev/config.yaml not found in $PROJECT_ROOT" >&2
    echo "Current directory: $(pwd)" >&2
    exit 1
fi

# Run ddev exec with the MCP server
exec ddev exec php /var/www/html/wp-content/plugins/elementor-mcp-main/mcp-server.php "$@"




