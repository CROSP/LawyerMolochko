<?php
/**
 * ACF (Advanced Custom Fields) tools for WordPress MCP.
 * Requires ACF or ACF Pro. Loaded only when get_field exists.
 *
 * @package Automattic\WordpressMcp\Tools
 */

declare( strict_types=1 );

namespace Automattic\WordpressMcp\Tools;

use Automattic\WordpressMcp\Core\RegisterMcpTool;

/**
 * McpAcfTools
 */
class McpAcfTools {

	/**
	 * Constructor. Registers tools only when ACF is active.
	 */
	public function __construct() {
		if ( ! function_exists( 'get_field' ) ) {
			return;
		}
		add_action( 'wordpress_mcp_init', array( $this, 'register_tools' ) );
	}

	/**
	 * Register ACF tools.
	 */
	public function register_tools(): void {
		new RegisterMcpTool(
			array(
				'name'                => 'acf_get_field',
				'description'         => 'Get a single ACF field value for a post, term, user, or options page. Use the field name (e.g. fancy_boxes, hero_slider_shortcode).',
				'type'                => 'read',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'post_id'   => array(
							'type'        => 'integer',
							'description' => 'Post, page or custom post type ID. Use "option" for options page.',
						),
						'field'     => array(
							'type'        => 'string',
							'description' => 'Field name or field key (e.g. fancy_boxes, hero_slider_shortcode).',
						),
						'format'    => array(
							'type'        => 'boolean',
							'description' => 'Whether to format the value (default true). Set false for raw DB value.',
						),
					),
					'required'   => array( 'post_id', 'field' ),
				),
				'callback'            => array( $this, 'get_field' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
				'annotations'         => array(
					'title'        => 'ACF: Get field',
					'readOnlyHint' => true,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'acf_update_field',
				'description'         => 'Update an ACF field value for a post, term, user, or options page. For repeater: value is array of row objects. For link: {url, title, target}. For image: attachment ID.',
				'type'                => 'update',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'post_id' => array(
							'type'        => 'integer',
							'description' => 'Post, page or custom post type ID. Use "option" for options page.',
						),
						'field'   => array(
							'type'        => 'string',
							'description' => 'Field name or key (e.g. fancy_boxes, hero_slider_shortcode).',
						),
						'value'      => array(
							'type'        => 'string',
							'description' => 'Simple value (text, number). E.g. [rev_slider alias="slider-1"]. Ignored if value_json is set.',
						),
						'value_json' => array(
							'type'        => 'string',
							'description' => 'JSON for complex values: repeater (array of row objects), link ({url,title,target}). Overrides value when set.',
						),
					),
					'required'   => array( 'post_id', 'field' ),
				),
				'callback'            => array( $this, 'update_field' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
				'annotations'         => array(
					'title'           => 'ACF: Update field',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'acf_get_field_groups',
				'description'         => 'List ACF field groups. Optionally filter by location (post_type, page_template, page, etc.).',
				'type'                => 'read',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'location' => array(
							'type'        => 'string',
							'description' => 'Optional. Filter by location, e.g. "page_type==front_page" or "post_type==page".',
						),
					),
					'required'   => array(),
				),
				'callback'            => array( $this, 'get_field_groups' ),
				'permission_callback' => '__return_true',
				'annotations'         => array(
					'title'        => 'ACF: List field groups',
					'readOnlyHint' => true,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'acf_get_fields',
				'description'         => 'Get fields of an ACF field group. Provide group key, group ID, or post_id to resolve group by location.',
				'type'                => 'read',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'group'   => array(
							'type'        => 'string',
							'description' => 'Field group key (e.g. group_molochko_front_page) or group ID.',
						),
						'post_id' => array(
							'type'        => 'integer',
							'description' => 'Optional. If group is empty, find the group that applies to this post.',
						),
					),
					'required'   => array(),
				),
				'callback'            => array( $this, 'get_fields' ),
				'permission_callback' => '__return_true',
				'annotations'         => array(
					'title'        => 'ACF: List fields in a group',
					'readOnlyHint' => true,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'acf_get_front_page_id',
				'description'         => 'Get the WordPress front page post ID (page_on_front option). Returns 0 if front page is not set to a static page.',
				'type'                => 'read',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(),
					'required'   => array(),
				),
				'callback'            => array( $this, 'get_front_page_id' ),
				'permission_callback' => '__return_true',
				'annotations'         => array(
					'title'        => 'ACF: Get front page ID',
					'readOnlyHint' => true,
				),
			)
		);

		new RegisterMcpTool(
			array(
				'name'                => 'acf_update_fields_batch',
				'description'         => 'Update multiple ACF fields for a post in one call. Pass post_id and fields_json: a JSON object of field names to values. For WYSIWYG use HTML string; for image use attachment ID; for link use {url, title, target}.',
				'type'                => 'update',
				'inputSchema'         => array(
					'type'       => 'object',
					'properties' => array(
						'post_id'     => array(
							'type'        => 'integer',
							'description' => 'Post, page or custom post type ID.',
						),
						'fields_json'  => array(
							'type'        => 'string',
							'description' => 'JSON object of field_name => value. E.g. {"about_subtitle":"ПРО бюро","about_title":"My Title"}.',
						),
					),
					'required'   => array( 'post_id', 'fields_json' ),
				),
				'callback'            => array( $this, 'update_fields_batch' ),
				'permission_callback' => array( $this, 'check_edit_permission' ),
				'annotations'         => array(
					'title'           => 'ACF: Update multiple fields',
					'readOnlyHint'    => false,
					'destructiveHint' => false,
					'idempotentHint'  => true,
				),
			)
		);
	}

	/**
	 * Check if the current user can edit posts (used for get/update field on post types).
	 *
	 * @return bool
	 */
	public function check_edit_permission(): bool {
		return current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Get ACF field value.
	 *
	 * @param array $args { post_id, field, format? }
	 * @return array
	 */
	public function get_field( array $args ): array {
		if ( ! function_exists( 'get_field' ) ) {
			return array( 'error' => 'ACF is not active.', 'code' => 'acf_inactive' );
		}
		$post_id = $args['post_id'] ?? 0;
		$field   = $args['field'] ?? '';
		$format  = isset( $args['format'] ) ? (bool) $args['format'] : true;

		if ( ! $field ) {
			return array( 'error' => 'Field name is required.', 'code' => 'missing_field' );
		}
		$value = get_field( $field, $post_id, $format );
		return array( 'value' => $value );
	}

	/**
	 * Update ACF field value.
	 *
	 * @param array $args { post_id, field, value }
	 * @return array
	 */
	public function update_field( array $args ): array {
		if ( ! function_exists( 'update_field' ) ) {
			return array( 'error' => 'ACF is not active.', 'code' => 'acf_inactive' );
		}
		$post_id = $args['post_id'] ?? 0;
		$field   = $args['field'] ?? '';

		if ( ! $field ) {
			return array( 'error' => 'Field name is required.', 'code' => 'missing_field' );
		}
		if ( ! empty( $args['value_json'] ) ) {
			$value = json_decode( $args['value_json'], true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return array( 'error' => 'Invalid value_json: ' . json_last_error_msg(), 'code' => 'invalid_json' );
			}
		} elseif ( array_key_exists( 'value', $args ) ) {
			$value = $args['value'];
		} else {
			return array( 'error' => 'Provide value or value_json.', 'code' => 'missing_value' );
		}
		$updated = update_field( $field, $value, $post_id );
		return array( 'success' => (bool) $updated );
	}

	/**
	 * Get ACF field groups.
	 *
	 * @param array $args { location? }
	 * @return array
	 */
	public function get_field_groups( array $args ): array {
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return array( 'error' => 'ACF is not active.', 'code' => 'acf_inactive' );
		}
		$location = $args['location'] ?? null;
		$groups   = acf_get_field_groups( $location ? array( $location ) : array() );
		$out      = array();
		foreach ( $groups as $g ) {
			$out[] = array(
				'key'   => $g['key'] ?? '',
				'title' => $g['title'] ?? '',
				'id'    => $g['ID'] ?? 0,
			);
		}
		return array( 'groups' => $out );
	}

	/**
	 * Get fields of a group.
	 *
	 * @param array $args { group?, post_id? }
	 * @return array
	 */
	public function get_fields( array $args ): array {
		if ( ! function_exists( 'acf_get_fields' ) ) {
			return array( 'error' => 'ACF is not active.', 'code' => 'acf_inactive' );
		}
		$group   = $args['group'] ?? '';
		$post_id = $args['post_id'] ?? 0;

		if ( $group ) {
			$fields = acf_get_fields( $group );
		} elseif ( $post_id ) {
			$groups = acf_get_field_groups( array( 'post_id' => $post_id ) );
			$fields = ! empty( $groups ) ? acf_get_fields( $groups[0]['key'] ) : array();
		} else {
			return array( 'error' => 'Provide group or post_id.', 'code' => 'missing_param' );
		}

		$out = array();
		foreach ( (array) $fields as $f ) {
			$out[] = array(
				'key'   => $f['key'] ?? '',
				'name'  => $f['name'] ?? '',
				'label' => $f['label'] ?? '',
				'type'  => $f['type'] ?? '',
			);
		}
		return array( 'fields' => $out );
	}

	/**
	 * Get WordPress front page post ID (page_on_front option).
	 *
	 * @param array $args Unused.
	 * @return array { front_page_id: int }
	 */
	public function get_front_page_id( array $args ): array {
		$id = (int) get_option( 'page_on_front', 0 );
		return array( 'front_page_id' => $id );
	}

	/**
	 * Update multiple ACF fields for a post in one call.
	 *
	 * @param array $args { post_id, fields_json }
	 * @return array { success: bool, updated: string[], errors: array }
	 */
	public function update_fields_batch( array $args ): array {
		if ( ! function_exists( 'update_field' ) ) {
			return array( 'error' => 'ACF is not active.', 'code' => 'acf_inactive' );
		}
		$post_id     = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
		$fields_json = $args['fields_json'] ?? '';

		if ( ! $post_id ) {
			return array( 'error' => 'post_id is required.', 'code' => 'missing_post_id' );
		}
		if ( ! $fields_json ) {
			return array( 'error' => 'fields_json is required.', 'code' => 'missing_fields_json' );
		}

		$fields = json_decode( $fields_json, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $fields ) ) {
			return array( 'error' => 'Invalid fields_json: ' . json_last_error_msg(), 'code' => 'invalid_json' );
		}

		$updated = array();
		$errors  = array();
		foreach ( $fields as $field_name => $value ) {
			if ( ! is_string( $field_name ) || $field_name === '' ) {
				continue;
			}
			$ok = update_field( $field_name, $value, $post_id );
			if ( $ok ) {
				$updated[] = $field_name;
			} else {
				$errors[] = array( 'field' => $field_name, 'message' => 'update_field returned false' );
			}
		}
		return array(
			'success' => count( $updated ) > 0,
			'updated' => $updated,
			'errors'  => $errors,
		);
	}
}
