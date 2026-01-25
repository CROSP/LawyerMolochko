# Elementor Document Management MCP Tools

This directory contains MCP tools for managing Elementor pages, posts, and documents. These tools provide programmatic access to Elementor's Document API, allowing you to create, retrieve, and modify Elementor content.

## Overview

Elementor uses a **Document** architecture to manage pages and posts. Each post/page built with Elementor is represented by a `Document` object that contains:

- **Elements Data**: The structure of sections, columns, and widgets
- **Settings**: Page-level settings (custom CSS, page layout, etc.)
- **Metadata**: Version info, edit mode, template type
- **Post Data**: Standard WordPress post information

## Tools

### 1. get_document

**Purpose**: Retrieves complete document data including elements, settings, and metadata.

**Input**:
```json
{
  "post_id": 123
}
```

**Output**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "post": {
      "ID": 123,
      "post_title": "My Page",
      "post_status": "publish",
      "post_type": "page",
      "post_modified": "2025-11-22 10:30:00",
      "post_author": 1
    },
    "elements": [
      {
        "id": "abc123",
        "elType": "section",
        "elements": [...]
      }
    ],
    "settings": {
      "post_title": "My Page",
      "post_status": "publish"
    },
    "metadata": {
      "template_type": "wp-page",
      "edit_mode": "builder",
      "version": "3.18.0",
      "is_built_with_elementor": true
    },
    "document_type": "wp-page",
    "autosave": null,
    "edit_url": "http://example.com/wp-admin/post.php?post=123&action=elementor",
    "preview_url": "http://example.com/?elementor-preview=123"
  }
}
```

**API Used**: `Plugin::$instance->documents->get($post_id)`

**Key Methods**:
- `Document::get_elements_data()` - Retrieves elements structure
- `Document::get_settings()` - Retrieves document settings
- `Document::get_template_type()` - Gets document type
- `Document::is_built_with_elementor()` - Checks if built with Elementor
- `Document::get_autosave()` - Retrieves autosave if exists

---

### 2. save_document

**Purpose**: Saves document data including elements and settings with validation.

**Input**:
```json
{
  "post_id": 123,
  "elements": [
    {
      "id": "abc123",
      "elType": "section",
      "settings": {},
      "elements": [
        {
          "id": "def456",
          "elType": "column",
          "elements": [
            {
              "id": "ghi789",
              "elType": "widget",
              "widgetType": "text-editor",
              "settings": {
                "editor": "Hello World"
              }
            }
          ]
        }
      ]
    }
  ],
  "settings": {
    "post_title": "Updated Title",
    "custom_css": ".my-class { color: red; }"
  }
}
```

**Output**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "status": "publish",
    "post_modified": "2025-11-22 10:35:00",
    "autosave": null,
    "elements_count": 1
  },
  "message": "Document saved successfully"
}
```

**API Used**: `Document::save($data)`

**Validation**:
- Checks if post exists
- Verifies user can edit post
- Validates elements structure (requires `elType` for all elements)
- Validates `widgetType` for widget elements
- Checks if document is editable by current user

**Key Features**:
- Validates elements before saving
- Automatically clears post CSS cache
- Creates autosave if needed
- Preserves revision history

---

### 3. get_elementor_data

**Purpose**: Retrieves the raw `_elementor_data` meta value (JSON).

**Input**:
```json
{
  "post_id": 123
}
```

**Output**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "has_elementor_data": true,
    "is_built_with_elementor": true,
    "raw_data": "[{\"id\":\"abc123\",\"elType\":\"section\"...}]",
    "decoded_data": [
      {
        "id": "abc123",
        "elType": "section"
      }
    ],
    "elements_count": 1
  }
}
```

**API Used**: `get_post_meta($post_id, '_elementor_data', true)`

**Use Cases**:
- Direct database access to Elementor data
- Debugging data structure
- Export/import operations
- Low-level data inspection

---

### 4. update_elementor_data

**Purpose**: Updates the raw `_elementor_data` meta value directly.

**Input**:
```json
{
  "post_id": 123,
  "data": [
    {
      "id": "new123",
      "elType": "section",
      "elements": []
    }
  ]
}
```

OR with JSON string:

```json
{
  "post_id": 123,
  "data": "[{\"id\":\"new123\",\"elType\":\"section\",\"elements\":[]}]"
}
```

**Output**:
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "updated": true,
    "data_length": 156,
    "elements_count": 1
  },
  "message": "Elementor data updated successfully"
}
```

**API Used**: `update_post_meta($post_id, '_elementor_data', wp_slash($data))`

