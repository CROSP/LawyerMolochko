# Agent 7: Template Library Tools - Implementation Summary

## Mission Completed ✓

Successfully created 5 MCP tools for Elementor template library management with comprehensive documentation and insights.

## Files Created

### Tools Implementation (5 files)

1. **class-list-templates.php** - Tool: `list_templates`
   - Lists templates from local/remote sources
   - Filters by type (page/section/widget) and source
   - Returns formatted template metadata
   - **Lines**: 154

2. **class-get-template.php** - Tool: `get_template`
   - Retrieves complete template data
   - Returns elements, settings, and metadata
   - Works with both local and remote sources
   - **Lines**: 161

3. **class-import-template.php** - Tool: `import_template`
   - Imports from JSON data or remote template ID
   - Handles automatic image importing
   - Processes content through Elementor's pipeline
   - **Lines**: 241

4. **class-export-template.php** - Tool: `export_template`
   - Exports templates to Elementor JSON format
   - Includes version and metadata
   - Returns downloadable JSON string
   - **Lines**: 141

5. **class-save-template.php** - Tool: `save_template`
   - Creates new templates programmatically
   - Validates template types
   - Sets up proper taxonomy and metadata
   - **Lines**: 163

### Documentation (2 files)

6. **README.md** - Comprehensive user guide
   - Tool usage examples
   - Template format documentation
   - Integration patterns
   - Best practices
   - **Lines**: 474

7. **INSIGHTS.md** - Deep technical analysis
   - Architecture discoveries
   - Template format insights
   - Performance considerations
   - Security patterns
   - Undocumented features
   - **Lines**: 586

**Total Lines of Code**: 1,920

## Key Insights Discovered

### 1. Source-Based Architecture

Elementor uses a polymorphic source pattern:
- **Source_Base**: Abstract interface
- **Source_Local**: Database operations on `elementor_library` CPT
- **Source_Remote**: API calls to Elementor.com
- **Manager**: Orchestrator/facade coordinating all sources

This mirrors the repository pattern and provides clean separation.

### 2. Document-Centric Paradigm

Templates are **documents**, not just posts:
```php
$document = Plugin::$instance->documents->get($template_id);
$document->save(['elements' => $content, 'settings' => $settings]);
```

Critical insight: Never manipulate post meta directly - always use Document API.

### 3. Import/Export Pipeline

Templates go through transformation pipelines:

**Export**: `Document → get_export_data() → process('on_export') → JSON`
**Import**: `JSON → prepare_data() → process('on_import') → Document::save()`

Each element and control can define custom `on_export()` and `on_import()` methods.

### 4. Template Data Structure

```json
{
  "content": [           // Recursive element tree
    {
      "id": "abc123",    // Unique random ID
      "elType": "section",
      "settings": {...},
      "elements": [...]   // Nested children
    }
  ],
  "page_settings": {...}, // Document-level settings
  "version": "3.18.0",   // Elementor version
  "title": "Template Name",
  "type": "page"         // Template type
}
```

### 5. Element Hierarchy

```
Section (elType: 'section')
  └─ Column (elType: 'column')
      └─ Widget (elType: 'widget', widgetType: 'heading')
```

This mirrors DOM structure but is framework-agnostic.

### 6. Settings Storage Patterns

**Prefixes Matter**:
- No prefix: Regular setting
- `_` prefix: Internal/meta setting
- `__` prefix: System setting (globals, dynamic tags)

**Responsive Suffixes**:
- No suffix: Desktop (default)
- `_tablet`: Tablet breakpoint
- `_mobile`: Mobile breakpoint

### 7. Image Handling

Images are compound objects:
```json
{
  "image": {
    "url": "https://example.com/image.jpg",
    "id": 123,
    "size": "full"
  }
}
```

**Import Process**:
1. Check hash (`_elementor_source_image_hash`) for duplicates
2. Download remote image via `wp_remote_get()`
3. Upload via `wp_upload_bits()`
4. Create attachment post
5. Generate image sizes
6. Store hash to prevent re-download

### 8. ID Regeneration

Element IDs must be regenerated on clone/import:
```php
$element['id'] = Utils::generate_random_string();
```

**Why**: IDs are used for:
- CSS selectors (`.elementor-element-abc123`)
- Editor state management
- Animation/interaction targeting
- DOM manipulation

### 9. Dynamic Content System

