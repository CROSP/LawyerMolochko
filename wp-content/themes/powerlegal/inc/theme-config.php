<?php
// make some configs
if(!function_exists('powerlegal_configs')){
    function powerlegal_configs($value){ 
        $body_font    = '\'Inter\', sans-serif';
        $heading_font = '\'Cormorant Infant\', sans-serif';
          
        $configs = [
            'theme_colors' => [
                'primary'   => [
                    'title' => esc_html__('Primary', 'powerlegal'),
                    'value' => powerlegal()->get_opt('primary_color', '#ad9779')
                ],
                'secondary'   => [
                    'title' => esc_html__('Secondary', 'powerlegal'),
                    'value' => powerlegal()->get_opt('secondary_color', '#1a243f')
                ],
                'additional01'   => [
                    'title' => esc_html__('Additional01 Color', 'powerlegal'),
                    'value' => powerlegal()->get_opt('additional01_color', '#2f3850')
                ],
                'body'     => [
                    'title' => esc_html__('Body', 'powerlegal'),
                    'value' => powerlegal()->get_opt('font_body', ['color' => '#6d6d6d'],'color')
                ],
                'heading'     => [
                    'title' => esc_html__('Heading', 'powerlegal'),
                    'value' => powerlegal()->get_opt('font_heading', ['color' => '#10172c'],'color')
                ],
            ],
            'link' => [
                'color' => powerlegal()->get_opt('link_color', ['regular' => 'inherit'],'regular'),
                'color-hover'   => powerlegal()->get_opt('link_color', ['hover' => '#ad9779'],'hover'),
                'color-active'  => powerlegal()->get_opt('link_color', ['active' => '#ad9779'],'active'),
            ],
            'custom_sizes' => [
                'size-post-single'      => [800, 470, true],
                'size-recent-post'      => [400, 468, true],
            ],
            'body' => [
                'bg'                => '#fff',
                'font-family'       => powerlegal()->get_theme_opt('font_body',['font-family' => $body_font], 'font-family'),
                'font-size'         => powerlegal()->get_theme_opt('font_body',['font-size' => '16px'], 'font-size'),
                'font-weight'       => powerlegal()->get_theme_opt('font_body',['font-weight' => '300'], 'font-weight'),
                'line-height'       => powerlegal()->get_theme_opt('font_body',['line-height' => '1.625'], 'line-height'),
                'letter-spacing'    => powerlegal()->get_theme_opt('font_body',['letter-spacing' => '0px'], 'letter-spacing'),

            ],
            'heading' => [
                'font-family'       => powerlegal()->get_theme_opt('font_heading',['font-family' => $heading_font], 'font-family'),
                'font-weight'       => powerlegal()->get_theme_opt('font_heading',['font-weight' => '700'], 'font-weight'),
                'line-height'       => powerlegal()->get_theme_opt('font_heading',['line-height' => '1.18'], 'line-height'),
                'letter-spacing'    => powerlegal()->get_theme_opt('font_heading',['letter-spacing' => '0.02em'], 'letter-spacing'),
                'color-hover'      => 'var(--primary-color)',
            ],
            'heading_font_size' => [
                'h1' => powerlegal()->get_theme_opt('font_h1','60px'),
                'h2' => powerlegal()->get_theme_opt('font_h2','48px'),
                'h3' => powerlegal()->get_theme_opt('font_h3','36px'),
                'h4' => powerlegal()->get_theme_opt('font_h4','26px'),
                'h5' => powerlegal()->get_theme_opt('font_h5','18px'),
                'h6' => powerlegal()->get_theme_opt('font_h6','16px')
            ],
            'header' => [
                'height' => '110px' // use for default header
            ],
            'logo' => [
                'mobile_width' => powerlegal()->get_opt('logo_mobile_size', ['width' => '192px', 'units' => 'px'])['width'],
            ],
            'border' => [
                'color'          => '#dadada',
            ],
            'divider' => [
                'color'          => '#d8d8d8',
            ],
            // Menu Color
            'menu' => [
                'bg'          => '#fff',
                'regular'     => 'var(--heading-color)',
                'hover'       => 'var(--primary-color)',
                'active'      => 'var(--primary-color)',
                'font_size'   => '16px',
                'font_weight' => 700,
                'font_family' => $heading_font
            ] ,
            'dropdown' => [
                'bg'            => '#FFFFFF',
                'shadow'        => '0px 5px 83px 0 rgba(40,40,40,0.08)',
                'regular'       => '#1e1e1e',
                'hover'         => 'var(--primary-color)',
                'active'        => 'var(--primary-color)',
                'font_size'     => '16px',
                'font_weight'   => '500',
                'item_bg'       => 'transparent',
                'item_bg_hover' => '#ffffff'
            ],
            'mobile_menu' => [
                'regular' => 'var(--heading-color)',
                'hover'   => 'var(--primary-color)',
                'active'  => 'var(--primary-color)',
                'font_size'   => '17px',
                'font_weight' => 600,
                'font_family' => $heading_font,
                'item_bg'       => 'transparent',
                'item_bg_hover' => 'transparent',
                'text_transform' => 'capitalize' 
            ],
            'mobile_submenu' => [
                'regular' => 'var(--heading-color)',
                'hover'   => 'var(--primary-color)',
                'active'  => 'var(--primary-color)',
                'font_size'     => '15px', 
                'font_weight' => 400, 
                'font_family' => $body_font,
                'item_bg'       => 'transparent',
                'item_bg_hover' => 'transparent',
                'text_transform' => 'capitalize' 
            ],
            'button' => [
                'font-family'        => $body_font,
                'font-size'          => '14px',
                'font-weight'        => '700',
                'line-height'        => '2.5',
                'bg-color'           => 'var(--primary-color)',      
                'color'              => '#ffffff',
                'letter-spacing'     => '1.5px',
                'padding'            => '10px 35px',
                'radius'             => '0',
                'radius-rtl'         => '0',
                'bg-color-hover'     => 'var(--secondary-color)',
                'color-hover'        => '#ffffff',
                'border-color-hover' => 'var(--secondary-color)',
            ],
        ];

        return $configs[$value];
    }
}
if(!function_exists('powerlegal_inline_styles')){
    function powerlegal_inline_styles() {
        $body              = powerlegal_configs('body');
        $theme_colors      = powerlegal_configs('theme_colors');
        $link_color        = powerlegal_configs('link');
        $heading           = powerlegal_configs('heading');
        $heading_font_size = powerlegal_configs('heading_font_size');
        $logo              = powerlegal_configs('logo');
        ob_start();
        echo ':root{';
        foreach ($theme_colors as $color => $value) {
            printf('--%1$s-color: %2$s;', $color,  $value['value']);
        }
        foreach ($theme_colors as $color => $value) {
            printf('--%1$s-color-rgb: %2$s;', $color,  powerlegal_hex_rgb($value['value']));
        }
        foreach ($link_color as $color => $value) {
            printf('--link-%1$s: %2$s;', $color, $value);
        }
        foreach ($body as $key => $value) {
            printf('--body-%1$s: %2$s;', $key, $value);
        }
        foreach ($heading as $key => $value) {
            printf('--heading-%1$s: %2$s;', $key, $value);
        }
        foreach ($heading_font_size as $key => $value) {
            printf('--heading-font-size-%1$s: %2$s;', $key, $value);
        }
        foreach ($logo as $key => $value) {
            printf('--logo-%1$s: %2$s;', $key, $value);
        }
        echo '}';
        
        // Apply global font to all elements using CSS variable (not hardcoded)
        // This ensures all blocks inherit the body font without hardcoding
        echo 'html, body { font-family: var(--body-font-family); }';
        // Apply to all common block and widget containers
        echo '.elementor-widget, .elementor-element, .wp-block, [class*="block"], [class*="elementor"], .widget, .entry-content, .post-content, p, span, div, a, li, td, th, input, textarea, select, button { font-family: var(--body-font-family); }';
        // Headings should use heading font
        echo 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { font-family: var(--heading-font-family); }';
        
        return ob_get_clean();

    }
}
 