**Security Checks**:
1. Verifies post exists
2. Checks user can edit post
3. Validates post has `_elementor_edit_mode` = 'builder'
4. Validates JSON structure
5. Validates elements have required fields

**Important Notes**:
- Uses `wp_slash()` to prevent data corruption during `update_post_meta`
- Clears Elementor CSS cache after update
- Deletes element cache
- More direct than `save_document`, less validation

---

### 5. create_elementor_page

**Purpose**: Creates a new WordPress post/page with Elementor enabled.

**Input**:
```json
{
  "title": "My New Page",
  "content_type": "page",
  "status": "draft"
}
```

**Output**:
```json
{
  "success": true,
  "data": {
    "post_id": 456,
    "title": "My New Page",
    "post_type": "page",
    "post_status": "draft",
    "edit_url": "http://example.com/wp-admin/post.php?post=456&action=elementor",
    "preview_url": "http://example.com/?elementor-preview=456",
    "permalink": "http://example.com/my-new-page/",
    "elementor_enabled": true
  },
  "message": "Elementor page created successfully with ID 456"
}
```

**API Used**:
- `wp_insert_post()` - Creates the post
- `update_post_meta()` - Sets Elementor metadata

**Metadata Set**:
```php
update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array() ) ) );
```

**Permission Checks**:
- Verifies user can create posts of specified type
- If status is 'publish', checks user can publish posts

---

## Elementor Document API Reference

### Core Concepts

#### 1. Document Manager
**Class**: `Elementor\Core\Documents_Manager`

Access via: `\Elementor\Plugin::$instance->documents`

**Key Methods**:
```php
// Get document by post ID
$document = Plugin::$instance->documents->get( $post_id );

// Get document with autosave
$document = Plugin::$instance->documents->get_doc_or_auto_save( $post_id );

// Create new document
$document = Plugin::$instance->documents->create( $type, $post_data, $meta_data );
```

#### 2. Document Base Class
**Class**: `Elementor\Core\Base\Document`

**Key Constants**:
```php
Document::TYPE_META_KEY              // '_elementor_template_type'
Document::PAGE_META_KEY              // '_elementor_page_settings'
Document::ELEMENTOR_DATA_META_KEY    // '_elementor_data'
Document::BUILT_WITH_ELEMENTOR_META_KEY // '_elementor_edit_mode'
```

**Important Methods**:

##### Get Elements Data
```php
$elements = $document->get_elements_data();
// Returns: Array of element data structures
```

##### Get Raw Elements
```php
$raw_elements = $document->get_elements_raw_data( $data, $with_html_content );
// Returns: Raw element data with full configuration
```

##### Save Document
```php
$data = [
    'elements' => $elements_array,
    'settings' => $settings_array
];
$success = $document->save( $data );
// Returns: Boolean success/failure
```

##### Get Settings
```php
$settings = $document->get_settings();
// Returns: Array of document settings
```

##### Is Built with Elementor
```php
$is_elementor = $document->is_built_with_elementor();
// Returns: Boolean
```

##### Get/Set Autosave
```php
$autosave = $document->get_autosave( $user_id, $create );
$autosave_id = $document->get_autosave_id( $user_id );
```

##### Meta Operations
```php
// Get meta
$value = $document->get_meta( $key );
$value = $document->get_main_meta( $key );

// Update meta
$document->update_meta( $key, $value );
$document->update_main_meta( $key, $value );

// JSON meta
$json_data = $document->get_json_meta( $key );
$document->update_json_meta( $key, $value );
```

#### 3. Element Structure

Elements follow a hierarchical structure:

```php
[
    'id' => 'unique_id',        // Random string ID
    'elType' => 'section',      // Element type: section, column, widget
    'settings' => [],           // Element settings
    'elements' => [             // Child elements
        [
            'id' => 'child_id',
            'elType' => 'column',
            'elements' => [...]
        ]
    ]
]
```

**Widget Element**:
```php
[
    'id' => 'widget_id',
    'elType' => 'widget',
    'widgetType' => 'text-editor',  // Required for widgets
    'settings' => [
        'editor' => 'Content here...'
    ]
]
```

#### 4. Data Storage

Elementor stores data in multiple meta keys:

| Meta Key | Purpose | Format |
|----------|---------|--------|
| `_elementor_data` | Element structure | JSON array |
| `_elementor_page_settings` | Page settings | Serialized array |
| `_elementor_edit_mode` | Builder status | 'builder' or empty |
| `_elementor_template_type` | Document type | String (wp-page, wp-post, etc.) |
| `_elementor_version` | Elementor version | String |
| `_elementor_css` | CSS generation status | Array |