Templates support dynamic tags:
```json
{
  "text": "{{user_name}}",
  "__dynamic__": {
    "text": "post_title"
  }
}
```

Format: `{{tag_name[key=value]}}` or stored in `__dynamic__` meta.

### 10. Global Settings References

Templates can reference global styles:
```json
{
  "__globals__": {
    "typography_font_family": "globals/typography?id=primary"
  }
}
```

Format: `globals/{type}?id={id}` (types: colors, typography)

## Template Format Deep Dive

### Element Object Structure

Every element follows this pattern:
```json
{
  "id": "abc123",              // Unique ID (regenerated on import)
  "elType": "section|column|widget",
  "settings": {                // Element-specific settings
    "setting_name": "value",
    "_margin": {...},          // Internal settings
    "__globals__": {...}       // Global references
  },
  "elements": [...]            // Nested children (recursive)
}
```

### Widget Element

Widgets add `widgetType`:
```json
{
  "id": "def456",
  "elType": "widget",
  "widgetType": "heading",     // Widget identifier
  "settings": {
    "title": "Hello World",
    "size": "h2"
  },
  "elements": []               // Widgets don't have children
}
```

### Page Settings

Document-level configuration:
```json
{
  "page_layout": "default|boxed|full_width",
  "template": "elementor_canvas|elementor_header_footer",
  "hide_title": "yes|no",
  "custom_css": "...",
  "page_transitions": {...}
}
```

### Responsive Values

Settings with breakpoint variations:
```json
{
  "padding": {
    "top": "20",
    "right": "20",
    "bottom": "20",
    "left": "20",
    "unit": "px"
  },
  "padding_tablet": {
    "top": "10",
    "unit": "px"
  },
  "padding_mobile": {
    "top": "5",
    "unit": "px"
  }
}
```

## Tool Usage Patterns

### Pattern 1: List and Filter

```javascript
// Get all page templates from local library
const result = await mcp.call('list_templates', {
  type: 'page',
  source: 'local'
});

console.log(`Found ${result.data.count} page templates`);
```

### Pattern 2: Clone Template

```javascript
// 1. Get original template
const original = await mcp.call('get_template', {
  template_id: 123,
  source: 'local'
});

// 2. Modify and save as new
const cloned = await mcp.call('save_template', {
  title: original.data.title + ' - Copy',
  type: original.data.type,
  content: original.data.content,
  page_settings: original.data.page_settings
});
```

### Pattern 3: Import from Remote

```javascript
// 1. List remote templates
const remote = await mcp.call('list_templates', {
  source: 'remote',
  type: 'section'
});

// 2. Import specific template
const imported = await mcp.call('import_template', {
  template_id: remote.data.templates[0].template_id,
  import_images: true
});
```

### Pattern 4: Backup Templates

```javascript
// 1. Get all local templates
const locals = await mcp.call('list_templates', {
  source: 'local'
});

// 2. Export each one
for (const template of locals.data.templates) {
  const exported = await mcp.call('export_template', {
    template_id: template.template_id
  });

  // Save to file
  fs.writeFileSync(
    exported.data.filename,
    exported.data.json
  );
}
```

### Pattern 5: Create from Scratch

```javascript
// Create minimal page template
const newTemplate = await mcp.call('save_template', {
  title: 'New Page',
  type: 'page',
  content: [
    {
      elType: 'section',
      settings: {
        layout: 'boxed'
      },
      elements: [
        {
          elType: 'column',
          settings: {
            _column_size: 100
          },
          elements: [
            {
              elType: 'widget',
              widgetType: 'heading',
              settings: {
                title: 'Welcome',
                size: 'h1'
              }
            }
          ]
        }
      ]
    }
  ]
});
```

## Integration with Existing Tools

These template tools complement the existing MCP tools:

### With Widget Tools
```javascript
// 1. Get widget schema
const schema = await mcp.call('get_widget_schema', {
  widget_type: 'heading'
});

// 2. Create widget with settings
const widget = {
  elType: 'widget',
  widgetType: 'heading',
  settings: {
    title: 'My Heading',
    ...schema.data.default_settings
  }
};

// 3. Save in template
await mcp.call('save_template', {
  title: 'Heading Template',
  type: 'widget',
  content: [widget]
});
```

### With Document Tools
```javascript
// 1. Get template
const template = await mcp.call('get_template', {
  template_id: 123
});

// 2. Apply to document
await mcp.call('update_document', {
  post_id: 456,
  content: template.data.content
});
```

