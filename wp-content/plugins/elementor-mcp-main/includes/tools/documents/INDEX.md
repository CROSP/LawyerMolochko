# Document Management Tools - Quick Index

Navigation index for all Document Management MCP tools.

## Files Overview

| File | Purpose | Size |
|------|---------|------|
| `class-get-document.php` | Get complete document data | 4.6K |
| `class-save-document.php` | Save document with validation | 6.0K |
| `class-get-elementor-data.php` | Get raw _elementor_data meta | 3.3K |
| `class-update-elementor-data.php` | Update raw meta directly | 5.7K |
| `class-create-elementor-page.php` | Create new Elementor page/post | 5.7K |
| `README.md` | Complete API documentation | 15K |
| `EXAMPLES.md` | Code examples and workflows | 13K |
| `AGENT6-SUMMARY.md` | Implementation summary | 13K |

---

## Quick Reference

### Tool Names

```
get_document           - Retrieve full document data
save_document          - Save elements and settings
get_elementor_data     - Get raw database value
update_elementor_data  - Update raw database value
create_elementor_page  - Create new page/post
```

### Required Inputs

```
get_document           → { post_id }
save_document          → { post_id, elements, [settings] }
get_elementor_data     → { post_id }
update_elementor_data  → { post_id, data }
create_elementor_page  → { title, [content_type], [status] }
```

### Namespace

All tools are in: `ElementorMCP\Tools\Documents`

### Extends

All tools extend: `ElementorMCP\Tools\Base_Tool`

---

## Usage Flow

```
1. Create Page
   └─> create_elementor_page

2. Add Content
   └─> save_document

3. Retrieve Data
   ├─> get_document (full data)
   └─> get_elementor_data (raw data)

4. Modify Content
   ├─> save_document (recommended)
   └─> update_elementor_data (direct)
```

---

## File Details

### class-get-document.php

**Class**: `Get_Document`
**Method**: `execute()`
**Returns**: Complete document structure

**Data Included**:
- Post information
- Elements array
- Document settings
- Metadata (version, template type, etc.)
- Autosave info
- URLs (edit, preview)

**API Calls**:
- `Plugin::$instance->documents->get($post_id)`
- `Document::get_elements_data()`
- `Document::get_settings()`

---

### class-save-document.php

**Class**: `Save_Document`
**Method**: `execute()`
**Returns**: Save status and stats

**Features**:
- Elements structure validation
- Settings update
- Autosave handling
- Cache clearing

**API Calls**:
- `Plugin::$instance->documents->get($post_id)`
- `Document::save($data)`

**Validation**:
- Elements is array
- Each element has elType
- Widgets have widgetType
- User can edit post

---

### class-get-elementor-data.php

**Class**: `Get_Elementor_Data`
**Method**: `execute()`
**Returns**: Raw and decoded meta data

**Features**:
- Direct database access
- JSON validation
- Element counting
- Elementor status check

**API Calls**:
- `get_post_meta($post_id, '_elementor_data', true)`

---

### class-update-elementor-data.php

**Class**: `Update_Elementor_Data`
**Method**: `execute()`
**Returns**: Update status

**Features**:
- Accepts array or JSON string
- Data validation
- Elementor enabled check
- Cache clearing
- wp_slash() for data integrity

**API Calls**:
- `update_post_meta($post_id, '_elementor_data', wp_slash($data))`
- `Post_CSS::create($post_id)->delete()`

**Security**:
- Post exists check
- Permission check
- Elementor enabled verification
- JSON validation
- Element structure validation

---

### class-create-elementor-page.php

**Class**: `Create_Elementor_Page`
**Method**: `execute()`
**Returns**: New post ID and URLs

**Features**:
- Create page or post
- Draft or publish status
- Auto-enable Elementor
- Initialize empty data
- Permission checks

**API Calls**:
- `wp_insert_post($post_data)`
- `update_post_meta()` (multiple)