#### 5. Database Class
**Class**: `Elementor\DB`

**Key Methods**:

##### Copy Elementor Meta
```php
Plugin::$instance->db->copy_elementor_meta( $from_post_id, $to_post_id );
```

##### Iterate Data
```php
$processed = Plugin::$instance->db->iterate_data( $data, function( $element ) {
    // Process each element
    return $element;
});
```

##### Save Plain Text
```php
Plugin::$instance->db->save_plain_text( $post_id );
// Saves searchable text version to post_content
```

---

## Common Workflows

### Create a New Page with Content

```php
// 1. Create page
$result = create_elementor_page([
    'title' => 'My New Page',
    'content_type' => 'page',
    'status' => 'draft'
]);

$post_id = $result['data']['post_id'];

// 2. Add content
save_document([
    'post_id' => $post_id,
    'elements' => [
        [
            'id' => 'section1',
            'elType' => 'section',
            'elements' => [
                [
                    'id' => 'column1',
                    'elType' => 'column',
                    'elements' => [
                        [
                            'id' => 'widget1',
                            'elType' => 'widget',
                            'widgetType' => 'heading',
                            'settings' => [
                                'title' => 'Welcome!'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
]);
```

### Clone a Page

```php
// 1. Get original document
$original = get_document(['post_id' => 123]);

// 2. Create new page
$new_page = create_elementor_page([
    'title' => $original['data']['post']['post_title'] . ' (Copy)',
    'content_type' => 'page',
    'status' => 'draft'
]);

// 3. Copy elements
save_document([
    'post_id' => $new_page['data']['post_id'],
    'elements' => $original['data']['elements'],
    'settings' => $original['data']['settings']
]);
```

### Modify Existing Content

```php
// 1. Get current document
$doc = get_document(['post_id' => 123]);

// 2. Modify elements (example: change heading text)
$elements = $doc['data']['elements'];
// ... modify $elements array ...

// 3. Save changes
save_document([
    'post_id' => 123,
    'elements' => $elements
]);
```

---

## Important Notes

### Data Slashing

When updating `_elementor_data` directly, always use `wp_slash()`:

```php
update_post_meta( $post_id, '_elementor_data', wp_slash( $json_data ) );
```

This prevents WordPress from unslashing quotes in the JSON, which would corrupt the data.

### Cache Management

After modifying Elementor data:
1. Post CSS is automatically deleted
2. Element cache is cleared
3. Page may need to be regenerated on next visit

### Autosaves

Elementor creates autosaves similar to WordPress:
- Stored as post revisions
- Contain user-specific changes
- Retrieved with `get_autosave()`

### Document Types

Common document types:
- `wp-page` - Standard WordPress page
- `wp-post` - Standard WordPress post
- `footer` - Footer template
- `header` - Header template
- `section` - Section template
- `page` - Generic template

### Version Compatibility

These tools work with:
- Elementor 3.0+
- Uses core Elementor APIs
- Compatible with Elementor Pro

---

## Error Handling

All tools return standardized error responses:

```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Human readable message",
    "data": {
      "additional": "context"
    }
  }
}
```

Common error codes:
- `elementor_not_active` - Elementor plugin not loaded
- `post_not_found` - Post ID doesn't exist
- `permission_denied` - User lacks required capabilities
- `invalid_elements` - Elements array structure invalid
- `save_failed` - Document save operation failed
- `elementor_not_enabled` - Post doesn't have Elementor enabled

---

## Testing

Example test workflow:

```bash
# 1. Create a new page
create_elementor_page {
  "title": "Test Page",
  "content_type": "page",
  "status": "draft"
}

# 2. Get the document (use returned post_id)
get_document {
  "post_id": 456
}

# 3. Save some content
save_document {
  "post_id": 456,
  "elements": [...]
}

# 4. Verify data was saved
get_elementor_data {
  "post_id": 456
}
```

---

## Security Considerations

1. **Permission Checks**: All tools verify user capabilities before operations
2. **Post Ownership**: Users must have edit permission for the specific post
3. **Data Validation**: Elements and settings are validated before saving
4. **SQL Injection**: Uses WordPress functions with proper escaping
5. **XSS Prevention**: Data is sanitized through WordPress and Elementor APIs

---

## Further Reading

- [Elementor Developer Docs](https://developers.elementor.com/)
- [Document Class Reference](https://code.elementor.com/classes/elementor-core-base-document/)
- [Elements Manager](https://code.elementor.com/classes/elementor-elements-manager/)
- WordPress Post Meta Functions
