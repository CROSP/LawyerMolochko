<?php
/**
 * Security Class
 *
 * Provides security and validation functionality for the MCP server:
 * - WordPress capability checks
 * - Nonce validation (when in WordPress context)
 * - Input sanitization helpers
 * - Rate limiting (simple implementation)
 * - Request validation
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Security
 *
 * Handles all security-related operations for the MCP server.
 */
class Security {
    /**
     * Nonce action for MCP operations
     */
    const NONCE_ACTION = 'elementor_mcp_action';

    /**
     * Nonce name
     */
    const NONCE_NAME = '_elementor_mcp_nonce';

    /**
     * Rate limit storage
     *
     * Format: ['tool_name' => ['count' => int, 'reset_time' => timestamp]]
     *
     * @var array
     */
    private $rate_limits = [];

    /**
     * Rate limit window in seconds (default: 60 seconds)
     *
     * @var int
     */
    private $rate_limit_window = 60;

    /**
     * Maximum requests per window (default: 100)
     *
     * @var int
     */
    private $max_requests_per_window = 100;

    /**
     * Tool-specific rate limits
     *
     * Format: ['tool_name' => max_requests]
     *
     * @var array
     */
    private $tool_rate_limits = [];

    /**
     * Whether to enforce WordPress capabilities
     *
     * @var bool
     */
    private $enforce_capabilities = true;

    /**
     * Required capability for tool execution
     *
     * @var string
     */
    private $default_capability = 'edit_posts';

    /**
     * Tool-specific capability requirements
     *
     * Format: ['tool_name' => 'capability']
     *
     * @var array
     */
    private $tool_capabilities = [];

    /**
     * Allowed tool names (whitelist)
     *
     * Empty array means all tools are allowed
     *
     * @var array
     */
    private $allowed_tools = [];

    /**
     * Blocked tool names (blacklist)
     *
     * @var array
     */
    private $blocked_tools = [];

    /**
     * Constructor
     *
     * @param array $config Security configuration
     */
    public function __construct($config = []) {
        $defaults = [
            'enforce_capabilities' => defined('ABSPATH'),
            'default_capability' => 'edit_posts',
            'rate_limit_window' => 60,
            'max_requests_per_window' => 100,
            'tool_capabilities' => [],
            'tool_rate_limits' => [],
        ];

        $config = array_merge($defaults, $config);

        $this->enforce_capabilities = $config['enforce_capabilities'];
        $this->default_capability = $config['default_capability'];
        $this->rate_limit_window = $config['rate_limit_window'];
        $this->max_requests_per_window = $config['max_requests_per_window'];
        $this->tool_capabilities = $config['tool_capabilities'];
        $this->tool_rate_limits = $config['tool_rate_limits'];

        // Set default tool capabilities for sensitive operations
        $this->set_default_tool_capabilities();
    }

    /**
     * Set default capability requirements for sensitive tools
     *
     * @return void
     */
    private function set_default_tool_capabilities() {
        $sensitive_tools = [
            // Document management - requires edit permissions
            'save_document' => 'edit_posts',
            'create_elementor_page' => 'edit_pages',
            'delete_elementor_data' => 'delete_posts',
            'update_elementor_data' => 'edit_posts',

            // Settings - requires manage options
            'update_elementor_settings' => 'manage_options',
            'update_global_colors' => 'edit_theme_options',
            'update_global_fonts' => 'edit_theme_options',
            'update_breakpoints' => 'edit_theme_options',
            'update_kit_settings' => 'edit_theme_options',

            // Template management
            'delete_template' => 'delete_posts',
            'import_template' => 'import',
            'export_template' => 'export',

            // Import/Export
            'import_site' => 'manage_options',
            'export_site' => 'manage_options',
            'import_settings' => 'manage_options',

            // System operations
            'regenerate_css' => 'manage_options',
            'clear_elementor_cache' => 'manage_options',
        ];

        foreach ($sensitive_tools as $tool => $capability) {
            if (!isset($this->tool_capabilities[$tool])) {
                $this->tool_capabilities[$tool] = $capability;
            }
        }
    }

