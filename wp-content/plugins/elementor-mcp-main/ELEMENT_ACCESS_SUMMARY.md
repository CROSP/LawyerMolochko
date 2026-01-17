# Element Access Summary - Free Consultation Container

## ✅ Successfully Accessed Element

### Element Details

**Element ID:** `322ae9a`  
**Element Type:** `widget`  
**Widget Type:** `pxl_fancy_box`  
**Location:** Homepage (Post ID: 222)

### Element Structure

```
Homepage (ID: 222)
└── Section (ID: 8f0a21e parent)
    └── Column (ID: 8f0a21e)
        └── Widget: pxl_fancy_box (ID: 322ae9a) ← Free Consultation
```

### Element Settings

```json
{
    "id": "322ae9a",
    "elType": "widget",
    "widgetType": "pxl_fancy_box",
    "settings": {
        "layout": "4",
        "title": "Free Consultation",
        "description": "If you are in need of help from a lawyer, we will advise you free of charge regardless of the case. Grow organically the holistic world view.",
        "selected_icon": {
            "value": {
                "url": "http://lawyermolochko.ddev.site:8080/wp-content/uploads/2022/07/noun_consulting_4023597.svg",
                "id": 6715
            },
            "library": "svg"
        },
        "selected_img": {
            "url": "http://lawyermolochko.ddev.site:8080/wp-content/uploads/2022/07/h1_fancy1.jpg",
            "id": 6709,
            "alt": "Fancy Box",
            "source": "library"
        },
        "link": {
            "url": "http://lawyermolochko.ddev.site:8080/book-appointment",
            "is_external": "on",
            "nofollow": "on",
            "custom_attributes": ""
        },
        "button_text": "GET IN TOUCH"
    }
}
```

### Parent Container

**Parent ID:** `8f0a21e`  
**Parent Type:** `column`  
**Column Settings:**
- `_column_size`: 33 (33% width)
- `content_position`: center
- `_inline_size_tablet`: 100 (full width on tablet)

### HTML Output Structure

The element renders as:

```html
<div class="elementor-widget-container">
    <div class="pxl-fancy-box layout-4">
        <div class="box-inner">
            <div class="box-top d-flex">
                <div class="box-icon d-flex">
                    <!-- SVG Icon -->
                </div>
                <h3 class="box-title">Free Consultation</h3>
            </div>
            <div class="box-center">
                <div class="box-description">
                    If you are in need of help from a lawyer, we will advise you free of charge regardless of the case. Grow organically the holistic world view.
                </div>
                <a class="btn-more" href="http://lawyermolochko.ddev.site:8080/book-appointment" target="_blank" rel="nofollow">
                    <span>GET IN TOUCH</span>
                    <i class="zmdi zmdi-long-arrow-right"></i>
                </a>
            </div>
            <div class="box-image">
                <img src="https://lawyermolochko.ddev.site:8443/wp-content/uploads/2022/07/h1_fancy1.jpg" alt="Fancy Box">
            </div>
        </div>
    </div>
</div>
```

## MCP Tools Used

1. **`get_document`** - Retrieved full page structure
2. **`elementor_find_elements`** - Searched for elements containing "Free Consultation"
3. **`elementor_get_element`** - Retrieved full element data
4. **`elementor_get_element_path`** - Got element hierarchy

## How to Access This Element via MCP

### Using Claude Desktop

Once Claude Desktop is configured with Elementor MCP, you can ask:

```
"Get the Free Consultation widget from the homepage"
"Show me the settings for element 322ae9a on page 222"
"Find all fancy-box widgets on the homepage"
```

### Direct MCP Tool Calls

```json
{
  "tool": "elementor_get_element",
  "arguments": {
    "post_id": 222,
    "element_id": "322ae9a"
  }
}
```

## Next Steps

You can now:
- ✅ Read element settings
- ✅ Update element content
- ✅ Modify element settings
- ✅ Move or duplicate the element
- ✅ Get element siblings
- ✅ Update widget content

All using the Elementor MCP tools!




