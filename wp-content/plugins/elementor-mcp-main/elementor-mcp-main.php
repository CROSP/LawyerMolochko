<?php
/**
 * Plugin Name: Elementor MCP
 * Description: Model Context Protocol (MCP) server for Elementor - enables AI assistants to interact with Elementor via Claude Desktop and other MCP clients.
 * Plugin URI: https://github.com/aguaitech/Elementor-MCP
 * Version: 1.0.0
 * Author: Elementor MCP
 * Author URI: https://github.com/aguaitech/Elementor-MCP
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Text Domain: elementor-mcp
 * Domain Path: /languages
 *
 * @package Elementor_MCP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the main plugin logic
require_once __DIR__ . '/elementor-mcp.php';
