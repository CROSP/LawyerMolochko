# Widget Management MCP Tools

Complete implementation of Elementor widget management tools for the MCP server.

## Overview

This directory contains four MCP tools that provide comprehensive access to Elementor's widget system:

1. **list_widgets** - Get all available widget types
2. **get_widget_schema** - Get complete widget schema with controls
3. **create_widget_instance** - Generate properly formatted widget instances
4. **get_widget_controls** - Get detailed control information

## Files

- `class-list-widgets.php` - List all widgets with filtering
- `class-get-widget-schema.php` - Complete widget schema retrieval
- `class-create-widget-instance.php` - Widget instance creation with validation
- `class-get-widget-controls.php` - Detailed control information

## Usage Examples

### 1. List All Widgets

```json
{
  "tool": "list_widgets",
  "args": {}
}
```

**With Filters:**
```json
{
  "tool": "list_widgets",
  "args": {
    "category": "basic",
    "search": "text"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "button": {
      "name": "button",
      "title": "Button",
      "icon": "eicon-button",
      "categories": ["basic"],
      "keywords": ["button", "link", "cta"],
      "type": "widget",
      "show_in_panel": true,
      "is_dynamic_content": false
    },
    "heading": {
      "name": "heading",
      "title": "Heading",
      "icon": "eicon-t-letter",
      "categories": ["basic"],
      "keywords": ["heading", "title", "text"],
      "type": "widget"
    }
  }
}
```

### 2. Get Widget Schema

