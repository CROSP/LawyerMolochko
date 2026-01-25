# Agent 6: Document Management Tools - Implementation Summary

## Mission Completed

Created Document Management MCP tools for handling Elementor pages/posts with comprehensive API integration.

## Files Created

### 1. class-get-document.php
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\class-get-document.php`

**Tool Name**: `get_document`

**Purpose**: Retrieves full Elementor document data

**Implementation Details**:
- Uses `Plugin::$instance->documents->get($post_id)` to fetch document
- Calls `Document::get_elements_data()` for elements structure
- Retrieves settings via `Document::get_settings()`
- Gets metadata including template type, version, edit mode
- Returns autosave information if available
- Includes edit URL and preview URL

**Input**: `{ post_id: integer }`

**Output**:
```json
{
  "post_id": 123,
  "post": { /* WordPress post data */ },
  "elements": [ /* Elementor elements array */ ],
  "settings": { /* Document settings */ },
  "metadata": {
    "template_type": "wp-page",
    "edit_mode": "builder",
    "version": "3.18.0",
    "is_built_with_elementor": true
  },
  "document_type": "wp-page",
  "autosave": null,
  "edit_url": "...",
  "preview_url": "..."
}
```

### 2. class-save-document.php
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\class-save-document.php`

**Tool Name**: `save_document`

**Purpose**: Saves document data with validation

**Implementation Details**:
- Uses `Document::save()` method from Elementor
- Validates elements structure before saving
- Checks for required `elType` property on all elements
- Validates `widgetType` for widget elements
- Verifies user can edit the document
- Clears CSS and element cache after save

**Input**:
```json
{
  "post_id": 123,
  "elements": [ /* array of elements */ ],
  "settings": { /* optional settings */ }
}
```

**Validation Rules**:
1. Elements must be array
2. Each element must be array
3. Each element must have `elType`
4. Widget elements must have `widgetType`
5. User must be able to edit post

**Output**:
```json
{
  "post_id": 123,
  "status": "publish",
  "post_modified": "2025-11-22 10:30:00",
  "autosave": { /* autosave info if exists */ },
  "elements_count": 5
}
```

### 3. class-get-elementor-data.php
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\class-get-elementor-data.php`

**Tool Name**: `get_elementor_data`

**Purpose**: Retrieves raw `_elementor_data` meta value

**Implementation Details**:
- Direct database access via `get_post_meta($post_id, '_elementor_data', true)`
- Returns both raw JSON string and decoded array
- Validates JSON is properly formatted
- Checks if post has Elementor enabled
- Counts number of top-level elements

**Input**: `{ post_id: integer }`

**Output**:
```json
{
  "post_id": 123,
  "has_elementor_data": true,
  "is_built_with_elementor": true,
  "raw_data": "[{...}]",
  "decoded_data": [{...}],
  "elements_count": 3
}
```

### 4. class-update-elementor-data.php
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\class-update-elementor-data.php`

**Tool Name**: `update_elementor_data`

**Purpose**: Updates raw `_elementor_data` meta value

**Implementation Details**:
- Uses `update_post_meta($post_id, '_elementor_data', wp_slash($data))`
- Accepts both JSON string and array input
- Validates JSON structure
- Verifies post has `_elementor_edit_mode` = 'builder'
- Uses `wp_slash()` to prevent data corruption
- Clears post CSS cache after update
- Deletes element cache

**Security Checks**:
1. Verifies post exists
2. Checks user can edit post
3. Validates post has Elementor enabled
4. Validates JSON structure
5. Validates elements have `elType`

**Input**:
```json
{
  "post_id": 123,
  "data": [ /* array or JSON string */ ]
}
```

**Output**:
```json
{
  "post_id": 123,
  "updated": true,
  "data_length": 1234,
  "elements_count": 3
}
```