**Meta Set**:
- `_elementor_edit_mode` = 'builder'
- `_elementor_version` = ELEMENTOR_VERSION
- `_elementor_data` = []

---

## Documentation Files

### README.md

**Sections**:
1. Overview
2. Tool Documentation (all 5 tools)
3. Elementor Document API Reference
4. Core Concepts
5. Element Structure
6. Data Storage
7. Common Workflows
8. Error Handling
9. Security
10. Testing

**Best For**:
- API reference
- Understanding architecture
- Learning Document system
- Troubleshooting

---

### EXAMPLES.md

**Sections**:
1. Basic tool usage
2. Common workflows
3. Element examples
4. Error handling
5. Advanced patterns
6. Testing commands

**Best For**:
- Quick start
- Copy-paste examples
- Learning by example
- Common use cases

---

### AGENT6-SUMMARY.md

**Sections**:
1. Mission overview
2. File details
3. Implementation notes
4. API study findings
5. Testing checklist
6. Integration notes
7. Key learnings

**Best For**:
- Development overview
- Integration planning
- Architecture understanding
- Review/audit

---

## Common Tasks

### Task: Get Page Content

**File**: `class-get-document.php`
**Input**:
```json
{"post_id": 123}
```

---

### Task: Update Page Content

**File**: `class-save-document.php`
**Input**:
```json
{
  "post_id": 123,
  "elements": [...]
}
```

---

### Task: Create New Page

**File**: `class-create-elementor-page.php`
**Input**:
```json
{
  "title": "New Page",
  "content_type": "page",
  "status": "draft"
}
```

---

### Task: Export/Import Data

**Export File**: `class-get-elementor-data.php`
**Import File**: `class-update-elementor-data.php`

---

### Task: Clone Page

**Files Used**:
1. `class-get-document.php` - Get original
2. `class-create-elementor-page.php` - Create new
3. `class-save-document.php` - Copy content

---

## Element Reference

### Minimal Section
```json
{
  "id": "section1",
  "elType": "section",
  "elements": []
}
```

### Minimal Column
```json
{
  "id": "column1",
  "elType": "column",
  "elements": []
}
```

### Minimal Widget
```json
{
  "id": "widget1",
  "elType": "widget",
  "widgetType": "heading",
  "settings": {
    "title": "Text"
  }
}
```

---

## Error Codes Quick Reference

| Code | Meaning | Tool |
|------|---------|------|
| `elementor_not_active` | Elementor not loaded | All |
| `post_not_found` | Invalid post ID | All |
| `permission_denied` | No edit access | All |
| `invalid_elements` | Bad element structure | Save/Update |
| `document_not_found` | Can't get document | Get/Save |
| `save_failed` | Save operation failed | Save |
| `elementor_not_enabled` | No Elementor meta | Update |
| `invalid_json` | JSON parse error | Get/Update Data |
| `creation_error` | Can't create post | Create |

---

## Integration Checklist

- [ ] All files in `includes/tools/documents/`
- [ ] Classes extend `Base_Tool`
- [ ] Namespace: `ElementorMCP\Tools\Documents`
- [ ] Register in `Tool_Manager`
- [ ] Test each tool
- [ ] Verify permissions work
- [ ] Check error handling
- [ ] Validate input schemas
- [ ] Test with real Elementor data

---

## Support

**Questions?**
- See `README.md` for detailed API reference
- See `EXAMPLES.md` for code examples
- See `AGENT6-SUMMARY.md` for implementation details

**Issues?**
- Check error codes above
- Verify post has Elementor enabled
- Confirm user permissions
- Validate element structure
- Check JSON formatting

---

## Version Info

**Created**: 2025-11-22
**Elementor Compatibility**: 3.0+
**WordPress Compatibility**: 5.0+
**PHP Requirement**: 7.0+

**Total Lines of Code**: ~1,400+
**Total Documentation**: ~4,500+ lines
**Total Files**: 8
