<?php
/**
 * RAG Hooks - WordPress hooks for auto-indexing pages.
 *
 * Automatically indexes Elementor pages when they are saved
 * and removes them when deleted.
 *
 * @package Elementor_MCP
 */

namespace Elementor_MCP\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class RAG_Hooks
 *
 * Registers WordPress hooks for automatic page indexing.
 */
class RAG_Hooks {

    /**
     * RAG client instance.
     *
     * @var RAG_Client
     */
    private $client;

    /**
     * Whether auto-indexing is enabled.
     *
     * @var bool
     */
    private $auto_index_enabled;

    /**
     * Singleton instance.
     *
     * @var RAG_Hooks|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return RAG_Hooks
     */
    public static function get_instance(): RAG_Hooks {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->client             = RAG_Client::get_instance();
        $this->auto_index_enabled = get_option( 'elementor_mcp_rag_auto_index', true );

        if ( $this->auto_index_enabled ) {
            $this->register_hooks();
        }
    }

    /**
     * Register WordPress hooks.
     */
    private function register_hooks() {
        // Hook into Elementor save
        add_action( 'elementor/document/after_save', [ $this, 'on_elementor_save' ], 10, 2 );

        // Hook into post deletion
        add_action( 'before_delete_post', [ $this, 'on_post_delete' ] );

        // Hook into post status changes
        add_action( 'transition_post_status', [ $this, 'on_status_change' ], 10, 3 );

        // Hook into bulk indexing action
        add_action( 'wp_ajax_elementor_mcp_bulk_index', [ $this, 'ajax_bulk_index' ] );
    }

    /**
     * Handle Elementor document save.
     *
     * @param \Elementor\Core\Base\Document $document Elementor document.
     * @param array                         $data     Save data.
     */
    public function on_elementor_save( $document, $data ) {
        $post_id = $document->get_post()->ID;
        $post    = get_post( $post_id );

        // Only index published posts/pages
        if ( 'publish' !== $post->post_status ) {
            return;
        }

        // Get Elementor data
        $elements_data = get_post_meta( $post_id, '_elementor_data', true );

        if ( empty( $elements_data ) ) {
            return;
        }

        // Decode if string
        if ( is_string( $elements_data ) ) {
            $elements_data = json_decode( $elements_data, true );
        }

        if ( empty( $elements_data ) || ! is_array( $elements_data ) ) {
            return;
        }

        // Send to RAG service (async via wp_schedule_single_event for non-blocking)
        $this->schedule_index( $post_id, $post->post_title, $elements_data, $post->post_type );
    }

    /**
     * Handle post deletion.
     *
     * @param int $post_id Post ID being deleted.
     */
    public function on_post_delete( $post_id ) {
        // Check if this post has Elementor data
        $elementor_data = get_post_meta( $post_id, '_elementor_data', true );

        if ( empty( $elementor_data ) ) {
            return;
        }

        // Delete from RAG index
        $this->client->delete_page( $post_id );
    }

    /**
     * Handle post status transitions.
     *
     * @param string   $new_status New status.
     * @param string   $old_status Old status.
     * @param \WP_Post $post       Post object.
     */
    public function on_status_change( $new_status, $old_status, $post ) {
        // Only handle Elementor posts
        $elementor_data = get_post_meta( $post->ID, '_elementor_data', true );

        if ( empty( $elementor_data ) ) {
            return;
        }

        // Decode if string
        if ( is_string( $elementor_data ) ) {
            $elementor_data = json_decode( $elementor_data, true );
        }

        if ( empty( $elementor_data ) || ! is_array( $elementor_data ) ) {
            return;
        }

        // Publishing - index the page
        if ( 'publish' === $new_status && 'publish' !== $old_status ) {
            $this->schedule_index( $post->ID, $post->post_title, $elementor_data, $post->post_type );
        }

        // Unpublishing - remove from index
        if ( 'publish' !== $new_status && 'publish' === $old_status ) {
            $this->client->delete_page( $post->ID );
        }
    }

    /**
     * Schedule page indexing (non-blocking).
     *
     * @param int    $post_id       Post ID.
     * @param string $post_title    Post title.
     * @param array  $elements_data Elementor data.
     * @param string $post_type     Post type.
     */
    private function schedule_index( int $post_id, string $post_title, array $elements_data, string $post_type ) {
        // For immediate indexing (blocking), uncomment:
        // $this->client->index_page( $post_id, $post_title, $elements_data, $post_type );

        // Non-blocking: schedule for immediate execution
        if ( ! wp_next_scheduled( 'elementor_mcp_index_page', [ $post_id ] ) ) {
            wp_schedule_single_event( time(), 'elementor_mcp_index_page', [ $post_id ] );
        }
    }

    /**
     * Index a page (called by scheduled event).
     *
     * @param int $post_id Post ID to index.
     */
    public function index_scheduled_page( int $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post || 'publish' !== $post->post_status ) {
            return;
        }

        $elements_data = get_post_meta( $post_id, '_elementor_data', true );

        if ( is_string( $elements_data ) ) {
            $elements_data = json_decode( $elements_data, true );
        }

        if ( empty( $elements_data ) ) {
            return;
        }

        $this->client->index_page( $post_id, $post->post_title, $elements_data, $post->post_type );
    }

    /**
     * AJAX handler for bulk indexing.
     */
    public function ajax_bulk_index() {
        check_ajax_referer( 'elementor_mcp_bulk_index', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $results = $this->bulk_index_all_pages();

        wp_send_json_success( $results );
    }

    /**
     * Bulk index all published Elementor pages.
     *
     * @return array Results summary.
     */
    public function bulk_index_all_pages(): array {
        $results = [
            'total'      => 0,
            'indexed'    => 0,
            'failed'     => 0,
            'skipped'    => 0,
            'errors'     => [],
        ];

        // Get all published posts/pages with Elementor data
        $query = new \WP_Query( [
            'post_type'      => [ 'page', 'post' ],
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_elementor_data',
                    'compare' => 'EXISTS',
                ],
            ],
        ] );

        $results['total'] = $query->found_posts;

        foreach ( $query->posts as $post ) {
            $elements_data = get_post_meta( $post->ID, '_elementor_data', true );

            if ( is_string( $elements_data ) ) {
                $elements_data = json_decode( $elements_data, true );
            }

            if ( empty( $elements_data ) ) {
                $results['skipped']++;
                continue;
            }

            $response = $this->client->index_page(
                $post->ID,
                $post->post_title,
                $elements_data,
                $post->post_type
            );

            if ( is_wp_error( $response ) ) {
                $results['failed']++;
                $results['errors'][] = [
                    'post_id' => $post->ID,
                    'error'   => $response->get_error_message(),
                ];
            } else {
                $results['indexed']++;
            }
        }

        return $results;
    }

    /**
     * Initialize the scheduled event hook.
     */
    public static function init_scheduled_hooks() {
        add_action( 'elementor_mcp_index_page', [ self::get_instance(), 'index_scheduled_page' ] );
    }
}

// Initialize scheduled hooks
RAG_Hooks::init_scheduled_hooks();
