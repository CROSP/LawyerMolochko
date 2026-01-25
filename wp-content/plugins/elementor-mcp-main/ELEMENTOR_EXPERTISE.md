# Elementor Expert Design & Development Guide

## Based on Training Data Analysis: SaaS Templates & Dark Portfolio

### 1. Core Design Systems

#### Global Color Palette Patterns

**SaaS Design System:**
- Primary: `#3A36DB` (Strong blue for CTAs and brand identity)
- Secondary: `#0090FF` (Bright accent blue)
- Text Base: `#FFFFFF` (White for dark backgrounds)
- Dark Text: `#01213A` (Navy for light backgrounds)
- Supporting Colors:
  - Light Grey: `#E3F0FF`
  - Mid Grey: `#F4F7FF`
  - Success Green: `#1AD598`
  - Accent Purple: `#DB5AEE`

**Dark Portfolio Design System:**
- Primary: `#030303` (Near black for backgrounds)
- Secondary: `#FFFFFF` (White for contrast)
- Text: `#D4D4D4` (Light grey for readability)
- Accent: `#F2CD8C` (Warm gold for highlights)
- Supporting Colors:
  - Border Grey: `#A8A8A8`
  - Icon Background: `#EAE6DC`
  - Section BG: `#0C0C0C`

### 2. Typography Systems

#### SaaS Typography Hierarchy
```json
{
  "hero_heading": {
    "family": "Manrope",
    "weight": "700",
    "size": "70px",
    "line_height": "80px",
    "letter_spacing": "-1.5px",
    "mobile_size": "40px"
  },
  "subheading": {
    "family": "Heebo",
    "weight": "500",
    "size": "22px",
    "line_height": "32px"
  },
  "body_text": {
    "family": "Heebo",
    "weight": "400",
    "size": "16px",
    "line_height": "26px"
  }
}
```

#### Dark Portfolio Typography
```json
{
  "display": {
    "family": "Urbanist",
    "weight": "700",
    "size": "80px",
    "text_transform": "uppercase",
    "mobile_size": "35px"
  },
  "heading": {
    "family": "Urbanist",
    "weight": "600",
    "size": "25px"
  },
  "body": {
    "family": "Urbanist",
    "weight": "400",
    "size": "16px",
    "line_height": "1.5em"
  }
}
```

### 3. Container & Layout Patterns

#### Modern Container Structure
```json
{
  "hero_container": {
    "flex_direction": "row",
    "flex_gap": "10px",
    "padding": {
      "desktop": "100px 0",
      "tablet": "40px 20px",
      "mobile": "40px 20px"
    },
    "background": "gradient",
    "overflow": "hidden"
  }
}
```

#### Responsive Breakpoint Strategy
- Desktop: 1440px+ (full width)
- Laptop: 1024px-1439px (boxed 1140px)
- Tablet: 768px-1023px (fluid with padding)
- Mobile: <768px (fluid with 20px padding)

### 4. Widget Configurations

#### Hero Section Components
```json
{
  "heading_widget": {
    "type": "heading",
    "settings": {
      "title": "Dynamic text",
      "header_size": "h1",
      "align": "center",
      "animation": "fadeInDown"
    }
  },
  "cta_button": {
    "type": "button",
    "settings": {
      "text": "Free Trial",
      "icon_align": "right",
      "icon": "arrow-right",
      "size": "lg",
      "hover_animation": "grow"
    }
  },
  "video_popup": {
    "type": "video_button",
    "settings": {
      "button_size": "90px",
      "icon_size": "28px",
      "border_radius": "50%",
      "box_shadow": "0 0 50px rgba(0,0,0,0.2)"
    }
  }
}
```

#### Pricing Table Structure
```json
{
  "pricing_widget": {
    "type": "elementskit-pricing",
    "layout": "3-column-grid",
    "featured_plan": "middle",
    "elements": {
      "price": "$29/month",
      "features_list": ["Feature 1", "Feature 2"],
      "cta_button": "Get Started"
    }
  }
}
```

### 5. Animation & Interaction Patterns

#### Entrance Animations
- Hero Title: `fadeInDown` with no delay
- Subtitle: `fadeInUp` with 200ms delay
- CTA Buttons: `fadeInUp` with 400ms delay
- Images: `fadeInUp` with 600ms delay

#### Hover Effects
- Buttons: Scale 1.05 with color transition
- Cards: Box shadow elevation change
- Images: Overlay opacity fade
- Links: Underline animation

### 6. Section Design Patterns

#### Hero Section
- Full viewport height (100vh) on desktop
- Gradient or image background with overlay
- Center-aligned content
- Two-button CTA group
- Supporting image or video

#### Features Section
- 3-column grid layout
- Icon + Heading + Description pattern
- Alternating image + text rows
- Animated counters/statistics

#### Pricing Section
- 3-tier pricing table
- Featured/popular badge on middle tier
- Toggle for monthly/yearly
- FAQ accordion below

#### Testimonials Section
- Carousel/slider format
- Avatar + Quote + Name/Role
- Star ratings
- Company logos

### 7. Best Practices

#### Performance Optimization
- Use WebP images with fallbacks
- Lazy load below-fold content
- Minimize custom CSS
- Use global colors/fonts
- Optimize container nesting

#### Accessibility
- Proper heading hierarchy (h1-h6)
- Alt text for all images
- ARIA labels for interactive elements
- Keyboard navigation support
- Sufficient color contrast ratios

#### Mobile-First Approach
- Stack columns on mobile
- Adjust typography scales
- Simplify animations
- Touch-friendly button sizes (min 44px)
- Reduce padding/margins

### 8. Advanced Techniques

#### Dynamic Content Patterns
```json
{
  "post_grid": {
    "posts_per_page": 6,
    "columns": {
      "desktop": 3,
      "tablet": 2,
      "mobile": 1
    },
    "image_ratio": "16:9",
    "show_excerpt": true,
    "read_more": "arrow-link"
  }
}
```

#### Custom CSS Classes
```css
/* Gradient text effect */
.gradient-text {
  background: linear-gradient(90deg, #3A36DB, #0090FF);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Glass morphism card */
.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
```

### 9. Component Library

#### Button Variations
1. Primary CTA: Filled background, white text
2. Secondary: Transparent bg, colored border
3. Ghost: No border, text only
4. Icon Button: Circle with icon
5. Text Link: Underlined with arrow

#### Card Patterns
1. Service Card: Icon + Title + Description
2. Pricing Card: Price + Features + CTA
3. Team Card: Image + Name + Role + Social
4. Portfolio Card: Image + Overlay + Title
5. Testimonial Card: Quote + Author + Rating

### 10. Workflow Best Practices

#### Development Process
1. Set up global styles first
2. Create reusable templates
3. Build sections modularly
4. Test responsive at each breakpoint
5. Optimize before deployment

#### Naming Conventions
- Sections: `section-[name]`
- Containers: `container-[purpose]`
- Custom classes: `custom-[element]-[variant]`
- IDs: Use sparingly, only for anchors

This expertise is based on analyzing professional SaaS and portfolio templates, incorporating modern design trends and Elementor best practices.