# Widget Tools Quick Reference

## Tool Index

| Tool | Purpose | Required Args | Optional Args |
|------|---------|---------------|---------------|
| `list_widgets` | Get all widgets | - | `category`, `search` |
| `get_widget_schema` | Get widget schema | `widget_name` | `include_common_controls` |
| `create_widget_instance` | Create widget JSON | `widget_type` | `settings`, `validate_settings` |
| `get_widget_controls` | Get control details | `widget_name` | `control_name`, `include_common`, `tab` |

## Common Control Types

### Input Controls
```
text, textarea, wysiwyg, code, number, hidden
```

### Choice Controls
```
select, select2, choose, switcher
```

### Media Controls
```
media, gallery, icon, icons
```

### Dimension Controls
```
slider, dimensions, color, font
```

### Special Controls
```
url, repeater, date_time
```

### Group Controls
```
typography, text_shadow, box_shadow, border, background
```

## Common Value Formats

### URL Control
```json
{
  "url": "https://example.com",
  "is_external": true,
  "nofollow": false
}
```

### Media Control
```json
{
  "id": 123,
  "url": "https://example.com/image.jpg"
}
```

### Slider Control
```json
{
  "size": 50,
  "unit": "px"
}
```

### Dimensions Control
```json
{
  "top": 10,
  "right": 20,
  "bottom": 10,
  "left": 20,
  "unit": "px",
  "isLinked": false
}
```

### Repeater Control
```json
[
  {"field1": "value1", "field2": "value2"},
  {"field1": "value3", "field2": "value4"}
]
```

## Widget Instance Format

```json
{
  "id": "a1b2c3d4",
  "elType": "widget",
  "widgetType": "button",
  "settings": {
    "text": "Click Me",
    "link": {
      "url": "https://example.com"
    }
  }
}
```

## Common Widget Categories

```
basic, general, pro-elements, theme-elements, wordpress,
woocommerce, site, post, archive
```

## Elementor Access Patterns

### Get Widget Manager
```php
Plugin::$instance->widgets_manager
```

### Get All Widgets
```php
$widgets = Plugin::$instance->widgets_manager->get_widget_types();
```

### Get Specific Widget
```php
$widget = Plugin::$instance->widgets_manager->get_widget_types('button');
```

### Get Widget Stack
```php
$stack = $widget->get_stack(true);  // With common controls
$stack = $widget->get_stack(false); // Without common
```

## Condition Operators

```
===  (equals)
!==  (not equals)
in   (in array)
!in  (not in array)
contains
!contains
<, >, <=, >=
```

## Tab Constants

```php
Controls_Manager::TAB_CONTENT
Controls_Manager::TAB_STYLE
Controls_Manager::TAB_ADVANCED
Controls_Manager::TAB_LAYOUT
```

## Selector Placeholders

```
{{WRAPPER}}  - Widget wrapper element
{{VALUE}}    - Control value
{{URL}}      - Media URL
{{UNIT}}     - Size unit
{{SIZE}}     - Size value
```

## Common Experiments

```
e_optimized_markup  - Affects inner wrapper
container           - New container element
nested_elements     - Nested tabs/accordion
atomic_widgets      - New widget architecture
```

## Error Codes

```
elementor_not_loaded       - Elementor not loaded
elementor_not_available    - Plugin class missing
elementor_not_initialized  - Instance not available
widget_not_found          - Widget doesn't exist
missing_required_argument - Required arg missing
settings_validation_failed - Invalid settings
invalid_type              - Wrong value type
invalid_option            - Invalid select option
value_too_small           - Below minimum
value_too_large           - Above maximum
invalid_url               - Invalid URL format
```

## Quick Test Commands

### List All Widgets
```json
{"tool": "list_widgets", "args": {}}
```

### List Basic Widgets
```json
{"tool": "list_widgets", "args": {"category": "basic"}}
```

### Get Button Schema
```json
{"tool": "get_widget_schema", "args": {"widget_name": "button"}}
```

