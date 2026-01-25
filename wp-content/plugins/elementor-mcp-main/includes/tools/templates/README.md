# Elementor Template Library Tools

MCP tools for managing Elementor templates - importing, exporting, listing, and creating templates.

## Overview

These tools provide programmatic access to Elementor's template library system, allowing AI agents to manage templates through the MCP protocol. The tools integrate with Elementor's `Plugin::$instance->templates_manager` and template sources (`Source_Local`, `Source_Remote`).

## Template System Architecture

### Template Sources

Elementor uses a **source-based architecture** for template management:

1. **Local Source** (`Source_Local`)
   - Templates saved in the WordPress database
   - Post type: `elementor_library`
   - Taxonomy: `elementor_library_type` (page, section, widget, etc.)
   - Fully editable and exportable

2. **Remote Source** (`Source_Remote`)
   - Templates from Elementor.com cloud library
   - Read-only (cannot be edited directly)
   - Must be imported to local to modify

3. **Template Manager** (`Manager`)
   - Central controller for all template operations
   - Manages registered sources
   - Handles import/export operations
   - Coordinates image imports

### Template Data Structure

An Elementor template consists of:

```json
{
  "content": [],          // Array of element objects (sections, columns, widgets)
  "page_settings": {},    // Page-level settings (layout, colors, typography)
  "title": "Template Name",
  "type": "page",         // Template type (page, section, widget, etc.)
  "version": "3.x.x"      // Elementor DB version
}
```

#### Content Structure

The `content` array contains nested element objects:

```json
{
  "id": "abc123",         // Unique element ID
  "elType": "section",    // Element type
  "settings": {           // Element-specific settings
    "layout": "boxed",
    "background_color": "#fff"
  },
  "elements": []          // Nested child elements
}
```

Element hierarchy:
- **Section** → Contains columns
- **Column** → Contains widgets
- **Widget** → Actual content elements (heading, text, image, etc.)

#### Page Settings

Page settings control document-wide configurations:

```json
{
  "page_layout": "default",
  "hide_title": "yes",
  "post_title": "Custom Title",
  "template": "elementor_canvas",
  "custom_css": "",
  "page_transitions": {}
}
```

## Tools

### 1. list_templates

Lists templates from local or remote library with filtering.

**Input:**
```json
{
  "type": "page|section|widget|all",
  "source": "local|remote|all"
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "templates": [
      {
        "template_id": 123,
        "title": "Homepage",
        "type": "page",
        "source": "local",
        "thumbnail": "https://...",
        "date": 1234567890,
        "author": "Admin",
        "url": "https://..."
      }
    ],
    "count": 1,
    "filters": {
      "type": "page",
      "source": "local"
    }
  }
}
```

**Use Cases:**
- Browse available templates
- Find templates by type
- Audit local vs remote templates

### 2. get_template

Retrieves complete template data including elements, settings, and metadata.

**Input:**
```json
{
  "template_id": 123,
  "source": "local|remote"
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "template_id": 123,
    "title": "Homepage",
    "type": "page",
    "source": "local",
    "content": [...],          // Full element tree
    "page_settings": {...},    // Page settings
    "metadata": {
      "thumbnail": "https://...",
      "date": 1234567890,
      "author": "Admin",
      "url": "https://..."
    }
  }
}
```

**Use Cases:**
- Analyze template structure
- Extract template content for modification
- Clone template with modifications

### 3. import_template

Imports templates from JSON data or remote template IDs.

**Input (from JSON data):**
```json
{
  "template_data": {
    "content": [...],
    "page_settings": {...},
    "title": "Imported Template",
    "type": "page"
  },
  "import_images": true
}
```

**Input (from remote ID):**
```json
{
  "template_id": "remote-template-id",
  "import_images": true
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "template_id": 456,
    "title": "Imported Template",
    "type": "page",
    "url": "https://...",
    "export_link": "https://...",
    "import_method": "json_data|remote_id"
  }
}
```

