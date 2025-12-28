#!/usr/bin/env php
<?php
/**
 * Elementor MCP Server
 * Model Context Protocol server for Elementor integration
 */

// Prevent direct access from web
if (php_sapi_name() !== 'cli') {
    exit("This script can only be run from CLI\n");
}

// Parse command line arguments
$options = getopt('', ['site-url:']);
$site_url = $options['site-url'] ?? 'http://localhost';

// Set up WordPress environment BEFORE loading WordPress
$_SERVER['HTTP_HOST'] = parse_url($site_url, PHP_URL_HOST);
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTPS'] = parse_url($site_url, PHP_URL_SCHEME) === 'https' ? 'on' : 'off';
$_SERVER['SERVER_PORT'] = parse_url($site_url, PHP_URL_PORT) ?: (parse_url($site_url, PHP_URL_SCHEME) === 'https' ? 443 : 80);

// Load WordPress
$wp_load_path = __DIR__ . '/../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    fwrite(STDERR, "Error: Could not find WordPress load file\n");
    exit(1);
}

// Parse command line arguments
$options = getopt('', ['site-url:']);
$site_url = $options['site-url'] ?? 'http://localhost';

// Set up WordPress environment
$_SERVER['HTTP_HOST'] = parse_url($site_url, PHP_URL_HOST);
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTPS'] = parse_url($site_url, PHP_URL_SCHEME) === 'https' ? 'on' : 'off';
$_SERVER['SERVER_PORT'] = parse_url($site_url, PHP_URL_PORT) ?: (parse_url($site_url, PHP_URL_SCHEME) === 'https' ? 443 : 80);

// Initialize MCP Server
class ElementorMCPServer {
    private $site_url;
    private $auth_token;
    
    public function __construct($site_url) {
        $this->site_url = $site_url;
        $this->auth_token = wp_generate_password(32, false);
    }
    
    public function start() {
        fwrite(STDOUT, json_encode([
            'jsonrpc' => '2.0',
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [
                        'listChanged' => false
                    ]
                ],
                'serverInfo' => [
                    'name' => 'Elementor MCP Server',
                    'version' => '1.0.0'
                ]
            ]
        ]) . "\n");
        
        $this->handleMessages();
    }
    
    private function handleMessages() {
        while ($line = fgets(STDIN)) {
            $message = json_decode($line, true);
            
            if (!$message) {
                continue;
            }
            
            $response = $this->handleRequest($message);
            
            if ($response) {
                fwrite(STDOUT, json_encode($response) . "\n");
            }
        }
    }
    
    private function handleRequest($message) {
        $method = $message['method'] ?? '';
        $id = $message['id'] ?? null;
        
        switch ($method) {
            case 'tools/list':
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'tools' => [
                            [
                                'name' => 'get_elementor_pages',
                                'description' => 'Get all pages built with Elementor',
                                'inputSchema' => [
                                    'type' => 'object',
                                    'properties' => []
                                ]
                            ],
                            [
                                'name' => 'get_page_elements',
                                'description' => 'Get all elements from a specific Elementor page',
                                'inputSchema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'page_id' => [
                                            'type' => 'integer',
                                            'description' => 'The ID of the page'
                                        ]
                                    ],
                                    'required' => ['page_id']
                                ]
                            ],
                            [
                                'name' => 'update_element',
                                'description' => 'Update an Elementor element',
                                'inputSchema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'page_id' => ['type' => 'integer'],
                                        'element_id' => ['type' => 'string'],
                                        'settings' => ['type' => 'object']
                                    ],
                                    'required' => ['page_id', 'element_id', 'settings']
                                ]
                            ]
                        ]
                    ]
                ];
                
            case 'tools/call':
                return $this->handleToolCall($message['params'], $id);
                
            default:
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'error' => [
                        'code' => -32601,
                        'message' => 'Method not found'
                    ]
                ];
        }
    }
    
    private function handleToolCall($params, $id) {
        $tool = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        switch ($tool) {
            case 'get_elementor_pages':
                return $this->getElementorPages($id);
                
            case 'get_page_elements':
                return $this->getPageElements($arguments, $id);
                
            case 'update_element':
                return $this->updateElement($arguments, $id);
                
            default:
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'error' => [
                        'code' => -32601,
                        'message' => 'Tool not found'
                    ]
                ];
        }
    }
    
    private function getElementorPages($id) {
        $pages = get_posts([
            'post_type' => ['page', 'post'],
            'meta_query' => [
                [
                    'key' => '_elementor_edit_mode',
                    'compare' => 'EXISTS'
                ]
            ],
            'posts_per_page' => -1
        ]);
        
        $page_list = [];
        foreach ($pages as $page) {
            $page_list[] = [
                'id' => $page->ID,
                'title' => $page->post_title,
                'type' => $page->post_type,
                'status' => $page->post_status,
                'url' => get_permalink($page->ID)
            ];
        }
        
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($page_list, JSON_PRETTY_PRINT)
                    ]
                ]
            ]
        ];
    }
    
    private function getPageElements($arguments, $id) {
        $page_id = $arguments['page_id'] ?? 0;
        
        if (!$page_id) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => 'Invalid page ID'
                ]
            ];
        }
        
        $document = Elementor\Plugin::$instance->documents->get($page_id);
        
        if (!$document) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => 'Document not found'
                ]
            ];
        }
        
        $elements = $document->get_elements_data();
        $formatted = $this->formatElements($elements);
        
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($formatted, JSON_PRETTY_PRINT)
                    ]
                ]
            ]
        ];
    }
    
    private function updateElement($arguments, $id) {
        $page_id = $arguments['page_id'] ?? 0;
        $element_id = $arguments['element_id'] ?? '';
        $settings = $arguments['settings'] ?? [];
        
        if (!$page_id || !$element_id) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => 'Missing required parameters'
                ]
            ];
        }
        
        $document = Elementor\Plugin::$instance->documents->get($page_id);
        
        if (!$document) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => 'Document not found'
                ]
            ];
        }
        
        // Find and update the element
        $elements = $document->get_elements_data();
        $updated = $this->findAndUpdateElement($elements, $element_id, $settings);
        
        if ($updated) {
            // Save the document
            $document->save(['elements' => $elements]);
            
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Element {$element_id} updated successfully"
                        ]
                    ]
                ]
            ];
        } else {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32602,
                    'message' => 'Element not found'
                ]
            ];
        }
    }
    
    private function formatElements($elements) {
        $formatted = [];
        
        foreach ($elements as $element) {
            $item = [
                'id' => $element['id'],
                'type' => $element['elType'],
                'widgetType' => isset($element['widgetType']) ? $element['widgetType'] : '',
                'settings' => isset($element['settings']) ? $element['settings'] : [],
            ];
            
            if (isset($element['elements']) && !empty($element['elements'])) {
                $item['children'] = $this->formatElements($element['elements']);
            }
            
            $formatted[] = $item;
        }
        
        return $formatted;
    }
    
    private function findAndUpdateElement(&$elements, $target_id, $settings) {
        foreach ($elements as &$element) {
            if ($element['id'] === $target_id) {
                $element['settings'] = array_merge($element['settings'], $settings);
                return true;
            }
            
            if (isset($element['elements']) && !empty($element['elements'])) {
                if ($this->findAndUpdateElement($element['elements'], $target_id, $settings)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}

// Start the server
$server = new ElementorMCPServer($site_url);
$server->start();
