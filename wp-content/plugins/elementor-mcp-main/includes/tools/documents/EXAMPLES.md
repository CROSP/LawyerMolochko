# Document Management Tools - Code Examples

Quick reference examples for using the Document Management MCP tools.

## Table of Contents
- [Get Document](#get-document)
- [Save Document](#save-document)
- [Get Elementor Data](#get-elementor-data)
- [Update Elementor Data](#update-elementor-data)
- [Create Elementor Page](#create-elementor-page)
- [Common Workflows](#common-workflows)

---

## Get Document

### Basic Usage

**Request:**
```json
{
  "tool": "get_document",
  "arguments": {
    "post_id": 123
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "post": {
      "ID": 123,
      "post_title": "About Us",
      "post_status": "publish",
      "post_type": "page"
    },
    "elements": [
      {
        "id": "abc123",
        "elType": "section",
        "elements": [...]
      }
    ],
    "settings": {},
    "metadata": {
      "template_type": "wp-page",
      "is_built_with_elementor": true
    }
  }
}
```

### Use Cases
- Retrieve complete page structure
- Inspect element hierarchy
- Check page settings
- Verify Elementor is enabled

---

## Save Document

### Save Elements Only

**Request:**
```json
{
  "tool": "save_document",
  "arguments": {
    "post_id": 123,
    "elements": [
      {
        "id": "section1",
        "elType": "section",
        "settings": {
          "layout": "boxed"
        },
        "elements": [
          {
            "id": "column1",
            "elType": "column",
            "settings": {
              "_column_size": 100
            },
            "elements": [
              {
                "id": "heading1",
                "elType": "widget",
                "widgetType": "heading",
                "settings": {
                  "title": "Welcome to Our Site",
                  "header_size": "h1"
                }
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### Save Elements and Settings

**Request:**
```json
{
  "tool": "save_document",
  "arguments": {
    "post_id": 123,
    "elements": [
      {
        "id": "section1",
        "elType": "section",
        "elements": [...]
      }
    ],
    "settings": {
      "post_title": "New Page Title",
      "custom_css": ".my-class { color: blue; }",
      "page_template": "elementor_canvas"
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "status": "publish",
    "post_modified": "2025-11-22 14:30:00",
    "elements_count": 1
  },
  "message": "Document saved successfully"
}
```

### Common Element Types

#### Text Editor Widget
```json
{
  "id": "text1",
  "elType": "widget",
  "widgetType": "text-editor",
  "settings": {
    "editor": "<p>Your content here</p>"
  }
}
```

#### Image Widget
```json
{
  "id": "image1",
  "elType": "widget",
  "widgetType": "image",
  "settings": {
    "image": {
      "url": "https://example.com/image.jpg",
      "id": 456
    },
    "image_size": "full"
  }
}
```

#### Button Widget
```json
{
  "id": "button1",
  "elType": "widget",
  "widgetType": "button",
  "settings": {
    "text": "Click Me",
    "link": {
      "url": "https://example.com",
      "is_external": true
    },
    "button_type": "primary"
  }
}
```

---

## Get Elementor Data

### Basic Usage

**Request:**
```json
{
  "tool": "get_elementor_data",
  "arguments": {
    "post_id": 123
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "has_elementor_data": true,
    "is_built_with_elementor": true,
    "raw_data": "[{\"id\":\"abc\",\"elType\":\"section\"...}]",
    "decoded_data": [
      {
        "id": "abc",
        "elType": "section"
      }
    ],
    "elements_count": 3
  }
}
```

### Use Cases
- Direct database inspection
- Data export
- Debugging element structure
- JSON validation

---

## Update Elementor Data

### Update with Array

**Request:**
```json
{
  "tool": "update_elementor_data",
  "arguments": {
    "post_id": 123,
    "data": [
      {
        "id": "new_section",
        "elType": "section",
        "elements": [
          {
            "id": "new_column",
            "elType": "column",
            "elements": []
          }
        ]
      }
    ]
  }
}
```

### Update with JSON String

**Request:**
```json
{
  "tool": "update_elementor_data",
  "arguments": {
    "post_id": 123,
    "data": "[{\"id\":\"new_section\",\"elType\":\"section\",\"elements\":[]}]"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 123,
    "updated": true,
    "data_length": 156,
    "elements_count": 1
  }
}
```

---

## Create Elementor Page

### Create Draft Page

**Request:**
```json
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "New Landing Page",
    "content_type": "page",
    "status": "draft"
  }
}
```

### Create Published Post

**Request:**
```json
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "New Blog Post",
    "content_type": "post",
    "status": "publish"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 456,
    "title": "New Landing Page",
    "post_type": "page",
    "post_status": "draft",
    "edit_url": "http://example.com/wp-admin/post.php?post=456&action=elementor",
    "preview_url": "http://example.com/?elementor-preview=456",
    "permalink": "http://example.com/new-landing-page/",
    "elementor_enabled": true
  }
}
```

---

## Common Workflows

### 1. Create Complete Landing Page

```json
// Step 1: Create the page
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Product Launch",
    "content_type": "page",
    "status": "draft"
  }
}

// Step 2: Add content (use returned post_id)
{
  "tool": "save_document",
  "arguments": {
    "post_id": 456,
    "elements": [
      {
        "id": "hero_section",
        "elType": "section",
        "settings": {
          "background_background": "classic",
          "background_color": "#000000"
        },
        "elements": [
          {
            "id": "hero_column",
            "elType": "column",
            "elements": [
              {
                "id": "hero_heading",
                "elType": "widget",
                "widgetType": "heading",
                "settings": {
                  "title": "Revolutionary Product",
                  "header_size": "h1",
                  "align": "center"
                }
              },
              {
                "id": "hero_button",
                "elType": "widget",
                "widgetType": "button",
                "settings": {
                  "text": "Pre-Order Now",
                  "align": "center"
                }
              }
            ]
          }
        ]
      }
    ],
    "settings": {
      "page_template": "elementor_canvas"
    }
  }
}
```

### 2. Clone Existing Page

```json
// Step 1: Get original page
{
  "tool": "get_document",
  "arguments": {
    "post_id": 123
  }
}

// Step 2: Create new page
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Product Launch (Copy)",
    "content_type": "page",
    "status": "draft"
  }
}