## Performance Considerations

### Image Import
- **I/O Intensive**: Each image downloads, uploads, processes
- **Can Timeout**: Large templates may exceed PHP execution time
- **Recommendation**: Set `import_images: false` for testing

### Content Processing
- **Recursive**: Iterates entire element tree
- **Complexity**: O(n) where n = elements + controls
- **Optimization**: Process in chunks for very large templates

### Database Operations
- **Multiple Writes**: Post + meta + taxonomy + cache
- **Hook Execution**: Multiple WordPress hooks fire
- **Tip**: Use `wp_defer_term_counting()` for bulk operations

## Security Implementation

All tools implement security at multiple levels:

1. **Elementor Check**: Verify plugin is active
2. **Capability Check**: Verify user has required permissions
3. **Input Validation**: Schema-based validation
4. **Permission Context**: Check specific post permissions
5. **Data Sanitization**: Multiple sanitization layers
6. **Error Handling**: Standardized error responses

### Permission Requirements

- **list_templates**: `edit_posts`
- **get_template**: `edit_posts`
- **import_template**: `edit_posts`
- **export_template**: `edit_post` (specific template)
- **save_template**: `publish_posts`

## Error Handling

All tools return standardized responses:

**Success**:
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed"
}
```

**Error**:
```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Human readable message",
    "data": {}
  }
}
```

## Testing Recommendations

### Unit Tests
1. Test each tool with valid inputs
2. Test error handling (missing params, invalid types)
3. Test permission checks
4. Test with/without Elementor active

### Integration Tests
1. Full import/export cycle
2. Clone templates
3. Cross-source operations (remote → local)
4. Image import functionality
5. Large template handling

### Edge Cases
1. Empty templates
2. Templates with dynamic content
3. Templates with global settings
4. Templates from different Elementor versions
5. Templates with missing widgets

## Future Enhancement Ideas

1. **Template Diff**: Compare two template versions
2. **Template Search**: Search by content/settings
3. **Template Validation**: Validate against schema
4. **Template Migration**: Migrate between Elementor versions
5. **Bulk Operations**: Import/export multiple templates
6. **Template Analytics**: Analyze template complexity
7. **Template Optimization**: Remove unused settings
8. **Template Preview**: Generate preview images

## Architectural Notes

### Design Decisions

1. **Extended Base_Tool**: Consistent with existing tools
2. **Source-Based Access**: Uses Elementor's source system
3. **Document API**: Leverages Document abstraction
4. **Error First**: Always check WP_Error
5. **Permission Context**: Check specific post permissions

### Code Quality

- **PSR-2 Compliant**: Follows WordPress coding standards
- **Well Documented**: Comprehensive PHPDoc blocks
- **Type Safe**: Parameter validation
- **Error Handling**: Comprehensive error checking
- **Hooks**: Before/after execution hooks

### Maintainability

- **Single Responsibility**: Each tool does one thing
- **Separation of Concerns**: Tools, validation, formatting separated
- **Extensible**: Easy to add new tools
- **Testable**: Can be unit tested
- **Documented**: README and INSIGHTS provide context

## Critical Files Analyzed

1. **manager.php** (1,302 lines)
   - Template manager orchestration
   - Source registration
   - Import/export coordination
   - Ajax action handling

2. **local.php** (1,811 lines)
   - Local template CRUD operations
   - Template export/import logic
   - Post type and taxonomy registration
   - Admin interface integration

3. **remote.php** (325 lines)
   - Remote template fetching
   - API communication
   - Template caching
   - Data transformation

4. **base.php** (544 lines)
   - Source interface definition
   - Common functionality
   - Processing pipelines
   - Helper methods

5. **class-import-images.php** (282 lines)
   - Image downloading
   - Hash-based deduplication
   - Attachment creation
   - SVG sanitization

## Conclusion

The Template Library Tools provide complete template management capabilities through MCP:

✅ **List** templates from any source with filtering
✅ **Get** complete template data with all metadata
✅ **Import** from JSON or remote sources with image handling
✅ **Export** to portable JSON format
✅ **Save** new templates programmatically

Combined with comprehensive documentation and deep insights into Elementor's template system, these tools enable AI agents to effectively manage Elementor templates.

The implementation follows Elementor's architecture patterns, respects security boundaries, and provides robust error handling - making it production-ready for real-world usage.

---

**Agent 7 signing off** - Template Library Tools complete! 🎉
