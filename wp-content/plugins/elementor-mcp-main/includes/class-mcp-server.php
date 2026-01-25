<?php
/**
 * MCP Server Core
 *
 * Handles the Model Context Protocol server implementation following
 * JSON-RPC 2.0 specification.
 *
 * This server:
 * - Accepts a transport layer for communication (STDIO, HTTP, WebSocket)
 * - Registers and manages MCP tools
 * - Handles MCP protocol methods (initialize, tools/list, tools/call, etc.)
 * - Implements JSON-RPC 2.0 error handling
 * - Provides a run loop for continuous operation
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

use ElementorMCP\Transport\Transport_Interface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class MCP_Server
 *
 * Main MCP server implementation following the Model Context Protocol specification.
 */
class MCP_Server {
    /**
     * MCP protocol version
     */
    const PROTOCOL_VERSION = '2024-11-05';

    /**
     * Server information
     */
    const SERVER_NAME = 'elementor-mcp';
    const SERVER_VERSION = '1.0.0';

    /**
     * JSON-RPC 2.0 error codes
     */
    const ERROR_PARSE_ERROR = -32700;
    const ERROR_INVALID_REQUEST = -32600;
    const ERROR_METHOD_NOT_FOUND = -32601;
    const ERROR_INVALID_PARAMS = -32602;
    const ERROR_INTERNAL_ERROR = -32603;

    /**
     * MCP-specific error codes
     */
    const ERROR_TOOL_NOT_FOUND = -32001;
    const ERROR_TOOL_EXECUTION_FAILED = -32002;
    const ERROR_UNAUTHORIZED = -32003;
    const ERROR_RESOURCE_NOT_FOUND = -32004;

    /**
     * Transport layer
     *
     * @var Transport_Interface
     */
    private $transport;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $logger;

    /**
     * Security instance
     *
     * @var Security
     */
    private $security;

    /**
     * Registered tools
     *
     * Format: ['tool_name' => Tool_Instance]
     *
     * @var array
     */
    private $tools = [];

    /**
     * Server capabilities
     *
     * @var array
     */
    private $capabilities = [];

    /**
     * Server initialization state
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Client information from initialize request
     *
     * @var array|null
     */
    private $client_info = null;

    /**
     * Whether the server is running
     *
     * @var bool
     */
    private $running = false;

    /**
     * Constructor
     *
     * @param Transport_Interface $transport Transport layer implementation
     * @param Logger|null $logger Optional logger instance
     * @param Security|null $security Optional security instance
     */
    public function __construct($transport, $logger = null, $security = null) {
        $this->transport = $transport;
        $this->logger = $logger ?: new Logger();
        $this->security = $security ?: new Security();

        // Set up server capabilities
        // According to MCP spec, capabilities indicate which features are supported
        // Tools are listed via tools/list, not in capabilities
        $this->capabilities = [
            'tools' => new \stdClass(), // Empty object indicates tools are supported
            'resources' => [
                'subscribe' => false,
                'listChanged' => false,
            ],
            'prompts' => [
                'listChanged' => false,
            ],
            'logging' => new \stdClass(), // Empty object, not array
        ];

        $this->logger->info('MCP Server initialized');
    }

    /**
     * Force initialize the server.
     *
     * Used to bypass the normal initialization flow when needed,
     * particularly in HTTP mode where initialization is implicit.
     *
     * @since 1.1.0
     */
    public function force_initialize() {
        $this->initialized = true;
        $this->logger->info('Server force initialized for HTTP mode');
    }

    /**
     * Register a tool
     *
     * @param string $name Tool name
     * @param object $tool Tool instance implementing execute() method
     * @return void
     */
    public function register_tool($name, $tool) {
        if (!method_exists($tool, 'execute')) {
            $this->logger->error("Tool {$name} must implement execute() method");
            return;
        }

        if (!method_exists($tool, 'get_schema')) {
            $this->logger->error("Tool {$name} must implement get_schema() method");
            return;
        }

        $this->tools[$name] = $tool;
        $this->logger->debug("Registered tool: {$name}");
    }