// Step 3: Copy elements (use elements from step 1, post_id from step 2)
{
  "tool": "save_document",
  "arguments": {
    "post_id": 789,
    "elements": [ /* elements from step 1 */ ],
    "settings": { /* settings from step 1 */ }
  }
}
```

### 3. Bulk Update Element Settings

```json
// Step 1: Get current document
{
  "tool": "get_document",
  "arguments": {
    "post_id": 123
  }
}

// Step 2: Modify elements in your code
// For example, change all heading colors to blue

// Step 3: Save updated elements
{
  "tool": "save_document",
  "arguments": {
    "post_id": 123,
    "elements": [ /* modified elements */ ]
  }
}
```

### 4. Create Template Library

```json
// Create multiple pages with different layouts

// Homepage Template
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Template: Homepage",
    "content_type": "page",
    "status": "draft"
  }
}

// About Page Template
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Template: About",
    "content_type": "page",
    "status": "draft"
  }
}

// Contact Page Template
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Template: Contact",
    "content_type": "page",
    "status": "draft"
  }
}
```

### 5. Export/Import Workflow

```json
// Export from Site A
{
  "tool": "get_elementor_data",
  "arguments": {
    "post_id": 123
  }
}
// Save the decoded_data to a file

// Import to Site B
// Step 1: Create new page
{
  "tool": "create_elementor_page",
  "arguments": {
    "title": "Imported Page",
    "content_type": "page",
    "status": "draft"
  }
}

// Step 2: Import data
{
  "tool": "update_elementor_data",
  "arguments": {
    "post_id": 456,
    "data": [ /* data from exported file */ ]
  }
}
```

### 6. Content Migration

```json
// Migrate content from old page to new page

