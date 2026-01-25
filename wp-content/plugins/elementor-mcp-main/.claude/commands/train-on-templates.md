# Train on Elementor Templates

You are now entering template training mode. Your task is to deeply analyze and learn from the Elementor templates in the training-data directory to understand professional SaaS design patterns and best practices.

## Allowed Tools

You can use the following tools without requiring user approval during training:
- Glob (pattern: "training-data/**/*.json", "training-data/**/manifest.json")
- Read (any file in training-data directory)
- Grep (for searching patterns in templates)
- mcp__wpcursor-elementor__get_elementor_info
- mcp__wpcursor-elementor__list_widgets
- mcp__wpcursor-elementor__get_widget_schema
- mcp__wpcursor-elementor__get_widget_controls
- mcp__wpcursor-elementor__list_control_types
- mcp__wpcursor-elementor__get_control_schema
- mcp__wpcursor-elementor__get_breakpoints
- mcp__wpcursor-elementor__get_global_colors
- mcp__wpcursor-elementor__get_global_fonts
- mcp__wpcursor-elementor__create_section
- mcp__wpcursor-elementor__create_container
- mcp__wpcursor-elementor__create_widget_instance
- mcp__wpcursor-elementor__elementor_add_section_to_page
- mcp__wpcursor-elementor__elementor_add_widget_to_column
- mcp__wpcursor-elementor__elementor_add_element
- mcp__wpcursor-elementor__create_elementor_page
- mcp__wpcursor-elementor__get_document
- mcp__wpcursor-elementor__get_elementor_data
- mcp__wpcursor-elementor__save_document
- mcp__wpcursor-elementor__update_elementor_data
- mcp__wpcursor-elementor__elementor_update_element
- mcp__wpcursor-elementor__elementor_batch_update_elements
- mcp__wpcursor-elementor__elementor_update_widget_content
- mcp__wpcursor-elementor__elementor_delete_element
- mcp__wpcursor-elementor__elementor_duplicate_element
- mcp__wpcursor-elementor__elementor_move_element
- mcp__wpcursor-elementor__elementor_replace_element
- mcp__wpcursor-elementor__elementor_find_elements
- mcp__wpcursor-elementor__elementor_get_element
- mcp__wpcursor-elementor__elementor_get_element_path
- mcp__wpcursor-elementor__elementor_get_element_siblings
- mcp__wpcursor-elementor__update_page_settings
- mcp__wpcursor-elementor__update_global_colors
- mcp__wpcursor-elementor__update_global_fonts
- mcp__wpcursor-elementor__list_templates
- mcp__wpcursor-elementor__get_template
- mcp__wpcursor-elementor__save_template
- mcp__wpcursor-elementor__import_template
- mcp__wpcursor-elementor__export_template
- mcp__wpcursor-elementor__pexels_search_photos
- mcp__wpcursor-elementor__pexels_import_photo
- mcp__wpcursor-elementor__pexels_get_curated_photos
- mcp__wpcursor-elementor__pexels_get_photo
- mcp__wpcursor-elementor__pexels_get_popular_videos
- mcp__wpcursor-elementor__pexels_search_videos
- Write (for creating documentation files)

## Training Instructions

1. **Explore Template Structure**
   - Read all manifest.json files in subdirectories of `training-data/`
   - Analyze the organization and naming conventions
   - Understand the template hierarchy and relationships

2. **Analyze Design Patterns**
   Study each template JSON file and extract:
   - **Layout Structures**: How sections, columns, and containers are organized
   - **Widget Usage**: Which widgets are used for specific purposes
   - **Typography Hierarchy**: Font sizes, weights, and line heights for different elements
   - **Color Schemes**: How colors are applied (backgrounds, text, accents)
   - **Spacing Patterns**: Padding, margins, and gaps between elements
   - **Responsive Design**: Tablet and mobile breakpoint settings
   - **Animation Effects**: Entrance animations and transitions
   - **Icon Usage**: How icons enhance the design
   - **Button Styles**: CTA design patterns and hover states

3. **Industry-Specific Learning**
   For each template kit, identify:
   - Target industry characteristics
   - Unique design requirements
   - Content patterns and copywriting style
   - Visual hierarchy priorities
   - User journey optimization

4. **Component Patterns**
   Document recurring patterns for:
   - **Hero Sections**: Headlines, subheadlines, CTAs, backgrounds
   - **Feature Showcases**: Icon boxes, grids, alternating layouts
   - **Pricing Tables**: Card designs, highlighting, feature lists
   - **Testimonials**: Carousel vs grid, quote styles, ratings
   - **CTAs**: Button combinations, urgency elements, background treatments
   - **Footers**: Column layouts, widget organization, social links
   - **Forms**: Field arrangements, validation, submission handling

5. **Best Practices Extraction**
   Compile guidelines for:
   - Mobile-first responsive design
   - Performance optimization (avoiding heavy animations)
   - Accessibility considerations
   - Visual consistency across sections
   - Effective use of white space
   - Color psychology and contrast
   - Typography readability
   - Navigation patterns

6. **Create Mental Models**
   Build frameworks for:
   - When to use containers vs sections
   - Optimal column distributions for different content
   - Widget selection for specific goals
   - Global vs local styling decisions
   - Template reusability strategies

## Implementation Guidelines

After analyzing the templates, you should be able to:
1. **Recreate similar designs** using native Elementor widgets
2. **Suggest appropriate patterns** based on user requirements
3. **Apply learned best practices** automatically
4. **Combine patterns creatively** for unique designs
5. **Optimize for performance** while maintaining visual appeal

## Key Learning Objectives

- Master the use of Elementor's widget ecosystem
- Understand professional spacing and typography standards
- Learn color theory application in web design
- Grasp responsive design principles
- Internalize conversion-focused design patterns

## Output Format

When asked to create pages after training:
1. Use **only native Elementor widgets** (no HTML widgets)
2. Apply **consistent global colors and typography**
3. Implement **proper responsive settings**
4. Follow **accessibility best practices**
5. Maintain **clean, semantic structure**

## Training Execution

Start by:
1. List all template directories in `training-data/`
2. Read and analyze manifest.json files
3. Study template JSON structures
4. Extract and document patterns
5. Create a comprehensive understanding document
6. Confirm completion of training

Remember: The goal is to internalize these patterns so you can create professional, conversion-optimized Elementor pages that match or exceed the quality of these templates.