**Features:**
- **Image Import**: Automatically downloads and imports remote images
- **ID Regeneration**: Creates new unique IDs for all elements
- **Data Processing**: Handles `on_import` processing for controls and elements

**Use Cases:**
- Import templates from external sources
- Migrate templates between sites
- Import from Elementor cloud library

### 4. export_template

Exports local templates to Elementor's JSON format.

**Input:**
```json
{
  "template_id": 123
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "template_id": 123,
    "title": "Homepage",
    "type": "page",
    "filename": "elementor-123-2025-11-22.json",
    "export_data": {
      "content": [...],
      "page_settings": {...},
      "version": "3.18.0",
      "title": "Homepage",
      "type": "page"
    },
    "json": "{...}",           // JSON string for file download
    "version": "3.18.0"
  }
}
```

**Use Cases:**
- Backup templates
- Share templates between sites
- Version control for templates
- Create template libraries

### 5. save_template

Creates new Elementor templates programmatically.

**Input:**
```json
{
  "title": "New Template",
  "type": "page",
  "content": [...],          // Optional, can be empty
  "page_settings": {...}     // Optional
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "template_id": 789,
    "title": "New Template",
    "type": "page",
    "url": "https://...",
    "edit_url": "https://.../wp-admin/post.php?post=789&action=elementor",
    "export_link": "https://...",
    "status": "publish"
  }
}
```

**Valid Template Types:**
- `page` - Full page templates
- `section` - Reusable sections
- `widget` - Single widget templates
- `header` - Theme Builder headers
- `footer` - Theme Builder footers
- `single` - Single post templates
- `archive` - Archive templates

**Use Cases:**
- Generate templates programmatically
- Create template starters
- Build template libraries
- Automate template creation

## Template Format Insights

### 1. Element ID System

Every element has a unique ID generated by `Utils::generate_random_string()`:
- **Format**: Random alphanumeric string
- **Purpose**: DOM manipulation, element targeting, data binding
- **Regeneration**: IDs are regenerated on import/clone to avoid conflicts

### 2. Settings Inheritance

Elements inherit settings from parents:
- **Section settings** → Apply to all columns and widgets within
- **Column settings** → Apply to widgets within the column
- **Widget settings** → Specific to that widget instance

### 3. Responsive Design

Elementor uses responsive breakpoints in settings:
```json
{
  "setting_name": "desktop_value",
  "setting_name_tablet": "tablet_value",
  "setting_name_mobile": "mobile_value"
}
```

Suffixes: `_tablet`, `_mobile`

### 4. Dynamic Content

Templates can contain dynamic tags:
```json
{
  "text": "{site_title}",
  "image": {
    "url": "{{post_featured_image}}",
    "id": "dynamic"
  }
}
```

### 5. Global Settings

Some settings reference global values:
```json
{
  "typography_typography": "custom",
  "__globals__": {
    "typography_font_family": "globals/typography?id=primary"
  }
}
```

### 6. Image Handling

Images in templates store both URL and ID:
```json
{
  "image": {
    "url": "https://example.com/image.jpg",
    "id": 123
  }
}
```

On import:
- Remote URLs are downloaded via `Import_Images` class
- New attachment posts are created
- IDs are updated to local attachment IDs
- Hash stored to prevent duplicate downloads

### 7. Version Compatibility

Templates include Elementor version:
- Used to handle format changes
- Triggers migrations if needed
- Ensures compatibility checks

### 8. Export Processing

During export, elements go through `on_export()`:
- Sensitive data removed (API keys, private settings)
- Temporary data cleared
- Controls marked `'export' => false` are excluded
- URLs converted to relative where possible

### 9. Import Processing

During import, elements go through `on_import()`:
- IDs regenerated for uniqueness
- Images downloaded and localized
- Dynamic content processed
- Controls processed through `Control::on_import()`

## Template Library Database Schema

### Post Type: `elementor_library`