```json
{
  "tool": "get_widget_schema",
  "args": {
    "widget_name": "button",
    "include_common_controls": true
  }
}
```

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "name": "button",
    "title": "Button",
    "icon": "eicon-button",
    "categories": ["basic"],
    "keywords": ["button", "link"],
    "type": "widget",
    "controls": {
      "text": {
        "type": "text",
        "label": "Text",
        "default": "Click here",
        "placeholder": "Enter button text"
      },
      "link": {
        "type": "url",
        "label": "Link",
        "dynamic": { "active": true },
        "default": { "url": "" }
      }
    },
    "defaults": {
      "text": "Click here",
      "link": { "url": "" }
    },
    "sections": {
      "section_button": {
        "label": "Button",
        "tab": "content",
        "controls": ["text", "link", "size"]
      }
    }
  }
}
```

### 3. Create Widget Instance

```json
{
  "tool": "create_widget_instance",
  "args": {
    "widget_type": "button",
    "settings": {
      "text": "Learn More",
      "link": {
        "url": "https://example.com",
        "is_external": true
      },
      "button_type": "primary"
    },
    "validate_settings": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "element": {
      "id": "a1b2c3d4",
      "elType": "widget",
      "widgetType": "button",
      "settings": {
        "text": "Learn More",
        "link": {
          "url": "https://example.com",
          "is_external": true
        },
        "button_type": "primary"
      }
    },
    "metadata": {
      "widget_name": "button",
      "widget_title": "Button",
      "categories": ["basic"]
    }
  }
}
```

### 4. Get Widget Controls

```json
{
  "tool": "get_widget_controls",
  "args": {
    "widget_name": "heading",
    "include_common": true,
    "tab": "content"
  }
}
```

**Get Specific Control:**
```json
{
  "tool": "get_widget_controls",
  "args": {
    "widget_name": "heading",
    "control_name": "title"
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "controls": {
      "title": {
        "name": "title",
        "type": "textarea",
        "label": "Title",
        "default": "Add Your Heading Text Here",
        "placeholder": "Enter your title",
        "dynamic": { "active": true },
        "ai": { "type": "text" },
        "metadata": {
          "type": "textarea",
          "is_input": true,
          "value_type": "string"
        }
      },
      "header_size": {
        "name": "header_size",
        "type": "select",
        "label": "HTML Tag",
        "options": {
          "h1": "H1",
          "h2": "H2",
          "h3": "H3"
        },
        "default": "h2"
      }
    },
    "organized_sections": {
      "section_title": {
        "label": "Heading",
        "tab": "content",
        "controls": {
          "title": { /* ... */ },
          "header_size": { /* ... */ }
        }
      }
    },
    "total_controls": 15
  }
}
```

## Elementor API Insights & Quirks

### Widget Manager Architecture

**Location:** `elementor/includes/managers/widgets.php`

#### Key Methods:

1. **`get_widget_types($widget_name = null)`**
   - Returns array of all registered widget instances
   - Each widget is a `Widget_Base` object
   - Automatically calls `init_widgets()` on first access
   - Can filter by specific widget name

2. **Widget Registration Flow:**
   ```php
   // Widgets are registered during init_widgets()
   $this->_widget_types[$widget_name] = $widget_instance;

   // Built-in widgets are in: elementor/includes/widgets/
   // Custom widgets hook into: 'elementor/widgets/register'
   ```

3. **Widget Types Array Structure:**
   - Key: Widget name (e.g., 'button', 'heading')
   - Value: Widget_Base instance
   - Accessible globally via: `Plugin::$instance->widgets_manager`

### Widget Base Class

**Location:** `elementor/includes/base/widget-base.php`

#### Important Properties:

1. **Type Identification:**
   - `get_type()` - Always returns 'widget'
   - `get_name()` - Unique widget identifier
   - `get_title()` - Display name
   - `get_icon()` - Icon class (eicon-*)
   - `get_categories()` - Array of categories
   - `get_keywords()` - Search keywords

2. **Control System:**
   - Widgets extend `Controls_Stack` which extends `Element_Base`
   - Controls are registered in `register_controls()` method
   - Controls are stored in stack structure

#### Widget Stack Structure:

The `get_stack()` method returns:
```php
[
    'controls' => [
        'control_name' => [
            'type' => 'text',
            'label' => 'Label',
            'default' => 'value',
            // ... more properties
        ]
    ],
    'tabs' => [
        'tab_content' => 'Content',
        'tab_style' => 'Style',
        'tab_advanced' => 'Advanced'
    ]
]
```

### Common Controls Integration

**QUIRK:** Widgets have two sets of controls:

1. **Widget-specific controls** - Defined in the widget class
2. **Common controls** - Shared controls (Advanced tab)

Access via:
```php
$widget->get_stack(true);  // With common controls
$widget->get_stack(false); // Without common controls
```

**Common widget classes:**
- `Widget_Common_Base` - Base common controls
- `Widget_Common` - Standard common controls (has inner wrapper)
- `Widget_Common_Optimized` - Optimized markup version

### Control Types Deep Dive

**Location:** Controls are defined in `elementor/includes/controls/`

#### Primary Control Types:

1. **Text Input Controls:**
   - `text` - Single line text
   - `textarea` - Multi-line text
   - `wysiwyg` - Rich text editor
   - `code` - Code editor

2. **Choice Controls:**
   - `select` - Dropdown
   - `select2` - Advanced dropdown
   - `choose` - Button group
   - `switcher` - On/off toggle

3. **Media Controls:**
   - `media` - Single image/file
   - `gallery` - Multiple images
   - `icon` - Icon picker
   - `icons` - Multiple icons

4. **Dimension Controls:**
   - `slider` - Numeric slider with units
   - `dimensions` - Top/Right/Bottom/Left
   - `color` - Color picker
   - `url` - URL with options

5. **Group Controls:**
   - `typography` - Font family, size, weight, etc.
   - `text_shadow` - Text shadow properties
   - `box_shadow` - Box shadow properties
   - `border` - Border width, color, radius
   - `background` - Background type, color, image, gradient

6. **Structural Controls:**
   - `section` - Section header
   - `tab` - Tab separator
   - `divider` - Visual separator
   - `heading` - Sub-heading
   - `raw_html` - Custom HTML

7. **Complex Controls:**
   - `repeater` - Repeatable fields group
   - `font` - Font selector
   - `image_dimensions` - Image size controls
   - `date_time` - Date/time picker

### Control Properties Reference

**Standard Properties:**
- `type` - Control type identifier
- `label` - Display label
- `default` - Default value
- `placeholder` - Placeholder text
- `description` - Help text
- `separator` - Visual separator (before, after, none)
- `show_label` - Whether to show label
- `label_block` - Full width label

**Conditional Display:**
- `condition` - Simple conditions: `['field' => 'value']`
- `conditions` - Complex conditions with AND/OR logic
  ```php
  'conditions' => [
      'relation' => 'or',
      'terms' => [
          ['name' => 'field1', 'value' => 'value1'],
          ['name' => 'field2', 'operator' => '!==', 'value' => 'value2']
      ]
  ]
  ```

**Dynamic Content:**
- `dynamic` - Enable dynamic tags: `['active' => true]`
- `ai` - AI integration: `['type' => 'text']`

**Responsive:**
- `responsive` - Device-specific values: `['active' => true]`
- `selectors` - CSS selectors for live preview
  ```php
  'selectors' => [
      '{{WRAPPER}} .element' => 'property: {{VALUE}};'
  ]
  ```

**Validation:**
- `min` - Minimum value (numbers)
- `max` - Maximum value (numbers)
- `step` - Increment step
- `options` - Valid options (select/choose)

**Advanced:**
- `frontend_available` - Make available in frontend JS
- `render_type` - When to re-render (ui, template, none)
- `prefix_class` - Add class to wrapper
- `return_value` - Value when active (switcher)

### Widget Data Structure (JSON)

**How Elementor Stores Widgets in `_elementor_data`:**

```json
{
  "id": "a1b2c3d4",
  "elType": "widget",
  "widgetType": "button",
  "settings": {
    "text": "Click here",
    "link": {
      "url": "https://example.com",
      "is_external": true,
      "nofollow": false
    },
    "_element_id": "",
    "_css_classes": ""
  }
}
```

**Element Types:**
- `widget` - Widget element
- `section` - Section container
- `column` - Column element
- `container` - Container (new)

### Button Widget Example Analysis

**File:** `elementor/includes/widgets/button.php`

**Key Observations:**

1. **Uses Trait:**
   ```php
   use Elementor\Includes\Widgets\Traits\Button_Trait;
   ```
   Controls are registered in trait method `register_button_content_controls()`

2. **Categories:**
   ```php
   return ['basic']; // Not 'general'
   ```

3. **Optimized Markup:**
   ```php
   has_widget_inner_wrapper() {
       return !Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
   }
   ```

4. **Dynamic Content:**
   ```php
   is_dynamic_content(): bool {
       return false; // Static widget
   }
   ```

### Heading Widget Example Analysis

**File:** `elementor/includes/widgets/heading.php`

**Advanced Features:**

1. **Content Sanitization:**
   - Implements `Sanitizable` interface
   - `sanitize()` method removes data attributes and certain tags
   - Prevents XSS while allowing HTML

2. **AI Integration:**
   ```php
   'ai' => [
       'type' => 'text',
   ]
   ```

3. **Dynamic Tags:**
   ```php
   'dynamic' => [
       'active' => true,
   ]
   ```

4. **Responsive Control:**
   ```php
   'responsive' => true,
   'selectors' => [
       '{{WRAPPER}}' => 'text-align: {{VALUE}};',
   ]
   ```

5. **Group Controls:**
   ```php
   $this->add_group_control(
       Group_Control_Typography::get_type(),
       [
           'name' => 'typography',
           'global' => [
               'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
           ],
           'selector' => '{{WRAPPER}} .elementor-heading-title',
       ]
   );
   ```

### Controls Stack Architecture

**File:** `elementor/includes/base/controls-stack.php`

**How Controls are Organized:**

1. **Sections:**
   - Define with `start_controls_section()` / `end_controls_section()`
   - Each section has a tab (content, style, advanced)

2. **Controls:**
   - Added with `add_control()` or `add_responsive_control()`
   - Must be within a section
   - Stored in internal stack

3. **Tabs:**
   - `Controls_Manager::TAB_CONTENT`
   - `Controls_Manager::TAB_STYLE`
   - `Controls_Manager::TAB_ADVANCED`
   - `Controls_Manager::TAB_LAYOUT`

### Critical API Quirks & Gotchas

#### 1. Widget Initialization
**QUIRK:** Widgets are lazy-loaded
- First call to `get_widget_types()` triggers `init_widgets()`
- Widgets registered via hooks may not be available immediately
- Always verify widget exists before using

#### 2. Common Controls
**QUIRK:** Two widget base classes
- `Widget_Common` - Has `elementor-widget-container` wrapper
- `Widget_Common_Optimized` - No inner wrapper (e_optimized_markup experiment)
- Choice depends on experiment state
- Affects DOM structure and CSS targeting

#### 3. Control Defaults
**QUIRK:** Multiple sources of defaults
- Control-level default
- Widget-level default via `get_default_data()`
- Global defaults from kit
- Dynamic tag defaults
- Order of precedence is complex

#### 4. URL Control Format
**QUIRK:** URL is an object, not string
```php
[
    'url' => 'https://example.com',
    'is_external' => true,
    'nofollow' => false,
    'custom_attributes' => ''
]
```

#### 5. Media Control Format
**QUIRK:** Returns both ID and URL
```php
[
    'id' => 123,
    'url' => 'https://example.com/image.jpg'
]
```

#### 6. Repeater Control
**QUIRK:** Array of arrays
```php
[
    [
        'field1' => 'value1',
        'field2' => 'value2'
    ],
    [
        'field1' => 'value3',
        'field2' => 'value4'
    ]
]
```

#### 7. Slider Control
**QUIRK:** Object with size and unit
```php
[
    'size' => 50,
    'unit' => 'px'
]
```

#### 8. Dimensions Control
**QUIRK:** Object with linked flag
```php
[
    'top' => 10,
    'right' => 20,
    'bottom' => 10,
    'left' => 20,
    'unit' => 'px',
    'isLinked' => false
]
```

#### 9. Typography Control (Group)
**QUIRK:** Generates multiple sub-controls
- `typography_typography` - Enable/disable
- `typography_font_family`
- `typography_font_size`
- `typography_font_weight`
- `typography_text_transform`
- `typography_font_style`
- `typography_text_decoration`
- `typography_line_height`
- `typography_letter_spacing`
- `typography_word_spacing`

#### 10. Condition Operators
**Available operators:**
- `=` or `==` - Equals (default)
- `!==` - Not equals
- `in` - In array
- `!in` - Not in array
- `contains` - Contains substring
- `!contains` - Doesn't contain
- `<` - Less than
- `>` - Greater than
- `<=` - Less than or equal
- `>=` - Greater than or equal

#### 11. Selector Placeholders
**Special placeholders:**
- `{{WRAPPER}}` - Widget wrapper element
- `{{VALUE}}` - Control value
- `{{URL}}` - Media URL
- `{{UNIT}}` - Size unit

#### 12. Global Values
**QUIRK:** Can reference global colors/fonts
```php
// Color control
[
    'global' => [
        'default' => Global_Colors::COLOR_PRIMARY
    ]
]

