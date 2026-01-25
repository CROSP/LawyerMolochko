<?php
/**
 * Create Elementor Page Tool
 *
 * Creates a new WordPress post/page with Elementor enabled.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP\Tools\Documents;

use ElementorMCP\Tools\Base_Tool;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create Elementor Page Tool Class
 *
 * Creates a new WordPress post or page with Elementor builder enabled.
 * Sets the _elementor_edit_mode meta to 'builder' to enable Elementor editing.
 *
 * @since 1.0.0
 */
class Create_Elementor_Page extends Base_Tool {

	/**
	 * Get tool name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool name.
	 */
	public function get_name() {
		return 'create_elementor_page';
	}

	/**
	 * Get tool description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tool description.
	 */
	public function get_description() {
		return 'Creates a new WordPress post or page with Elementor builder enabled. Returns the new post ID.';
	}

	/**
	 * Get input schema.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JSON schema for input validation.
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'title'        => array(
					'type'        => 'string',
					'description' => 'The title for the new post/page',
					'minLength'   => 1,
				),
				'content_type' => array(
					'type'        => 'string',
					'description' => 'Type of content to create (page or post)',
					'enum'        => array( 'page', 'post' ),
					'default'     => 'page',
				),
				'status'       => array(
					'type'        => 'string',
					'description' => 'Post status (draft or publish)',
					'enum'        => array( 'draft', 'publish' ),
					'default'     => 'draft',
				),
			),
			'required'   => array( 'title' ),
		);
	}

	/**
	 * Execute the tool.
	 *
	 * Creates a new post/page with Elementor enabled.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Input arguments containing title, content_type, and status.
	 * @return array Tool execution result.
	 */
	public function execute( $args ) {
		// Verify Elementor is active
		if ( ! $this->verify_elementor_active() ) {
			return $this->format_error( 'Elementor is not active', 'elementor_not_active' );
		}

		$title = sanitize_text_field( $args['title'] );
		$content_type = isset( $args['content_type'] ) ? $args['content_type'] : 'page';
		$status = isset( $args['status'] ) ? $args['status'] : 'draft';

		// Validate content type
		if ( ! in_array( $content_type, array( 'page', 'post' ), true ) ) {
			return $this->format_error(
				'Invalid content_type. Must be "page" or "post"',
				'invalid_content_type'
			);
		}

		// Validate status
		if ( ! in_array( $status, array( 'draft', 'publish' ), true ) ) {
			return $this->format_error(
				'Invalid status. Must be "draft" or "publish"',
				'invalid_status'
			);
		}

		// Get the post type object to check capabilities
		$post_type_object = get_post_type_object( $content_type );
		if ( ! $post_type_object ) {
			return $this->format_error(
				sprintf( 'Post type "%s" does not exist', $content_type ),
				'invalid_post_type'
			);
		}

		// Check user permissions to create posts of this type
		if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return $this->format_error(
				sprintf( 'You do not have permission to create %s', $content_type ),
				'permission_denied'
			);
		}

		// If publishing, check publish capability
		if ( 'publish' === $status && ! current_user_can( $post_type_object->cap->publish_posts ) ) {
			return $this->format_error(
				sprintf( 'You do not have permission to publish %s', $content_type ),
				'permission_denied'
			);
		}

		try {
			// Create the post
			$post_data = array(
				'post_title'   => $title,
				'post_type'    => $content_type,
				'post_status'  => $status,
				'post_content' => '', // Empty content, will use Elementor
			);

			$post_id = wp_insert_post( $post_data, true );

			if ( is_wp_error( $post_id ) ) {
				return $this->format_error(
					sprintf( 'Failed to create post: %s', $post_id->get_error_message() ),
					'post_creation_failed',
					array( 'wp_error' => $post_id->get_error_code() )
				);
			}

			// Enable Elementor on this post
			update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );

			// Set Elementor version
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );

			// Initialize empty Elementor data
			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array() ) ) );

			// Get the created post
			$post = get_post( $post_id );

			// Get document if Elementor is loaded
			$document = null;
			$edit_url = admin_url( sprintf( 'post.php?post=%d&action=elementor', $post_id ) );
			$preview_url = get_permalink( $post_id );

			if ( did_action( 'elementor/loaded' ) ) {
				$document = \Elementor\Plugin::$instance->documents->get( $post_id );
				if ( $document ) {
					$edit_url = $document->get_edit_url();
					$preview_url = $document->get_preview_url();
				}
			}

			$result = array(
				'post_id'      => $post_id,
				'title'        => $post->post_title,
				'post_type'    => $post->post_type,
				'post_status'  => $post->post_status,
				'edit_url'     => $edit_url,
				'preview_url'  => $preview_url,
				'permalink'    => get_permalink( $post_id ),
				'elementor_enabled' => true,
			);

			return $this->format_success(
				$result,
				sprintf( 'Elementor %s created successfully with ID %d', $content_type, $post_id )
			);

		} catch ( \Exception $e ) {
			return $this->format_error(
				sprintf( 'Error creating post: %s', $e->getMessage() ),
				'creation_error',
				array( 'exception' => get_class( $e ) )
			);
		}
	}
}