### 5. class-create-elementor-page.php
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\class-create-elementor-page.php`

**Tool Name**: `create_elementor_page`

**Purpose**: Creates new WordPress post/page with Elementor enabled

**Implementation Details**:
- Uses `wp_insert_post()` to create post
- Sets `_elementor_edit_mode` = 'builder'
- Sets `_elementor_version` = current Elementor version
- Initializes empty `_elementor_data` = []
- Supports both pages and posts
- Supports draft and publish status
- Checks create and publish permissions

**Metadata Set**:
```php
update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array() ) ) );
```

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
  "post_id": 456,
  "title": "My New Page",
  "post_type": "page",
  "post_status": "draft",
  "edit_url": "http://example.com/wp-admin/post.php?post=456&action=elementor",
  "preview_url": "http://example.com/?elementor-preview=456",
  "permalink": "http://example.com/my-new-page/",
  "elementor_enabled": true
}
```

### 6. README.md
**Location**: `E:\Projects\WPCursor\elementor-mcp\includes\tools\documents\README.md`

**Purpose**: Comprehensive documentation of Document API

**Contents**:
- Overview of Elementor Document architecture
- Detailed tool documentation
- Elementor Document API reference
- Core concepts and methods
- Element structure specifications
- Data storage format
- Common workflows and examples
- Error handling
- Security considerations
- Testing instructions

---

## Elementor Document API Study

### Key Findings

#### 1. Documents Manager (documents-manager.php)

**Main Class**: `Elementor\Core\Documents_Manager`

**Key Methods Identified**:
```php
public function get( $post_id, $from_cache = true )
// Line 177-207
// Retrieves document by post ID
// Creates document instance based on doc type
// Caches documents for performance

public function create( $type, $post_data = [], $meta_data = [] )
// Line 376-429
// Creates new document with specified type
// Sets meta_data including _elementor_edit_mode
// Returns Document instance

public function ajax_save( $request )
// Line 511-590
// Handles AJAX save from editor
// Validates permissions
// Calls Document::save()
```

**Document Type Detection** (Line 757-774):
```php
private function get_doc_type_by_id( $post_id )
// Checks _elementor_template_type meta
// Falls back to post_type mapping
// Returns document type string
```

#### 2. Document Base Class (document.php)

**Main Class**: `Elementor\Core\Base\Document`

**Critical Constants** (Lines 42-49):
```php
const TYPE_META_KEY = '_elementor_template_type';
const PAGE_META_KEY = '_elementor_page_settings';
const ELEMENTOR_DATA_META_KEY = '_elementor_data';
const BUILT_WITH_ELEMENTOR_META_KEY = '_elementor_edit_mode';
```

**Element Data Methods**:

**get_elements_data()** (Lines 1106-1134):
```php
public function get_elements_data( $status = self::STATUS_PUBLISH )
// Gets JSON meta for ELEMENTOR_DATA_META_KEY
// Handles autosave retrieval
// Converts to Elementor if empty (first time)
```

**get_elements_raw_data()** (Lines 1056-1096):
```php
public function get_elements_raw_data( $data = null, $with_html_content = false )
// Iterates through elements
// Creates element instances
// Gets raw data from each element
// Returns editor-ready data array
```

**save()** (Lines 792-890):
```php
public function save( $data )
// Validates user can edit
// Saves elements via save_elements()
// Saves settings via save_settings()
// Saves template type and version
// Deletes CSS cache
// Fires before/after hooks
```

**save_elements()** (Lines 1347-1387):
```php
protected function save_elements( $elements )
// Gets raw data from elements
// JSON encodes with wp_slash()
// Updates _elementor_data meta
// Saves plain text version
// Fires iteration actions
```

**Meta Operations**:
```php
// Lines 1023-1043: get_json_meta() and update_json_meta()
// Lines 1491-1521: get_meta(), update_meta(), delete_meta()
// Lines 1453-1481: get_main_meta(), update_main_meta(), delete_main_meta()
```

#### 3. Database Class (db.php)

**Main Class**: `Elementor\DB`