**Post Meta:**
- `_elementor_data` - JSON encoded element data
- `_elementor_template_type` - Template type (page, section, etc.)
- `_elementor_edit_mode` - Edit mode (builder)
- `_elementor_version` - Elementor version used
- `_elementor_page_settings` - Page settings array

**Taxonomies:**
- `elementor_library_type` - Template type taxonomy
- `elementor_library_category` - Template categories (user-created)

### Image Imports

**Post Meta on Imported Images:**
- `_elementor_source_image_hash` - SHA1 hash of original URL
- Purpose: Prevent duplicate downloads on re-import

## Error Handling

All tools return standardized error responses:

```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Human readable error message",
    "data": {}
  }
}
```

**Common Error Codes:**
- `elementor_inactive` - Elementor plugin not active
- `permission_denied` - User lacks required capability
- `template_not_found` - Template doesn't exist
- `invalid_type` - Invalid template type
- `save_error` - Failed to save template
- `import_error` - Import operation failed
- `export_error` - Export operation failed

## Permissions

Template operations require specific WordPress capabilities:

- **List Templates**: `edit_posts`
- **Get Template**: `edit_posts`
- **Import Template**: `edit_posts`
- **Export Template**: `edit_post` (for specific template)
- **Save Template**: `publish_posts`

## Integration Notes

### Template Manager Access

```php
$templates_manager = \Elementor\Plugin::$instance->templates_manager;
```

### Source Access

```php
// Get local source
$local_source = $templates_manager->get_source( 'local' );

// Get remote source
$remote_source = $templates_manager->get_source( 'remote' );
```

### Image Import

```php
$import_images = $templates_manager->get_import_images_instance();
$imported = $import_images->import( $attachment_data );
```

### Document System

Templates are documents:
```php
$document = \Elementor\Plugin::$instance->documents->get( $template_id );
$document->save( $data );
```

## Best Practices

1. **Always Regenerate IDs**: Use `replace_elements_ids()` when cloning
2. **Import Images**: Set `import_images: true` for remote templates
3. **Validate Types**: Check template type against document types
4. **Handle Errors**: Always check for `WP_Error` returns
5. **Check Permissions**: Verify user capabilities before operations
6. **Version Awareness**: Consider Elementor version compatibility
7. **Backup Before Import**: Templates override is destructive
8. **Clean Temp Files**: Import processes may create temporary files

## Example Workflows

### Clone and Modify Template

```javascript
// 1. Get original template
const original = await get_template({ template_id: 123, source: 'local' });

// 2. Modify content
const modified = {
  ...original.data,
  title: original.data.title + ' - Copy',
  content: modifyContent(original.data.content)
};

// 3. Save as new template
const newTemplate = await save_template(modified);
```

### Import from Remote Library

```javascript
// 1. List remote templates
const remoteTemplates = await list_templates({ source: 'remote', type: 'page' });

// 2. Import desired template
const imported = await import_template({
  template_id: remoteTemplates.data.templates[0].template_id,
  import_images: true
});
```

### Backup All Local Templates

```javascript
// 1. Get all local templates
const locals = await list_templates({ source: 'local' });

// 2. Export each one
for (const template of locals.data.templates) {
  const exported = await export_template({ template_id: template.template_id });
  // Save exported.data.json to file
  fs.writeFileSync(exported.data.filename, exported.data.json);
}
```

## Dependencies

- **Elementor Plugin**: Required, version 3.0+
- **WordPress**: 5.0+
- **PHP**: 7.0+

## File Structure

```
templates/
├── class-list-templates.php      - List templates tool
├── class-get-template.php        - Get template data tool
├── class-import-template.php     - Import template tool
├── class-export-template.php     - Export template tool
├── class-save-template.php       - Create template tool
└── README.md                     - This documentation
```

## Future Enhancements

Potential additions:
- Bulk template operations
- Template diff comparison
- Template search by content
- Template validation
- Template migration between versions
- Template preview generation
- Template categories management
