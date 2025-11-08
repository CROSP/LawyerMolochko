<?php
/**
 * Script to query and update WordPress pages directly in the database
 * 
 * Usage: php update-pages.php
 * Or access via web browser (if placed in web root)
 */

// Load WordPress
require_once( __DIR__ . '/wp-load.php' );

global $wpdb;

// Get the posts table name
$posts_table = $wpdb->posts;

/**
 * Query pages from the database
 * 
 * @param array $args Optional query arguments
 * @return array Array of page objects
 */
function query_pages( $args = array() ) {
    global $wpdb;
    
    $defaults = array(
        'post_status' => 'publish',
        'post_type' => 'page',
        'limit' => -1, // -1 means no limit
        'orderby' => 'post_title',
        'order' => 'ASC'
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $where = array();
    $where[] = $wpdb->prepare( "post_type = %s", $args['post_type'] );
    
    if ( ! empty( $args['post_status'] ) ) {
        if ( is_array( $args['post_status'] ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $args['post_status'] ), '%s' ) );
            $where[] = $wpdb->prepare( "post_status IN ($placeholders)", $args['post_status'] );
        } else {
            $where[] = $wpdb->prepare( "post_status = %s", $args['post_status'] );
        }
    }
    
    if ( ! empty( $args['post_id'] ) ) {
        $where[] = $wpdb->prepare( "ID = %d", $args['post_id'] );
    }
    
    if ( ! empty( $args['post_title'] ) ) {
        $where[] = $wpdb->prepare( "post_title LIKE %s", '%' . $wpdb->esc_like( $args['post_title'] ) . '%' );
    }
    
    $where_clause = implode( ' AND ', $where );
    
    $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
    if ( ! $orderby ) {
        $orderby = 'post_title ASC';
    }
    
    $limit = '';
    if ( $args['limit'] > 0 ) {
        $limit = $wpdb->prepare( "LIMIT %d", $args['limit'] );
    }
    
    $query = "SELECT * FROM {$wpdb->posts} WHERE {$where_clause} ORDER BY {$orderby} {$limit}";
    
    return $wpdb->get_results( $query );
}

/**
 * Update a page directly in the database
 * 
 * @param int $page_id Page ID
 * @param array $data Data to update (e.g., ['post_title' => 'New Title', 'post_content' => 'New Content'])
 * @return int|false Number of rows updated, or false on error
 */
function update_page( $page_id, $data ) {
    global $wpdb;
    
    // Ensure post_modified is updated
    $data['post_modified'] = current_time( 'mysql' );
    $data['post_modified_gmt'] = current_time( 'mysql', 1 );
    
    // Update the page
    $result = $wpdb->update(
        $wpdb->posts,
        $data,
        array( 'ID' => $page_id ),
        null, // Let wpdb determine format
        array( '%d' ) // ID format
    );
    
    if ( $result !== false ) {
        // Clear WordPress cache for this post
        clean_post_cache( $page_id );
    }
    
    return $result;
}

/**
 * Bulk update pages based on criteria
 * 
 * @param array $where_criteria Criteria to match pages (e.g., ['post_status' => 'draft'])
 * @param array $update_data Data to update
 * @return int|false Number of rows updated, or false on error
 */
function bulk_update_pages( $where_criteria, $update_data ) {
    global $wpdb;
    
    // Ensure post_modified is updated
    $update_data['post_modified'] = current_time( 'mysql' );
    $update_data['post_modified_gmt'] = current_time( 'mysql', 1 );
    
    // Ensure we're only updating pages
    $where_criteria['post_type'] = 'page';
    
    // Build WHERE clause
    $where_format = array();
    foreach ( $where_criteria as $key => $value ) {
        if ( is_numeric( $value ) ) {
            $where_format[] = '%d';
        } else {
            $where_format[] = '%s';
        }
    }
    
    // Build UPDATE format
    $update_format = array();
    foreach ( $update_data as $key => $value ) {
        if ( is_numeric( $value ) ) {
            $update_format[] = '%d';
        } else {
            $update_format[] = '%s';
        }
    }
    
    $result = $wpdb->update(
        $wpdb->posts,
        $update_data,
        $where_criteria,
        $update_format,
        $where_format
    );
    
    if ( $result !== false && $result > 0 ) {
        // Clear WordPress cache
        wp_cache_flush();
    }
    
    return $result;
}

