<?php
/**
 * RAG Client for Smart RAG Service integration.
 *
 * Provides methods to interact with the Smart RAG service
 * for context retrieval and page indexing.
 *
 * @package Elementor_MCP
 */

namespace Elementor_MCP\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class RAG_Client
 *
 * HTTP client for communicating with the Smart RAG service.
 */
class RAG_Client {

    /**
     * RAG service base URL.
     *
     * @var string
     */
    private $base_url;

    /**
     * API key for authentication.
     *
     * @var string
     */
    private $api_key;

    /**
     * Webhook secret for signature generation.
     *
     * @var string
     */
    private $webhook_secret;

    /**
     * Request timeout in seconds.
     *
     * @var int
     */
    private $timeout = 30;

    /**
     * Singleton instance.
     *
     * @var RAG_Client|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return RAG_Client
     */
    public static function get_instance(): RAG_Client {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->base_url       = $this->get_option( 'rag_service_url', 'http://localhost:8080' );
        $this->api_key        = $this->get_option( 'rag_api_key', '' );
        $this->webhook_secret = $this->get_option( 'rag_webhook_secret', '' );
    }

    /**
     * Get option value.
     *
     * @param string $key     Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    private function get_option( string $key, $default = '' ) {
        return get_option( 'elementor_mcp_' . $key, $default );
    }

    /**
     * Make HTTP request to RAG service.
     *
     * @param string $method   HTTP method (GET, POST, DELETE).
     * @param string $endpoint API endpoint.
     * @param array  $data     Request data.
     * @return array|WP_Error
     */
    private function request( string $method, string $endpoint, array $data = [] ) {
        $url = rtrim( $this->base_url, '/' ) . '/api/v1' . $endpoint;

        $args = [
            'method'  => $method,
            'timeout' => $this->timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];

        // Add API key if configured
        if ( ! empty( $this->api_key ) ) {
            $args['headers']['X-API-Key'] = $this->api_key;
        }

        // Add body for POST/PUT requests
        if ( in_array( $method, [ 'POST', 'PUT', 'PATCH' ], true ) && ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        // Add query params for GET requests
        if ( 'GET' === $method && ! empty( $data ) ) {
            $url = add_query_arg( $data, $url );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        $decoded     = json_decode( $body, true );

        if ( $status_code >= 400 ) {
            $error_message = isset( $decoded['detail'] ) ? $decoded['detail'] : "HTTP Error: {$status_code}";
            return new \WP_Error( 'rag_api_error', $error_message, [ 'status' => $status_code ] );
        }

        return $decoded;
    }

    /**
     * Check if RAG service is available.
     *
     * @return bool
     */
    public function is_available(): bool {
        $result = $this->request( 'GET', '/health' );
        return ! is_wp_error( $result ) && isset( $result['status'] ) && 'healthy' === $result['status'];
    }

    /**
     * Get service health status.
     *
     * @return array|WP_Error
     */
    public function get_health() {
        return $this->request( 'GET', '/health' );
    }

    /**
     * Perform smart search across all collections.
     *
     * @param string      $query           Search query.
     * @param string|null $industry        Industry hint.
     * @param array|null  $section_types   Section type filters.
     * @param int|null    $max_results     Maximum results.
     * @param bool        $include_user_pages Include user pages in results.
     * @return array|WP_Error
     */
    public function search(
        string $query,
        ?string $industry = null,
        ?array $section_types = null,
        ?int $max_results = null,
        bool $include_user_pages = true
    ) {
        $data = [
            'query'              => $query,
            'include_user_pages' => $include_user_pages,
        ];

        if ( $industry ) {
            $data['industry'] = $industry;
        }

        if ( $section_types ) {
            $data['section_types'] = $section_types;
        }

        if ( $max_results ) {
            $data['max_results'] = $max_results;
        }

        return $this->request( 'POST', '/search', $data );
    }

    /**
     * Search for sections.
     *
     * @param string      $query        Search query.
     * @param string|null $section_type Section type filter.
     * @param string|null $industry     Industry filter.
     * @param int         $limit        Max results.
     * @return array|WP_Error
     */
    public function search_sections(
        string $query,
        ?string $section_type = null,
        ?string $industry = null,
        int $limit = 10
    ) {
        $data = [
            'query' => $query,
            'limit' => $limit,
        ];

        if ( $section_type ) {
            $data['section_type'] = $section_type;
        }

        if ( $industry ) {
            $data['industry'] = $industry;
        }

        return $this->request( 'POST', '/search/sections', $data );
    }

    /**
     * Search for copywriting examples.
     *
     * @param string      $query        Search query.
     * @param string|null $content_type Content type (headline, body, cta).
     * @param string|null $section_type Section type filter.
     * @param int         $limit        Max results.
     * @return array|WP_Error
     */
    public function search_copywriting(
        string $query,
        ?string $content_type = null,
        ?string $section_type = null,
        int $limit = 10
    ) {
        $data = [
            'query' => $query,
            'limit' => $limit,
        ];

        if ( $content_type ) {
            $data['content_type'] = $content_type;
        }

        if ( $section_type ) {
            $data['section_type'] = $section_type;
        }

        return $this->request( 'POST', '/search/copywriting', $data );
    }

    /**
     * Get AI-ready context for page/section generation.
     *
     * @param string      $query             Description of what to create.
     * @param string|null $industry          Industry hint.
     * @param array|null  $section_types     Section type filters.
     * @param bool        $include_raw_json  Include Elementor JSON.
     * @param string      $format            Response format (json or prompt).
     * @return array|WP_Error
     */
    public function get_context(
        string $query,
        ?string $industry = null,
        ?array $section_types = null,
        bool $include_raw_json = false,
        string $format = 'json'
    ) {
        $data = [
            'query'            => $query,
            'include_raw_json' => $include_raw_json,
            'format'           => $format,
        ];

        if ( $industry ) {
            $data['industry'] = $industry;
        }

        if ( $section_types ) {
            $data['section_types'] = $section_types;
        }

        return $this->request( 'POST', '/context', $data );
    }

    /**
     * Get context for specific section type.
     *
     * @param string      $section_type Section type (hero, pricing, etc.).
     * @param string      $query        Additional context.
     * @param string|null $industry     Industry hint.
     * @return array|WP_Error
     */
    public function get_section_context(
        string $section_type,
        string $query = '',
        ?string $industry = null
    ) {
        $data = [
            'section_type'     => $section_type,
            'query'            => $query,
            'include_raw_json' => true,
        ];

        if ( $industry ) {
            $data['industry'] = $industry;
        }

        return $this->request( 'POST', '/context/section', $data );
    }

    /**
     * Get context for full page generation.
     *
     * @param string      $query     Description of page.
     * @param string|null $industry  Industry hint.
     * @param string|null $page_type Page type.
     * @return array|WP_Error
     */
    public function get_page_context(
        string $query,
        ?string $industry = null,
        ?string $page_type = null
    ) {
        $data = [ 'query' => $query ];

        if ( $industry ) {
            $data['industry'] = $industry;
        }

        if ( $page_type ) {
            $data['page_type'] = $page_type;
        }

        return $this->request( 'POST', '/context/page', $data );
    }

    /**
     * Get design patterns for an industry.
     *
     * @param string $industry Industry name.
     * @return array|WP_Error
     */
    public function get_design_patterns( string $industry ) {
        return $this->request( 'GET', "/context/patterns/{$industry}" );
    }

    /**
     * Index a page in the RAG database.
     *
     * @param int    $post_id       WordPress post ID.
     * @param string $post_title    Post title.
     * @param array  $elements_data Elementor elements data.
     * @param string $post_type     Post type.
     * @return array|WP_Error
     */
    public function index_page(
        int $post_id,
        string $post_title,
        array $elements_data,
        string $post_type = 'page'
    ) {
        $data = [
            'post_id'       => $post_id,
            'post_title'    => $post_title,
            'elements_data' => $elements_data,
            'post_type'     => $post_type,
            'post_url'      => get_permalink( $post_id ),
        ];

        return $this->request( 'POST', '/webhook/page/index', $data );
    }

    /**
     * Delete a page from the RAG index.
     *
     * @param int $post_id WordPress post ID.
     * @return array|WP_Error
     */
    public function delete_page( int $post_id ) {
        return $this->request( 'DELETE', "/webhook/page/{$post_id}" );
    }

    /**
     * Send webhook event to RAG service.
     *
     * @param string      $event         Event type.
     * @param int         $post_id       Post ID.
     * @param string|null $post_title    Post title.
     * @param array|null  $elements_data Elementor data.
     * @return array|WP_Error
     */
    public function send_webhook(
        string $event,
        int $post_id,
        ?string $post_title = null,
        ?array $elements_data = null
    ) {
        $data = [
            'event'     => $event,
            'post_id'   => $post_id,
            'timestamp' => current_time( 'c' ),
        ];

        if ( $post_title ) {
            $data['post_title'] = $post_title;
        }

        if ( $elements_data ) {
            $data['elements_data'] = $elements_data;
        }

        $url  = rtrim( $this->base_url, '/' ) . '/api/v1/webhook/page';
        $body = wp_json_encode( $data );

        $args = [
            'method'  => 'POST',
            'timeout' => $this->timeout,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'    => $body,
        ];

        // Add API key
        if ( ! empty( $this->api_key ) ) {
            $args['headers']['X-API-Key'] = $this->api_key;
        }

        // Add webhook signature
        if ( ! empty( $this->webhook_secret ) ) {
            $signature                             = 'sha256=' . hash_hmac( 'sha256', $body, $this->webhook_secret );
            $args['headers']['X-Webhook-Signature'] = $signature;
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );

        if ( $status_code >= 400 ) {
            return new \WP_Error( 'webhook_error', "Webhook failed: {$status_code}" );
        }

        return json_decode( $body, true );
    }

    /**
     * Get list of indexed pages.
     *
     * @return array|WP_Error
     */
    public function get_indexed_pages() {
        return $this->request( 'GET', '/webhook/pages' );
    }

    /**
     * Index a kit from training data.
     *
     * @param string $kit_name Kit name.
     * @return array|WP_Error
     */
    public function index_kit( string $kit_name ) {
        return $this->request( 'POST', '/index', [ 'kit_name' => $kit_name ] );
    }

    /**
     * Index all training kits.
     *
     * @return array|WP_Error
     */
    public function index_all_kits() {
        return $this->request( 'POST', '/index/all' );
    }

    /**
     * Get indexing status.
     *
     * @return array|WP_Error
     */
    public function get_index_status() {
        return $this->request( 'GET', '/index/status' );
    }
}