    /**
     * Register multiple tools at once
     *
     * @param array $tools Array of [name => tool_instance]
     * @return void
     */
    public function register_tools($tools) {
        foreach ($tools as $name => $tool) {
            $this->register_tool($name, $tool);
        }
    }

    /**
     * Get all registered tools
     *
     * @return array
     */
    public function get_tools() {
        return $this->tools;
    }

    /**
     * Main server run loop
     *
     * Continuously reads messages from transport and handles them
     * until the connection closes or server is stopped.
     *
     * @return void
     */
    public function run() {
        $this->running = true;
        $this->logger->info('MCP Server starting...');

        try {
            $this->transport->initialize();

            while ($this->running && $this->transport->is_connected()) {
                try {
                    $message = $this->transport->read_message();

                    if ($message === null) {
                        // EOF or connection closed
                        $this->logger->info('Connection closed by client');
                        break;
                    }

                    $response = $this->handle_message($message);

                    if ($response !== null) {
                        $this->transport->send_message($response);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error processing message: ' . $e->getMessage());

                    // Send error response if we can extract an ID
                    $error_response = $this->create_error_response(
                        isset($message['id']) ? $message['id'] : null,
                        self::ERROR_INTERNAL_ERROR,
                        'Internal server error: ' . $e->getMessage()
                    );

                    $this->transport->send_message($error_response);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Fatal server error: ' . $e->getMessage());
        } finally {
            $this->shutdown();
        }
    }

    /**
     * Stop the server
     *
     * @return void
     */
    public function stop() {
        $this->running = false;
        $this->logger->info('Server stop requested');
    }

    /**
     * Shutdown the server gracefully
     *
     * @return void
     */
    private function shutdown() {
        $this->logger->info('MCP Server shutting down...');
        $this->transport->shutdown();
        $this->running = false;
        $this->initialized = false;
    }

    /**
     * Handle incoming message
     *
     * Validates JSON-RPC 2.0 format and routes to appropriate handler.
     *
     * @param array $message Decoded message
     * @return array|null Response message or null for notifications
     */
    private function handle_message($message) {
        // Validate JSON-RPC 2.0 format
        if (!isset($message['jsonrpc']) || $message['jsonrpc'] !== '2.0') {
            return $this->create_error_response(
                $message['id'] ?? null,
                self::ERROR_INVALID_REQUEST,
                'Invalid JSON-RPC version'
            );
        }

        if (!isset($message['method'])) {
            return $this->create_error_response(
                $message['id'] ?? null,
                self::ERROR_INVALID_REQUEST,
                'Missing method field'
            );
        }

        $method = $message['method'];
        $params = $message['params'] ?? [];
        $id = $message['id'] ?? null;

        // Notifications don't have an ID and don't expect a response
        $is_notification = ($id === null);

        $this->logger->debug("Handling method: {$method}" . ($is_notification ? ' (notification)' : ''));

        try {
            // Route to appropriate handler
            $result = $this->route_method($method, $params);

            // Don't send response for notifications
            if ($is_notification) {
                return null;
            }

            return $this->create_success_response($id, $result);
        } catch (\Exception $e) {
            // Don't send error response for notifications
            if ($is_notification) {
                $this->logger->error("Error in notification {$method}: " . $e->getMessage());
                return null;
            }

            $code = $e->getCode() ?: self::ERROR_INTERNAL_ERROR;
            return $this->create_error_response($id, $code, $e->getMessage());
        }
    }

    /**
     * Route method to appropriate handler
     *
     * @param string $method Method name
     * @param array $params Method parameters
     * @return mixed Method result
     * @throws \Exception If method not found or execution fails
     */
    private function route_method($method, $params) {
        switch ($method) {
            case 'initialize':
                return $this->handle_initialize($params);

            case 'initialized':
                return $this->handle_initialized($params);

            case 'tools/list':
                return $this->handle_tools_list($params);

            case 'tools/call':
                return $this->handle_tools_call($params);

            case 'resources/list':
                return $this->handle_resources_list($params);

            case 'resources/read':
                return $this->handle_resources_read($params);

            case 'prompts/list':
                return $this->handle_prompts_list($params);

            case 'prompts/get':
                return $this->handle_prompts_get($params);

            case 'ping':
                return $this->handle_ping($params);

            default:
                throw new \Exception("Method not found: {$method}", self::ERROR_METHOD_NOT_FOUND);
        }
    }

    /**
     * Handle initialize request
     *
     * @param array $params Initialize parameters
     * @return array Server information and capabilities
     * @throws \Exception If already initialized
     */
    private function handle_initialize($params) {
        if ($this->initialized) {
            throw new \Exception('Server already initialized', self::ERROR_INVALID_REQUEST);
        }

        // Store client information
        $this->client_info = [
            'name' => $params['clientInfo']['name'] ?? 'unknown',
            'version' => $params['clientInfo']['version'] ?? 'unknown',
        ];

        $protocol_version = $params['protocolVersion'] ?? null;

        if ($protocol_version && $protocol_version !== self::PROTOCOL_VERSION) {
            $this->logger->warning("Protocol version mismatch. Client: {$protocol_version}, Server: " . self::PROTOCOL_VERSION);
        }

        $this->logger->info("Client connected: {$this->client_info['name']} v{$this->client_info['version']}");

        $response = [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'serverInfo' => [
                'name' => self::SERVER_NAME,
                'version' => self::SERVER_VERSION,
            ],
            'capabilities' => $this->capabilities,
        ];

        // Add experimental capabilities for stateless transports (HTTP)
        if ($this->transport->get_type() === 'http') {
            $response['experimental_capabilities'] = [
                'stateless' => true
            ];
        }

        return $response;
    }

    /**
     * Handle initialized notification
     *
     * Sent by client after receiving initialize response.
     *
     * @param array $params Parameters (unused)
     * @return void
     */
    private function handle_initialized($params) {
        $this->initialized = true;
        $this->logger->info('Server initialized successfully');
    }

    /**
     * Handle tools/list request
     *
     * Returns list of all available tools.
     *
     * @param array $params Parameters (unused)
     * @return array Tools list
     * @throws \Exception If server not initialized
     */
    private function handle_tools_list($params) {
        $this->ensure_initialized();

        $tools = [];

        foreach ($this->tools as $name => $tool) {
            $schema = $tool->get_schema();

            $tools[] = [
                'name' => $name,
                'description' => $schema['description'] ?? '',
                'inputSchema' => $schema['inputSchema'] ?? [
                    'type' => 'object',
                    'properties' => [],
                ],
            ];
        }

        return ['tools' => $tools];
    }

    /**
     * Handle tools/call request
     *
     * Executes a tool with given arguments.
     *
     * @param array $params Tool call parameters
     * @return array Tool execution result
     * @throws \Exception If tool not found or execution fails
     */
    private function handle_tools_call($params) {
        $this->ensure_initialized();

        if (!isset($params['name'])) {
            throw new \Exception('Missing tool name', self::ERROR_INVALID_PARAMS);
        }

        $tool_name = $params['name'];
        $arguments = $params['arguments'] ?? [];

        if (!isset($this->tools[$tool_name])) {
            throw new \Exception("Tool not found: {$tool_name}", self::ERROR_TOOL_NOT_FOUND);
        }

        // Security check
        if (!$this->security->can_execute_tool($tool_name, $arguments)) {
            throw new \Exception('Unauthorized to execute this tool', self::ERROR_UNAUTHORIZED);
        }

        // Rate limiting check
        if (!$this->security->check_rate_limit($tool_name)) {
            throw new \Exception('Rate limit exceeded for this tool', self::ERROR_UNAUTHORIZED);
        }

        $this->logger->info("Executing tool: {$tool_name}");

        try {
            $tool = $this->tools[$tool_name];
            $result = $tool->execute($arguments);

            // Format result as MCP content
            $content = $this->format_tool_result($result);

            return [
                'content' => $content,
                'isError' => false,
            ];
        } catch (\Exception $e) {
            $this->logger->error("Tool execution failed: {$tool_name} - " . $e->getMessage());

            throw new \Exception(
                "Tool execution failed: " . $e->getMessage(),
                self::ERROR_TOOL_EXECUTION_FAILED
            );
        }
    }

    /**
     * Handle resources/list request
     *
     * Returns list of available resources (optional feature).
     *
     * @param array $params Parameters
     * @return array Resources list
     */
    private function handle_resources_list($params) {
        $this->ensure_initialized();

        // Resources not implemented yet
        return ['resources' => []];
    }

    /**
     * Handle resources/read request
     *
     * Reads a specific resource (optional feature).
     *
     * @param array $params Parameters
     * @return array Resource content
     * @throws \Exception If resource not found
     */
    private function handle_resources_read($params) {
        $this->ensure_initialized();

        throw new \Exception('Resources not implemented', self::ERROR_RESOURCE_NOT_FOUND);
    }

    /**
     * Handle prompts/list request
     *
     * Returns list of available prompts (optional feature).
     *
     * @param array $params Parameters
     * @return array Prompts list
     */
    private function handle_prompts_list($params) {
        $this->ensure_initialized();

        // Prompts not implemented yet
        return ['prompts' => []];
    }

    /**
     * Handle prompts/get request
     *
     * Gets a specific prompt (optional feature).
     *
     * @param array $params Parameters
     * @return array Prompt content
     * @throws \Exception If prompt not found
     */
    private function handle_prompts_get($params) {
        $this->ensure_initialized();

        throw new \Exception('Prompts not implemented', self::ERROR_METHOD_NOT_FOUND);
    }

    /**
     * Handle ping request
     *
     * Simple health check.
     *
     * @param array $params Parameters (unused)
     * @return array Pong response
     */
    private function handle_ping($params) {
        return ['status' => 'pong'];
    }

    /**
     * Format tool result as MCP content
     *
     * Converts various result formats to MCP content array.
     *
     * @param mixed $result Tool execution result
     * @return array MCP content array
     */
    private function format_tool_result($result) {
        // If result is already in MCP format
        if (is_array($result) && isset($result['type'])) {
            return [$result];
        }

        // If result is array of MCP content items
        if (is_array($result) && isset($result[0]['type'])) {
            return $result;
        }

        // Convert to text content
        $text = is_string($result) ? $result : wp_json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return [
            [
                'type' => 'text',
                'text' => $text,
            ],
        ];
    }

    /**
     * Create a JSON-RPC 2.0 success response
     *
     * @param mixed $id Request ID
     * @param mixed $result Result data
     * @return array Response message
     */
    private function create_success_response($id, $result) {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ];
    }

    /**
     * Create a JSON-RPC 2.0 error response
     *
     * @param mixed $id Request ID
     * @param int $code Error code
     * @param string $message Error message
     * @param mixed $data Optional additional error data
     * @return array Error response message
     */
    private function create_error_response($id, $code, $message, $data = null) {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($data !== null) {
            $error['data'] = $data;
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => $error,
        ];
    }

    /**
     * Ensure server is initialized
     *
     * For STDIO, some clients (e.g. Cursor) may not send the "initialized"
     * notification after "initialize". We treat a successful initialize
     * (client_info is set) as sufficient for tools/list, resources/list, etc.
     *
     * @throws \Exception If not initialized
     */
    private function ensure_initialized() {
        if ($this->initialized) {
            return;
        }
        // STDIO fallback: if we have client_info, we've completed initialize handshake
        if ($this->client_info !== null) {
            $this->initialized = true;
            $this->logger->info('Treating client_info as initialized (client may not have sent "initialized" notification)');
            return;
        }
        throw new \Exception('Server not initialized', self::ERROR_INVALID_REQUEST);
    }

    /**
     * Get logger instance
     *
     * @return Logger
     */
    public function get_logger() {
        return $this->logger;
    }

    /**
     * Get security instance
     *
     * @return Security
     */
    public function get_security() {
        return $this->security;
    }

    /**
     * Get server initialization state
     *
     * @return bool
     */
    public function is_initialized() {
        return $this->initialized;
    }

    /**
     * Get client information
     *
     * @return array|null
     */
    public function get_client_info() {
        return $this->client_info;
    }
}