// Example usage when run from command line or web
if ( php_sapi_name() === 'cli' || isset( $_GET['run'] ) ) {
    
    echo "=== WordPress Pages Database Query & Update Tool ===\n\n";
    
    // Example 1: Query all published pages
    echo "1. Querying all published pages...\n";
    $pages = query_pages( array( 'post_status' => 'publish' ) );
    echo "Found " . count( $pages ) . " published pages\n\n";
    
    // Display first 5 pages
    if ( ! empty( $pages ) ) {
        echo "Sample pages (first 5):\n";
        foreach ( array_slice( $pages, 0, 5 ) as $page ) {
            echo "  - ID: {$page->ID}, Title: {$page->post_title}, Status: {$page->post_status}\n";
        }
        echo "\n";
    }
    
    // Example 2: Query pages by title
    echo "2. Querying pages with 'Home' in title...\n";
    $home_pages = query_pages( array( 'post_title' => 'Home' ) );
    echo "Found " . count( $home_pages ) . " pages with 'Home' in title\n\n";
    
    // Example 3: Update a specific page (uncomment and modify as needed)
    /*
    echo "3. Updating a specific page...\n";
    $page_id = 1; // Change this to your page ID
    $update_result = update_page( $page_id, array(
        'post_title' => 'Updated Page Title',
        'post_content' => 'Updated page content here...'
    ) );
    
    if ( $update_result !== false ) {
        echo "Successfully updated page ID: {$page_id}\n";
    } else {
        echo "Failed to update page ID: {$page_id}\n";
    }
    echo "\n";
    */
    
    // Example 4: Bulk update pages (uncomment and modify as needed)
    /*
    echo "4. Bulk updating pages...\n";
    $bulk_result = bulk_update_pages(
        array( 'post_status' => 'draft' ), // Where criteria
        array( 'post_status' => 'publish' ) // Update data
    );
    
    if ( $bulk_result !== false ) {
        echo "Successfully updated {$bulk_result} pages\n";
    } else {
        echo "Failed to bulk update pages\n";
    }
    */
    
    echo "\n=== Script completed ===\n";
    echo "\nTo use this script programmatically:\n";
    echo "  - Call query_pages() to query pages\n";
    echo "  - Call update_page() to update a single page\n";
    echo "  - Call bulk_update_pages() to update multiple pages\n";
    
} else {
    // Web interface
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>WordPress Pages Database Tool</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .container { max-width: 1200px; margin: 0 auto; }
            h1 { color: #333; }
            .section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
            .button { background: #0073aa; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 3px; }
            .button:hover { background: #005a87; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #0073aa; color: white; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>WordPress Pages Database Tool</h1>
            
            <div class="section">
                <h2>Query Pages</h2>
                <form method="GET">
                    <input type="hidden" name="action" value="query">
                    <label>Status: 
                        <select name="status">
                            <option value="publish">Published</option>
                            <option value="draft">Draft</option>
                            <option value="any">Any</option>
                        </select>
                    </label>
                    <button type="submit" class="button">Query Pages</button>
                </form>
                
                <?php
                if ( isset( $_GET['action'] ) && $_GET['action'] === 'query' ) {
                    $status = isset( $_GET['status'] ) ? $_GET['status'] : 'publish';
                    $status = $status === 'any' ? array( 'publish', 'draft', 'private' ) : $status;
                    
                    $pages = query_pages( array( 'post_status' => $status, 'limit' => 100 ) );
                    
                    echo "<h3>Found " . count( $pages ) . " pages</h3>";
                    if ( ! empty( $pages ) ) {
                        echo "<table>";
                        echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Date</th><th>Actions</th></tr>";
                        foreach ( $pages as $page ) {
                            echo "<tr>";
                            echo "<td>{$page->ID}</td>";
                            echo "<td>{$page->post_title}</td>";
                            echo "<td>{$page->post_status}</td>";
                            echo "<td>{$page->post_date}</td>";
                            echo "<td><a href='?action=edit&id={$page->ID}'>Edit</a></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                }
                ?>
            </div>
            
            <?php
            if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['id'] ) ) {
                $page_id = intval( $_GET['id'] );
                $pages = query_pages( array( 'post_id' => $page_id ) );
                $page = ! empty( $pages ) ? $pages[0] : null;
                
                if ( $page && isset( $_POST['update'] ) ) {
                    $update_data = array();
                    if ( isset( $_POST['post_title'] ) ) {
                        $update_data['post_title'] = sanitize_text_field( $_POST['post_title'] );
                    }
                    if ( isset( $_POST['post_content'] ) ) {
                        $update_data['post_content'] = wp_kses_post( $_POST['post_content'] );
                    }
                    if ( isset( $_POST['post_status'] ) ) {
                        $update_data['post_status'] = sanitize_text_field( $_POST['post_status'] );
                    }
                    
                    if ( ! empty( $update_data ) ) {
                        $result = update_page( $page_id, $update_data );
                        if ( $result !== false ) {
                            echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>Page updated successfully!</div>";
                            // Refresh page data
                            $pages = query_pages( array( 'post_id' => $page_id ) );
                            $page = ! empty( $pages ) ? $pages[0] : null;
                        } else {
                            echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>Failed to update page.</div>";
                        }
                    }
                }
                
                if ( $page ) {
                    ?>
                    <div class="section">
                        <h2>Edit Page: <?php echo esc_html( $page->post_title ); ?></h2>
                        <form method="POST">
                            <input type="hidden" name="update" value="1">
                            <label>Title:<br>
                                <input type="text" name="post_title" value="<?php echo esc_attr( $page->post_title ); ?>" style="width: 100%; padding: 5px;">
                            </label><br><br>
                            <label>Content:<br>
                                <textarea name="post_content" rows="10" style="width: 100%; padding: 5px;"><?php echo esc_textarea( $page->post_content ); ?></textarea>
                            </label><br><br>
                            <label>Status:<br>
                                <select name="post_status">
                                    <option value="publish" <?php selected( $page->post_status, 'publish' ); ?>>Published</option>
                                    <option value="draft" <?php selected( $page->post_status, 'draft' ); ?>>Draft</option>
                                    <option value="private" <?php selected( $page->post_status, 'private' ); ?>>Private</option>
                                </select>
                            </label><br><br>
                            <button type="submit" class="button">Update Page</button>
                            <a href="?action=query&status=publish" class="button" style="text-decoration: none; display: inline-block;">Cancel</a>
                        </form>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </body>
    </html>
    <?php
}