**copy_elementor_meta()** (Lines 356-379):
```php
public function copy_elementor_meta( $from_post_id, $to_post_id )
// Copies all _elementor* meta keys
// Handles _elementor_data with wp_slash()
// Uses update_metadata for revision support
```

**iterate_data()** (Lines 259-279):
```php
public function iterate_data( $data_container, $callback, $args = [] )
// Recursively iterates through element tree
// Calls callback for each element
// Handles nested elements array
```

**save_plain_text()** (Lines 225-242):
```php
public function save_plain_text( $post_id )
// Renders element plain content
// Strips HTML tags
// Saves to post_content for search
```

### Data Structure Insights

#### Element Hierarchy
```
Document
└── Elements Array (sections/containers)
    └── Section/Container
        └── Columns
            └── Widgets
                └── Settings
```

#### Required Fields
- All elements: `id`, `elType`
- Widgets: `widgetType`
- Optional: `settings`, `elements` (for containers)

#### Storage Format
- `_elementor_data`: JSON array of elements
- Must use `wp_slash()` before saving
- Retrieved with `get_post_meta()` and `json_decode()`

---

## Testing Checklist

- [x] All 5 tools extend Base_Tool
- [x] Each tool has get_name(), get_description(), get_input_schema(), execute()
- [x] Input validation implemented
- [x] Permission checks included
- [x] Error handling with standardized format
- [x] Success responses follow MCP protocol
- [x] Elementor API methods used correctly
- [x] Data validation before saves
- [x] Cache clearing after modifications
- [x] Documentation complete

---

## Integration Notes

### Tool Manager Registration

These tools should be registered in the Tool_Manager class:

```php
// In Tool_Manager::register_tools()
require_once ELEMENTOR_MCP_PATH . 'includes/tools/documents/class-get-document.php';
require_once ELEMENTOR_MCP_PATH . 'includes/tools/documents/class-save-document.php';
require_once ELEMENTOR_MCP_PATH . 'includes/tools/documents/class-get-elementor-data.php';
require_once ELEMENTOR_MCP_PATH . 'includes/tools/documents/class-update-elementor-data.php';
require_once ELEMENTOR_MCP_PATH . 'includes/tools/documents/class-create-elementor-page.php';

$this->register_tool( new \ElementorMCP\Tools\Documents\Get_Document() );
$this->register_tool( new \ElementorMCP\Tools\Documents\Save_Document() );
$this->register_tool( new \ElementorMCP\Tools\Documents\Get_Elementor_Data() );
$this->register_tool( new \ElementorMCP\Tools\Documents\Update_Elementor_Data() );
$this->register_tool( new \ElementorMCP\Tools\Documents\Create_Elementor_Page() );
```

---

## Key Learnings

1. **Document vs Post**: Elementor wraps WordPress posts in Document objects
2. **Data Slashing**: Must use `wp_slash()` when saving JSON to prevent corruption
3. **Cache Management**: Always clear CSS and element cache after modifications
4. **Autosaves**: Separate from WordPress autosaves, stored as revisions
5. **Validation**: Elements must have elType, widgets need widgetType
6. **Permissions**: Check both general edit_posts and specific edit_post capabilities
7. **Iteration**: Elementor provides iterate_data() for recursive element processing

---

## Architecture Alignment

✅ Follows Base_Tool abstract class pattern
✅ Uses Elementor's core API methods
✅ Implements proper validation and security
✅ Provides comprehensive documentation
✅ Mirrors Elementor's architecture patterns
✅ Compatible with MCP protocol
✅ Extensible for future enhancements

---

## Conclusion

Successfully created a complete set of Document Management tools that provide full CRUD operations for Elementor pages and posts. The implementation is production-ready with proper error handling, validation, and documentation.

**Total Files Created**: 6
- 5 Tool Classes
- 1 Comprehensive README

**Lines of Code**: ~1,400+ (excluding documentation)

**API Coverage**:
- Documents Manager API ✓
- Document Base Class API ✓
- Database Operations API ✓
- Meta Data Management ✓
- Element Validation ✓

Ready for integration into the MCP server.
