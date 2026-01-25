<?php
/**
 * Element Operations Trait
 *
 * Provides shared methods for element manipulation across tools.
 * Handles common operations like finding, validating, and modifying elements.
 *
 * @package ElementorMCP\Tools\Traits
 * @since 1.2.0
 */

namespace ElementorMCP\Tools\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Element Operations Trait
 *
 * Shared functionality for element CRUD operations.
 *
 * @since 1.2.0
 */
trait Element_Operations {

    /**
     * Find element recursively in elements array.
     *
     * @since 1.2.0
     * @param array  $elements   The elements array to search.
     * @param string $element_id The element ID to find.
     * @param array  &$path      Optional. Reference to array for tracking path.
     * @return array|null The found element or null.
     */
    protected function find_element_recursive( $elements, $element_id, &$path = [] ) {
        foreach ( $elements as $index => $element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                $path[] = [
                    'id'    => $element_id,
                    'index' => $index,
                    'type'  => $element['elType'] ?? 'unknown',
                ];
                return $element;
            }

            if ( ! empty( $element['elements'] ) ) {
                $path[] = [
                    'id'    => $element['id'] ?? 'unknown',
                    'index' => $index,
                    'type'  => $element['elType'] ?? 'unknown',
                ];
                $found = $this->find_element_recursive( $element['elements'], $element_id, $path );
                if ( $found ) {
                    return $found;
                }
                array_pop( $path );
            }
        }
        return null;
    }

    /**
     * Find parent element and index of a child element.
     *
     * @since 1.2.0
     * @param array  $elements   The elements array to search.
     * @param string $element_id The element ID to find parent for.
     * @param array  $parent     Current parent context (internal use).
     * @return array|null Array with [parent_element, index, is_root] or null.
     */
    protected function find_parent_and_index( $elements, $element_id, $parent = null ) {
        foreach ( $elements as $index => $element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                return [
                    'parent'  => $parent,
                    'index'   => $index,
                    'is_root' => $parent === null,
                ];
            }

            if ( ! empty( $element['elements'] ) ) {
                $found = $this->find_parent_and_index( $element['elements'], $element_id, $element );
                if ( $found ) {
                    return $found;
                }
            }
        }
        return null;
    }

    /**
     * Update element recursively by reference.
     *
     * @since 1.2.0
     * @param array  &$elements        The elements array (by reference).
     * @param string $element_id       The element ID to update.
     * @param array  $new_settings     New settings to apply.
     * @param bool   $replace_settings Whether to replace all settings.
     * @return bool True if element was found and updated.
     */
    protected function update_element_recursive( &$elements, $element_id, $new_settings, $replace_settings = false ) {
        foreach ( $elements as &$element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                if ( $replace_settings ) {
                    $element['settings'] = $new_settings;
                } else {
                    if ( ! isset( $element['settings'] ) ) {
                        $element['settings'] = [];
                    }
                    $element['settings'] = array_merge( $element['settings'], $new_settings );
                }
                return true;
            }

            if ( ! empty( $element['elements'] ) ) {
                if ( $this->update_element_recursive( $element['elements'], $element_id, $new_settings, $replace_settings ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Remove element recursively by reference.
     *
     * @since 1.2.0
     * @param array  &$elements  The elements array (by reference).
     * @param string $element_id The element ID to remove.
     * @return array|null The removed element or null if not found.
     */
    protected function remove_element_recursive( &$elements, $element_id ) {
        foreach ( $elements as $index => &$element ) {
            if ( isset( $element['id'] ) && $element['id'] === $element_id ) {
                $removed = $element;
                array_splice( $elements, $index, 1 );
                return $removed;
            }

            if ( ! empty( $element['elements'] ) ) {
                $removed = $this->remove_element_recursive( $element['elements'], $element_id );
                if ( $removed ) {
                    return $removed;
                }
            }
        }
        return null;
    }

    /**
     * Insert element at a specific parent and position.
     *
     * @since 1.2.0
     * @param array  &$elements   The elements array (by reference).
     * @param string $parent_id   The parent element ID (null for root).
     * @param array  $new_element The element to insert.
     * @param int    $position    Position index (-1 for append).
     * @return bool True on success, false if parent not found.
     */
    protected function insert_element_at_parent( &$elements, $parent_id, $new_element, $position = -1 ) {
        // If no parent_id, insert at root level
        if ( empty( $parent_id ) ) {
            if ( $position < 0 || $position >= count( $elements ) ) {
                $elements[] = $new_element;
            } else {
                array_splice( $elements, $position, 0, [ $new_element ] );
            }
            return true;
        }

        // Find parent and insert
        foreach ( $elements as &$element ) {
            if ( isset( $element['id'] ) && $element['id'] === $parent_id ) {
                if ( ! isset( $element['elements'] ) ) {
                    $element['elements'] = [];
                }

                if ( $position < 0 || $position >= count( $element['elements'] ) ) {
                    $element['elements'][] = $new_element;
                } else {
                    array_splice( $element['elements'], $position, 0, [ $new_element ] );
                }
                return true;
            }

            if ( ! empty( $element['elements'] ) ) {
                if ( $this->insert_element_at_parent( $element['elements'], $parent_id, $new_element, $position ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if an element type can be a child of a parent type.
     *
     * @since 1.2.0
     * @param string $parent_type Parent element type (section, column, container).
     * @param string $child_type  Child element type.
     * @return bool True if valid child relationship.
     */
    protected function is_valid_child_type( $parent_type, $child_type ) {
        $rules = [
            'section'   => [ 'column' ],
            'column'    => [ 'widget' ],
            'container' => [ 'container', 'widget' ],
        ];

        if ( ! isset( $rules[ $parent_type ] ) ) {
            return false;
        }

        return in_array( $child_type, $rules[ $parent_type ], true );
    }

    /**
     * Validate element can be inserted as child of parent.
     *
     * @since 1.2.0
     * @param array      $parent_element The parent element (null for root).
     * @param array      $child_element  The child element to validate.
     * @param array|null &$error         Optional. Reference to store error details.
     * @return bool True if valid.
     */
    protected function validate_parent_child_relationship( $parent_element, $child_element, &$error = null ) {
        $child_type = $child_element['elType'] ?? '';

        // Root level validation
        if ( $parent_element === null ) {
            $valid_root_types = [ 'section', 'container' ];
            if ( ! in_array( $child_type, $valid_root_types, true ) ) {
                $error = [
                    'code'    => 'invalid_root_element',
                    'message' => sprintf(
                        'Element type "%s" cannot be at root level. Only section or container allowed.',
                        $child_type
                    ),
                ];
                return false;
            }
            return true;
        }

        $parent_type = $parent_element['elType'] ?? '';

        if ( ! $this->is_valid_child_type( $parent_type, $child_type ) ) {
            $error = [
                'code'    => 'invalid_child_type',
                'message' => sprintf(
                    'Element type "%s" cannot be a child of "%s".',
                    $child_type,
                    $parent_type
                ),
            ];
            return false;
        }

        return true;
    }

    /**
     * Generate a unique element ID.
     *
     * @since 1.2.0
     * @return string 8-character hexadecimal ID.
     */
    protected function generate_element_id() {
        return substr( md5( uniqid( mt_rand(), true ) ), 0, 8 );
    }

    /**
     * Regenerate all IDs in an element tree.
     *
     * Creates new unique IDs for the element and all descendants.
     *
     * @since 1.2.0
     * @param array $element The element to regenerate IDs for.
     * @return array Element with new IDs.
     */
    protected function regenerate_ids_recursive( $element ) {
        $element['id'] = $this->generate_element_id();

        if ( ! empty( $element['elements'] ) ) {
            $element['elements'] = array_map(
                [ $this, 'regenerate_ids_recursive' ],
                $element['elements']
            );
        }

        return $element;
    }

    /**
     * Check if an element is a descendant of another.
     *
     * Used to prevent circular references when moving elements.
     *
     * @since 1.2.0
     * @param array  $elements      The elements array to search.
     * @param string $ancestor_id   Potential ancestor element ID.
     * @param string $descendant_id Potential descendant element ID.
     * @return bool True if descendant_id is inside ancestor_id.
     */
    protected function is_descendant_of( $elements, $ancestor_id, $descendant_id ) {
        // First find the ancestor
        $path = [];
        $ancestor = $this->find_element_recursive( $elements, $ancestor_id, $path );

        if ( ! $ancestor || empty( $ancestor['elements'] ) ) {
            return false;
        }

        // Check if descendant is within ancestor's children
        $descendant_path = [];
        $descendant = $this->find_element_recursive( $ancestor['elements'], $descendant_id, $descendant_path );

        return $descendant !== null;
    }

    /**
     * Get element path as array of IDs.
     *
     * @since 1.2.0
     * @param array  $elements   The elements array to search.
     * @param string $element_id The element ID to find path for.
     * @return array|null Array of element IDs from root to element, or null.
     */
    protected function get_element_path( $elements, $element_id ) {
        $path = [];
        $found = $this->find_element_recursive( $elements, $element_id, $path );

        if ( ! $found ) {
            return null;
        }

        return array_column( $path, 'id' );
    }

    /**
     * Count total elements in tree.
     *
     * @since 1.2.0
     * @param array $elements The elements array.
     * @return int Total element count.
     */
    protected function count_elements_recursive( $elements ) {
        $count = count( $elements );

        foreach ( $elements as $element ) {
            if ( ! empty( $element['elements'] ) ) {
                $count += $this->count_elements_recursive( $element['elements'] );
            }
        }

        return $count;
    }

    /**
     * Check if a post is valid and built with Elementor.
     *
     * @since 1.2.0
     * @param int $post_id The post ID.
     * @return bool True if valid Elementor post.
     */
    protected function is_valid_elementor_post( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return false;
        }

        if ( ! \Elementor\Plugin::$instance->documents->get( $post_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Get Elementor document for a post.
     *
     * @since 1.2.0
     * @param int $post_id The post ID.
     * @return \Elementor\Core\Base\Document|null The document or null.
     */
    protected function get_document( $post_id ) {
        return \Elementor\Plugin::$instance->documents->get( $post_id );
    }

    /**
     * Save document and invalidate caches.
     *
     * @since 1.2.0
     * @param \Elementor\Core\Base\Document $document The document to save.
     * @param array                         $elements The elements data.
     * @param int                           $post_id  The post ID.
     * @return bool|\WP_Error True on success, WP_Error on failure.
     */
    protected function save_document_and_invalidate( $document, $elements, $post_id ) {
        $result = $document->save( [
            'elements' => $elements,
            'settings' => $document->get_settings(),
        ] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Invalidate caches
        $this->invalidate_caches( $post_id );

        // Clear Elementor's internal cache
        \Elementor\Plugin::$instance->files_manager->clear_cache();

        return true;
    }

    /**
     * Invalidate MCP caches for a post.
     *
     * @since 1.2.0
     * @param int    $post_id    The post ID.
     * @param string $element_id Optional. Specific element ID to invalidate.
     */
    protected function invalidate_caches( $post_id, $element_id = null ) {
        if ( class_exists( '\ElementorMCP\Cache\Page_Cache' ) ) {
            \ElementorMCP\Cache\Page_Cache::invalidate( $post_id );
        }

        if ( $element_id && class_exists( '\ElementorMCP\Cache\Element_Cache' ) ) {
            \ElementorMCP\Cache\Element_Cache::invalidate( $post_id, $element_id );
        }
    }

    /**
     * Ensure element has required structure.
     *
     * @since 1.2.0
     * @param array $element The element to normalize.
     * @return array Normalized element.
     */
    protected function normalize_element( $element ) {
        // Ensure ID exists
        if ( empty( $element['id'] ) ) {
            $element['id'] = $this->generate_element_id();
        }

        // Ensure elType exists
        if ( empty( $element['elType'] ) ) {
            $element['elType'] = 'widget';
        }

        // Ensure settings is array/object
        if ( ! isset( $element['settings'] ) ) {
            $element['settings'] = new \stdClass();
        }

        // Ensure elements array exists for containers
        if ( in_array( $element['elType'], [ 'section', 'column', 'container' ], true ) ) {
            if ( ! isset( $element['elements'] ) ) {
                $element['elements'] = [];
            }
        }

        return $element;
    }
}