// Typography
[
    'global' => [
        'default' => Global_Typography::TYPOGRAPHY_PRIMARY
    ]
]
```

#### 13. Experiments/Features
**QUIRK:** Widget behavior changes based on active experiments
- Check: `Plugin::$instance->experiments->is_feature_active('feature_name')`
- Common experiments:
  - `e_optimized_markup` - Affects inner wrapper
  - `container` - New container element
  - `nested_elements` - Nested tabs/accordion
  - `atomic_widgets` - New widget architecture

#### 14. Widget ID Generation
**QUIRK:** IDs are hex strings
- Use `dechex(mt_rand())` for consistency
- Must be unique within document
- Length varies (7-8 characters typically)

#### 15. Element Path
**QUIRK:** Widgets need proper nesting
- Widget → Column → Section (classic)
- Widget → Container (new)
- Invalid nesting causes render issues

## Validation Best Practices

### 1. Always Verify Elementor
```php
$verify = $this->verify_elementor();
if (is_wp_error($verify)) {
    return $this->error($verify);
}
```

### 2. Validate Widget Exists
```php
$widget = $this->get_widget_instance($widget_name);
if (is_wp_error($widget)) {
    return $this->error($widget);
}
```

### 3. Sanitize All Settings
```php
$sanitized_settings = $this->sanitize_settings($settings);
```

### 4. Type-Specific Validation
- Numbers: Check min/max bounds
- Select: Verify option exists
- URLs: Validate format
- Media: Verify attachment exists
- Colors: Check format (hex, rgb, etc.)

### 5. Conditional Logic
- Validate condition fields exist
- Check operator validity
- Ensure condition values are appropriate

## Error Handling

All tools return standardized responses:

**Success:**
```json
{
  "success": true,
  "data": { /* ... */ },
  "message": "Optional success message"
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Error description",
    "data": { /* Optional error data */ }
  }
}
```

## Performance Considerations

1. **Widget Type Caching:**
   - Widgets are cached after first load
   - No need to cache in MCP layer

2. **Stack Generation:**
   - `get_stack()` can be expensive
   - Cache results when possible
   - Use `get_stack(false)` if common controls not needed

3. **Control Extraction:**
   - Large widgets have 50+ controls
   - Filter early to reduce data transfer
   - Use specific control retrieval when possible

## Testing Recommendations

1. **Test with Core Widgets:**
   - button, heading, image, text-editor
   - These are stable and well-documented

2. **Test Pro Widgets:**
   - May have different structure
   - Check availability before use

3. **Test Theme Builder Widgets:**
   - Different context (archive, single, etc.)
   - May require specific conditions

4. **Test with Experiments:**
   - Enable/disable e_optimized_markup
   - Test container vs section structure

## Future Enhancements

1. **Widget Validation:**
   - Deep validation of all control types
   - Context-aware validation
   - Cross-control validation

2. **Widget Templates:**
   - Pre-configured widget instances
   - Common patterns library

3. **Widget Cloning:**
   - Clone existing widget instances
   - Preserve all settings

4. **Bulk Operations:**
   - Create multiple widgets at once
   - Update multiple widgets

5. **Widget Search:**
   - Search by control values
   - Find widgets using specific settings

## Related Documentation

- Elementor Developer Documentation: https://developers.elementor.com/
- Widget API: https://developers.elementor.com/widgets/
- Controls Reference: https://developers.elementor.com/controls/
- Dynamic Tags: https://developers.elementor.com/dynamic-tags/

## Support & Issues

For issues or questions about these tools:
1. Check this documentation first
2. Examine the Elementor source code
3. Test with core widgets before custom widgets
4. Verify Elementor version compatibility