### Create Button
```json
{
  "tool": "create_widget_instance",
  "args": {
    "widget_type": "button",
    "settings": {"text": "Click Me"}
  }
}
```

### Get Heading Controls
```json
{"tool": "get_widget_controls", "args": {"widget_name": "heading"}}
```

## Common Widgets Reference

### Core Widgets (Always Available)

**Basic Category:**
- `button` - Button with link
- `heading` - Heading/title
- `image` - Single image
- `text-editor` - WYSIWYG content
- `video` - Video embed
- `spacer` - Empty space
- `divider` - Horizontal line
- `google-maps` - Google Maps embed
- `icon` - Icon display
- `image-box` - Image with text
- `icon-box` - Icon with text
- `star-rating` - Star rating display
- `image-carousel` - Image slider
- `image-gallery` - Image gallery
- `icon-list` - List with icons
- `counter` - Animated counter
- `progress` - Progress bar
- `testimonial` - Testimonial
- `tabs` - Tab container
- `accordion` - Accordion
- `toggle` - Toggle content
- `social-icons` - Social media icons
- `alert` - Alert box
- `audio` - Audio player
- `shortcode` - WordPress shortcode
- `html` - Custom HTML
- `menu-anchor` - Anchor point
- `sidebar` - WordPress sidebar
- `read-more` - Read more link
- `rating` - Rating display

## Widget Settings Patterns

### Simple Text Widget
```json
{
  "widget_type": "heading",
  "settings": {
    "title": "My Title",
    "header_size": "h2"
  }
}
```

### Widget with Link
```json
{
  "widget_type": "button",
  "settings": {
    "text": "Learn More",
    "link": {
      "url": "https://example.com",
      "is_external": true
    }
  }
}
```

### Widget with Media
```json
{
  "widget_type": "image",
  "settings": {
    "image": {
      "id": 123,
      "url": "https://example.com/image.jpg"
    },
    "image_size": "medium"
  }
}
```

### Widget with Repeater
```json
{
  "widget_type": "icon-list",
  "settings": {
    "icon_list": [
      {
        "text": "Item 1",
        "icon": {"value": "fas fa-check"}
      },
      {
        "text": "Item 2",
        "icon": {"value": "fas fa-star"}
      }
    ]
  }
}
```

## Best Practices

1. **Always verify Elementor is loaded**
2. **Validate widget exists before use**
3. **Sanitize all user input**
4. **Use proper value formats for control types**
5. **Check experiments before using features**
6. **Generate unique hex IDs**
7. **Validate settings before creating instances**
8. **Handle errors gracefully**

## Performance Tips

1. Use `get_stack(false)` if common controls not needed
2. Filter controls early to reduce data
3. Request specific controls when possible
4. Cache schema results when appropriate
5. Batch widget operations when possible

## Debugging

### Check if Elementor is Loaded
```php
did_action('elementor/loaded')
```

### Check if Widget Exists
```php
$widget = Plugin::$instance->widgets_manager->get_widget_types('widget_name');
if (!$widget) {
    // Widget doesn't exist
}
```

### Check Experiment State
```php
Plugin::$instance->experiments->is_feature_active('experiment_name')
```

### Validate Control Type
```php
$controls = Plugin::$instance->controls_manager->get_controls();
if (!isset($controls[$control_type])) {
    // Control type doesn't exist
}
```

## Common Gotchas

1. **Widget names are singular** (`button`, not `buttons`)
2. **Categories use underscores** (`pro-elements`, not `pro_elements`)
3. **IDs must be hex strings** (use `dechex(mt_rand())`)
4. **URLs are objects**, not strings
5. **Media controls need both ID and URL**
6. **Group controls create multiple controls**
7. **Common controls are optional** (don't always include)
8. **Experiments affect behavior** (check before use)
9. **Validation is strict** (use exact formats)
10. **Selectors only work in editor** (not in API)