// Step 1: Get old page content
{
  "tool": "get_document",
  "arguments": {
    "post_id": 100
  }
}

// Step 2: Update new page with old content
{
  "tool": "save_document",
  "arguments": {
    "post_id": 200,
    "elements": [ /* elements from old page */ ]
  }
}
```

---

## Error Handling Examples

### Post Not Found
```json
{
  "success": false,
  "error": {
    "code": "post_not_found",
    "message": "Post with ID 999 does not exist",
    "data": {}
  }
}
```

### Permission Denied
```json
{
  "success": false,
  "error": {
    "code": "permission_denied",
    "message": "You do not have permission to edit this document",
    "data": {}
  }
}
```

### Invalid Elements
```json
{
  "success": false,
  "error": {
    "code": "invalid_elements",
    "message": "Element at index 0 is missing elType property",
    "data": {
      "element": {"id": "test"}
    }
  }
}
```

### Elementor Not Enabled
```json
{
  "success": false,
  "error": {
    "code": "elementor_not_enabled",
    "message": "This post does not have Elementor enabled",
    "data": {
      "edit_mode": "",
      "hint": "Use create_elementor_page tool to enable Elementor on this post"
    }
  }
}
```

---

## Advanced Examples

### Multi-Column Layout

```json
{
  "tool": "save_document",
  "arguments": {
    "post_id": 123,
    "elements": [
      {
        "id": "section1",
        "elType": "section",
        "settings": {
          "structure": "20",
          "gap": "default"
        },
        "elements": [
          {
            "id": "col1",
            "elType": "column",
            "settings": {
              "_column_size": 50
            },
            "elements": [
              {
                "id": "widget1",
                "elType": "widget",
                "widgetType": "text-editor",
                "settings": {
                  "editor": "Left column content"
                }
              }
            ]
          },
          {
            "id": "col2",
            "elType": "column",
            "settings": {
              "_column_size": 50
            },
            "elements": [
              {
                "id": "widget2",
                "elType": "widget",
                "widgetType": "text-editor",
                "settings": {
                  "editor": "Right column content"
                }
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### Section with Background

```json
{
  "id": "section_bg",
  "elType": "section",
  "settings": {
    "background_background": "classic",
    "background_color": "#f5f5f5",
    "background_image": {
      "url": "https://example.com/bg.jpg",
      "id": 789
    },
    "background_position": "center center",
    "background_size": "cover"
  },
  "elements": [...]
}
```

### Responsive Settings

```json
{
  "id": "responsive_heading",
  "elType": "widget",
  "widgetType": "heading",
  "settings": {
    "title": "Responsive Heading",
    "typography_typography": "custom",
    "typography_font_size": {
      "size": 48,
      "unit": "px"
    },
    "typography_font_size_tablet": {
      "size": 36,
      "unit": "px"
    },
    "typography_font_size_mobile": {
      "size": 24,
      "unit": "px"
    }
  }
}
```

---

## Tips and Best Practices

1. **Always get before save**: Retrieve current data before modifications
2. **Preserve IDs**: Keep element IDs when updating to maintain references
3. **Validate structure**: Ensure all required fields are present
4. **Handle errors**: Check response success before proceeding
5. **Clear cache**: Tools automatically clear cache, but verify in production
6. **Use drafts**: Create/update as draft first, publish when ready
7. **Test permissions**: Verify user capabilities before operations
8. **Backup data**: Get current data before major updates

---

## Testing Commands

```bash
# Test create page
create_elementor_page {"title":"Test Page","content_type":"page","status":"draft"}

# Test get document
get_document {"post_id":123}

# Test save simple content
save_document {"post_id":123,"elements":[{"id":"s1","elType":"section","elements":[]}]}

# Test get raw data
get_elementor_data {"post_id":123}

# Test update data
update_elementor_data {"post_id":123,"data":[{"id":"s1","elType":"section","elements":[]}]}
```