    /**
     * Check if a user can execute a tool
     *
     * @param string $tool_name Tool name
     * @param array $arguments Tool arguments
     * @return bool Whether the user has permission
     */
    public function can_execute_tool($tool_name, $arguments = []) {
        // Check if tool is blocked
        if (in_array($tool_name, $this->blocked_tools, true)) {
            return false;
        }

        // Check whitelist (if configured)
        if (!empty($this->allowed_tools) && !in_array($tool_name, $this->allowed_tools, true)) {
            return false;
        }

        // Check WordPress capabilities (if enabled)
        if ($this->enforce_capabilities) {
            $required_capability = $this->get_required_capability($tool_name, $arguments);

            if (!$this->current_user_can($required_capability)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get required capability for a tool
     *
     * @param string $tool_name Tool name
     * @param array $arguments Tool arguments
     * @return string Required capability
     */
    private function get_required_capability($tool_name, $arguments = []) {
        // Check tool-specific capability
        if (isset($this->tool_capabilities[$tool_name])) {
            return $this->tool_capabilities[$tool_name];
        }

        // Special handling for post-based operations
        if (isset($arguments['post_id'])) {
            $post_id = absint($arguments['post_id']);
            $post_type = get_post_type($post_id);

            if ($post_type === 'page') {
                return 'edit_pages';
            } elseif ($post_type === 'elementor_library') {
                return 'edit_posts';
            }

            return 'edit_posts';
        }

        // Default capability
        return $this->default_capability;
    }

    /**
     * Check if current user has a capability
     *
     * Wrapper around WordPress current_user_can with fallback for non-WP context
     *
     * @param string $capability Capability to check
     * @return bool Whether user has capability
     */
    private function current_user_can($capability) {
        // In WordPress context
        if (function_exists('current_user_can')) {
            return current_user_can($capability);
        }

        // In CLI/non-WP context - allow all operations
        // This should be combined with other authentication mechanisms
        if (defined('STDIN') && php_sapi_name() === 'cli') {
            return true;
        }

        // Default deny
        return false;
    }

    /**
     * Check rate limit for a tool
     *
     * @param string $tool_name Tool name
     * @return bool Whether the request is within rate limits
     */
    public function check_rate_limit($tool_name) {
        $current_time = time();

        // Get tool-specific limit or use default
        $max_requests = isset($this->tool_rate_limits[$tool_name])
            ? $this->tool_rate_limits[$tool_name]
            : $this->max_requests_per_window;

        // Initialize rate limit tracking for this tool
        if (!isset($this->rate_limits[$tool_name])) {
            $this->rate_limits[$tool_name] = [
                'count' => 0,
                'reset_time' => $current_time + $this->rate_limit_window,
            ];
        }

        $rate_limit = &$this->rate_limits[$tool_name];

        // Reset counter if window has passed
        if ($current_time >= $rate_limit['reset_time']) {
            $rate_limit['count'] = 0;
            $rate_limit['reset_time'] = $current_time + $this->rate_limit_window;
        }

        // Check if limit exceeded
        if ($rate_limit['count'] >= $max_requests) {
            return false;
        }

        // Increment counter
        $rate_limit['count']++;

        return true;
    }

    /**
     * Reset rate limit for a tool
     *
     * @param string|null $tool_name Tool name or null for all tools
     * @return void
     */
    public function reset_rate_limit($tool_name = null) {
        if ($tool_name === null) {
            $this->rate_limits = [];
        } else {
            unset($this->rate_limits[$tool_name]);
        }
    }

    /**
     * Get rate limit status for a tool
     *
     * @param string $tool_name Tool name
     * @return array Rate limit status
     */
    public function get_rate_limit_status($tool_name) {
        if (!isset($this->rate_limits[$tool_name])) {
            return [
                'remaining' => $this->max_requests_per_window,
                'reset_time' => time() + $this->rate_limit_window,
            ];
        }

        $rate_limit = $this->rate_limits[$tool_name];
        $max_requests = isset($this->tool_rate_limits[$tool_name])
            ? $this->tool_rate_limits[$tool_name]
            : $this->max_requests_per_window;

        return [
            'remaining' => max(0, $max_requests - $rate_limit['count']),
            'reset_time' => $rate_limit['reset_time'],
        ];
    }

    /**
     * Create a nonce
     *
     * @return string Nonce value
     */
    public function create_nonce() {
        if (function_exists('wp_create_nonce')) {
            return wp_create_nonce(self::NONCE_ACTION);
        }

        // Fallback for non-WP context
        return hash('sha256', self::NONCE_ACTION . time());
    }

    /**
     * Verify a nonce
     *
     * @param string $nonce Nonce value to verify
     * @return bool Whether nonce is valid
     */
    public function verify_nonce($nonce) {
        if (function_exists('wp_verify_nonce')) {
            return (bool) wp_verify_nonce($nonce, self::NONCE_ACTION);
        }

        // In non-WP context, nonces are not enforced
        return true;
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Data to sanitize
     * @param string $type Data type hint
     * @return mixed Sanitized data
     */
    public function sanitize_input($data, $type = 'text') {
        if (is_array($data)) {
            return array_map(function ($item) use ($type) {
                return $this->sanitize_input($item, $type);
            }, $data);
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return intval($data);

            case 'float':
            case 'number':
                return floatval($data);

            case 'bool':
            case 'boolean':
                return (bool) $data;

            case 'email':
                return function_exists('sanitize_email')
                    ? sanitize_email($data)
                    : filter_var($data, FILTER_SANITIZE_EMAIL);

            case 'url':
                return function_exists('esc_url_raw')
                    ? esc_url_raw($data)
                    : filter_var($data, FILTER_SANITIZE_URL);

            case 'key':
                return function_exists('sanitize_key')
                    ? sanitize_key($data)
                    : preg_replace('/[^a-z0-9_\-]/', '', strtolower($data));

            case 'html':
                return function_exists('wp_kses_post')
                    ? wp_kses_post($data)
                    : strip_tags($data);

            case 'textarea':
                return function_exists('sanitize_textarea_field')
                    ? sanitize_textarea_field($data)
                    : strip_tags($data);

            case 'text':
            default:
                return function_exists('sanitize_text_field')
                    ? sanitize_text_field($data)
                    : strip_tags($data);
        }
    }

    /**
     * Validate post ID and check access
     *
     * @param int $post_id Post ID
     * @param string $capability Required capability
     * @return bool Whether post is valid and accessible
     */
    public function validate_post_access($post_id, $capability = 'edit_post') {
        $post_id = absint($post_id);

        if ($post_id <= 0) {
            return false;
        }

        // In WordPress context
        if (function_exists('get_post')) {
            $post = get_post($post_id);

            if (!$post) {
                return false;
            }

            // Check if user can edit this specific post
            if (function_exists('current_user_can')) {
                return current_user_can($capability, $post_id);
            }
        }

        // In non-WP context, basic validation only
        return $post_id > 0;
    }

    /**
     * Validate JSON schema
     *
     * Basic JSON schema validation for inputs.
     *
     * @param array $data Data to validate
     * @param array $schema JSON schema
     * @return array|true True if valid, array of errors otherwise
     */
    public function validate_schema($data, $schema) {
        $errors = [];

        // Check required properties
        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $required_field) {
                if (!isset($data[$required_field])) {
                    $errors[] = "Required field missing: {$required_field}";
                }
            }
        }

        // Validate property types
        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $property => $property_schema) {
                if (!isset($data[$property])) {
                    continue;
                }

                $value = $data[$property];
                $expected_type = $property_schema['type'] ?? null;

                if ($expected_type && !$this->validate_type($value, $expected_type)) {
                    $errors[] = "Invalid type for {$property}: expected {$expected_type}";
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validate value type
     *
     * @param mixed $value Value to check
     * @param string $expected_type Expected type
     * @return bool Whether value matches type
     */
    private function validate_type($value, $expected_type) {
        switch ($expected_type) {
            case 'string':
                return is_string($value);

            case 'number':
            case 'integer':
                return is_numeric($value);

            case 'boolean':
                return is_bool($value);

            case 'array':
                return is_array($value);

            case 'object':
                return is_object($value) || (is_array($value) && !isset($value[0]));

            case 'null':
                return $value === null;

            default:
                return true;
        }
    }

    /**
     * Set tool-specific capability requirement
     *
     * @param string $tool_name Tool name
     * @param string $capability Required capability
     * @return void
     */
    public function set_tool_capability($tool_name, $capability) {
        $this->tool_capabilities[$tool_name] = $capability;
    }

    /**
     * Set tool-specific rate limit
     *
     * @param string $tool_name Tool name
     * @param int $max_requests Maximum requests per window
     * @return void
     */
    public function set_tool_rate_limit($tool_name, $max_requests) {
        $this->tool_rate_limits[$tool_name] = $max_requests;
    }

    /**
     * Block a tool
     *
     * @param string $tool_name Tool name to block
     * @return void
     */
    public function block_tool($tool_name) {
        if (!in_array($tool_name, $this->blocked_tools, true)) {
            $this->blocked_tools[] = $tool_name;
        }
    }

    /**
     * Unblock a tool
     *
     * @param string $tool_name Tool name to unblock
     * @return void
     */
    public function unblock_tool($tool_name) {
        $this->blocked_tools = array_diff($this->blocked_tools, [$tool_name]);
    }

    /**
     * Set allowed tools whitelist
     *
     * @param array $tools Array of allowed tool names
     * @return void
     */
    public function set_allowed_tools($tools) {
        $this->allowed_tools = $tools;
    }

    /**
     * Add tool to allowed list
     *
     * @param string $tool_name Tool name
     * @return void
     */
    public function allow_tool($tool_name) {
        if (!in_array($tool_name, $this->allowed_tools, true)) {
            $this->allowed_tools[] = $tool_name;
        }
    }

    /**
     * Get security configuration
     *
     * @return array Current security configuration
     */
    public function get_config() {
        return [
            'enforce_capabilities' => $this->enforce_capabilities,
            'default_capability' => $this->default_capability,
            'rate_limit_window' => $this->rate_limit_window,
            'max_requests_per_window' => $this->max_requests_per_window,
            'tool_capabilities' => $this->tool_capabilities,
            'tool_rate_limits' => $this->tool_rate_limits,
            'allowed_tools' => $this->allowed_tools,
            'blocked_tools' => $this->blocked_tools,
        ];
    }

    /**
     * Get security statistics
     *
     * @return array Security statistics
     */
    public function get_stats() {
        return [
            'total_rate_limited_tools' => count($this->rate_limits),
            'blocked_tools_count' => count($this->blocked_tools),
            'allowed_tools_count' => count($this->allowed_tools),
            'rate_limits' => $this->rate_limits,
        ];
    }
}
