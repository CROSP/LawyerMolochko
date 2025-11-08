<?php
//* Layout Section
$pricing_table_layout_section = array(
    'name' => 'layout_section',
    'label' => esc_html__('Layout', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_LAYOUT,
    'controls' => array(
        array(
            'name'    => 'layout',
            'label'   => esc_html__( 'Layout', 'powerlegal' ),
            'type'    => 'layoutcontrol',
            'default' => '1',
            'options' => [
                '1' => [
                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_pricing_table-1.jpg'
                ],
            ],
        ),
    ),
);

//* Pricing Table Title Section
$pricing_table_title_section = array(
    'name' => 'pricing_table_title_section',
    'label' => esc_html__('Title', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    'controls' => array(
        array(
            'name' => 'pricing_table_title_text',
            'label' => esc_html__('Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__('Pricing Table', 'powerlegal'),
            'label_block' => true,
        ),
        array(
            'name' => 'show_highlight_text',
            'label' => esc_html__('Highlight Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'false',
        ),
        array(
            'name' => 'highlight_text',
            'label' => esc_html__('Highlight Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__('BEST', 'powerlegal'),
            'label_block' => true,
            'condition' => [
                'show_highlight_text' => 'true',
            ],
        ),
    ),
);

//* Pricing Table Pricing Section
$pricing_table_price_section = array(
    'name' => 'pricing_table_price_section',
    'label' => esc_html__('Price', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    'controls' => array(
        array(
            'name' => 'pricing_table_price_currency',
            'label' => esc_html__('Currency', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '$',
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_price_value',
            'label' => esc_html__('Price', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '25',
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_price_separator',
            'label' => esc_html__('Divider', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '/',
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_price_duration',
            'label' => esc_html__('Duration', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'm',
            'label_block' => true,
        ),
    ),
);

//* Pricing Table Description Section
$pricing_table_desc_section = array(
    'name' => 'pricing_table_desc_section',
    'label' => esc_html__('Description', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    'controls' => array(
        array(
            'name' => 'pricing_table_description',
            'label' => esc_html__('Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => esc_html__('This is description text...', 'powerlegal'),
            'label_block' => true,
        ),
    ),
);

//* Pricing Table Icon List Section
$pricing_table_list_section = array(
    'name' => 'pricing_table_list_section',
    'label' => esc_html__('Icon List', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    'controls' => array(
        array(
            'name' => 'fancy_text_list_items',
            'label' => esc_html__('Features', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'default' => [
                [
                    'pricing_list_item_icon' => 'fa fa-check',
                    'pricing_list_item_text' => esc_html__('List Item #1', 'powerlegal'),
                ],
                [
                    'pricing_list_item_icon' => 'fa fa-check',
                    'pricing_list_item_text' => esc_html__('List Item #2', 'powerlegal'),
                ],
                [
                    'pricing_list_item_icon' => 'fa fa-check',
                    'pricing_list_item_text' => esc_html__('List Item #3', 'powerlegal'),
                ],
            ],
            'controls' => array(
                array(
                    'name' => 'pricing_list_item_text',
                    'label' => esc_html__('Text', 'powerlegal'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'label_block' => true,
                ),
                array(
                    'name' => 'pricing_list_item_icon',
                    'label' => esc_html__('Icon', 'powerlegal'),
                    'type' => \Elementor\Controls_Manager::ICONS,
                    'fa4compatibility' => 'icon',
                ),
                array(
                    'name' => 'pricing_list_item_slashed',
                    'label' => esc_html__('Slashed', 'powerlegal'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                ),
            ),
            'title_field' => '<i class="{{ pricing_list_item_icon }}" aria-hidden="true"></i> {{{ pricing_list_item_text }}}',
        ),
    ),
);
//* Pricing Table Button Section
$pricing_table_button_section = array(
    'name' => 'pricing_table_button_section',
    'label' => esc_html__('Button', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
    'controls' => array(
        array(
            'name' => 'pricing_table_button_text',
            'label' => esc_html__('Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__('Get Started', 'powerlegal'),
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_button_url_type',
            'label' => esc_html__('Link Type', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'url' => esc_html__('URL', 'powerlegal'),
                'link' => esc_html__('Existing Page', 'powerlegal'),
            ],
            'default' => 'url',
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_button_link',
            'label' => esc_html__('Link', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::URL,
            'condition' => [
                'pricing_table_button_url_type' => 'url',
            ],
            'label_block' => true,
        ),
        array(
            'name' => 'pricing_table_button_link_existing_content',
            'label' => esc_html__('Existing Page', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => pxl_get_all_page(),
            'condition' => [
                'pricing_table_button_url_type' => 'link',
            ],
            'multiple' => false,
            'label_block' => true,
        ),
    ),
);

//* Title Style Settings
$pricing_title_style_settings = array(
    'name' => 'pricing_title_style_settings',
    'label' => esc_html__('Title', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    'controls' => array(
        array(
            'name' => 'image_background',
            'label' => esc_html__('Background Image', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'dynamic' => [
                'active' => true,
            ],
            'default' => "",
        ),
        array(
            'name' => 'title_background',
            'label' => esc_html__('Background Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pricing-price-container' => 'background-color: {{VALUE}};'
            ],
        ),
        array(
            'name' => 'pricing_title_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pricing-table-title' => 'color: {{VALUE}} !important;'
            ]
        ),
        array(
            'name' => 'title_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pricing-table-title',
        ),
        array(
            'name' => 'border_width',
            'label' => esc_html__( 'Border Width', 'powerlegal' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-table-container .inner-box' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'responsive' => true,
        ),
        array(
            'name' => 'border_color',
            'label' => esc_html__( 'Border Color', 'powerlegal' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '',
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-table-container .inner-box' => 'border-color: {{VALUE}};',
            ],
        ),
        array(
            'name' => 'pricing_title_margin',
            'label' => esc_html__('Margin', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'control_type' => 'responsive',
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .pricing-table-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ]
        ),
    ),
);

//* Price Style Settings
$pricing_price_style_settings = array(
    'name' => 'pricing_price_style_settings',
    'label' => esc_html__('Price', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    'controls' => array(
        array(
            'name' => 'pricing_currency_heading',
            'label' => esc_html__('Currency', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_currency_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-currency' => 'color: {{VALUE}} !important;'
            ],
        ),
        array(
            'name' => 'currency_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-currency',
        ),
        array(
            'name' => 'pricing_currency_postion',
            'label' => esc_html__('Position', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'control_type' => 'responsive',
            'size_units' => ['px', 'em', '%'],
            'allowed_dimensions' => ['top', 'left'],
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap  .pricing-price-currency' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'after'
        ),
        array(
            'name' => 'pricing_price_heading',
            'label' => esc_html__('Price', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_price_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-value' => 'color: {{VALUE}};'
            ],
        ),
        array(
            'name' => 'price_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-value',
        ),
        array(
            'name' => 'pricing_price_margin',
            'label' => esc_html__('Margin', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'control_type' => 'responsive',
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-value' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'after',
        ),
        array(
            'name' => 'pricing_sep_heading',
            'label' => esc_html__('Divider', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_sep_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-separator' => 'color: {{VALUE}};'
            ],
        ),
        array(
            'name' => 'separator_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-separator',
        ),
        array(
            'name' => 'pricing_sep_margin',
            'label' => esc_html__('Margin', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'control_type' => 'responsive',
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'after',
        ),
        array(
            'name' => 'pricing_dur_heading',
            'label' => esc_html__('Duration', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_dur_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-duration' => 'color: {{VALUE}};'
            ],
        ),
        array(
            'name' => 'duration_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-duration',
        ),
        array(
            'name' => 'pricing_dur_margin',
            'label' => esc_html__('Margin', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'control_type' => 'responsive',
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-duration' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ],
            'separator' => 'after',
        ),
    ),
);

//* Icon List Style Settings
$pricing_list_style_settings = array(
    'name' => 'pricing_list_style_settings',
    'label' => esc_html__('Features List', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    'controls' => array(
        array(
            'name' => 'pricing_features_text_heading',
            'label' => esc_html__('Text', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_list_text_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list .pricing-list-span' => 'color: {{VALUE}};'
            ]
        ),
        array(
            'name' => 'pricing_list_text_color_hover',
            'label' => esc_html__('Hover Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-table-container:hover .pricing-list .pricing-list-span' => 'color: {{VALUE}} !important;'
            ]
        ),
        array(
            'name' => 'pricing_list_slashed_color',
            'label' => esc_html__('Slashed Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list .pricing-list-span.item-slashed' => 'color: {{VALUE}};'
            ]
        ),
        array(
            'name' => 'list_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-list .pricing-list-span',
            'separator' => 'after',
        ),
        array(
            'name' => 'pricing_features_icon_heading',
            'label' => esc_html__('Icon', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::HEADING,
        ),
        array(
            'name' => 'pricing_list_icon_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list i' => 'color: {{VALUE}} !important;'
            ]
        ),
        array(
            'name' => 'pricing_list_icon_size',
            'label' => esc_html__('Size', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list i' => 'font-size: {{SIZE}}px',
            ]
        ),
        array(
            'name' => 'pricing_list_icon_spacing',
            'label' => esc_html__('Spacing', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list i' => 'margin-right: {{SIZE}}px',
            ],
        ),
        array(
            'name' => 'pricing_list_item_margin',
            'label' => esc_html__('Vertical Spacing', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-list li' => 'margin-bottom: {{SIZE}}px;'
            ],
            'separator' => 'after'
        ),
        array(
            'name' => 'features_list_background',
            'label' => esc_html__('Background Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-table-container .pricing-list-container' => 'background-color: {{VALUE}};'
            ],
        ),
        array(
            'name' => 'features_list_background_hover',
            'label' => esc_html__('Hover Background', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-table-container:hover .pricing-list-container' => 'background-color: {{VALUE}} !important;'
            ],
        ),
    ),
);

//* Button Style Settings
$pricing_button_style_settings = array(
    'name' => 'pricing_button_style_settings',
    'label' => esc_html__('Button', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    'controls' => array(
        array(
            'name' => 'pricing_button_color',
            'label' => esc_html__('Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button' => 'color: {{VALUE}};'
            ]
        ),
        array(
            'name' => 'pricing_button_hover_color',
            'label' => esc_html__('Hover Text Color', 'powerlegal'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button:hover' => 'color: {{VALUE}};'
            ]
        ),
        array(
            'name' => 'button_typo',
            'type' => \Elementor\Group_Control_Typography::get_type(),
            'control_type' => 'group',
            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button',
        ),
        array(
            'name' => 'pricing_table_button_style_tabs',
            'control_type' => 'tab',
            'tabs' => array(
                array(
                    'name' => 'pricing_table_button_style_normal',
                    'label' => esc_html__('Normal', 'powerlegal'),
                    'controls' => array(
                        array(
                            'name' => 'pricing_table_button_background',
                            'type' => \Elementor\Group_Control_Background::get_type(),
                            'control_type' => 'group',
                            'types' => ['classic', 'gradient'],
                            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button',
                        ),
                        array(
                            'name' => 'pricing_table_button_border',
                            'type' => \Elementor\Group_Control_Border::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button',
                        ),
                        array(
                            'name' => 'pricing_table_box_button_radius',
                            'label' => esc_html__('Border Radius', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SLIDER,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button' => 'border-radius: {{SIZE}}{{UNIT}};'
                            ]
                        ),
                        array(
                            'name' => 'pricing_button_margin',
                            'label' => esc_html__('Margin', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::DIMENSIONS,
                            'control_type' => 'responsive',
                            'size_units' => ['px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                            ]
                        ),
                        array(
                            'name' => 'pricing_button_padding',
                            'label' => esc_html__('Padding', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::DIMENSIONS,
                            'control_type' => 'responsive',
                            'size_units' => ['px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pricing-wrap .pricing-price-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                            ]
                        ),
                    )
                ),
                array(
                    'name' => 'pricing_table_button_style_hover',
                    'label' => esc_html__('Hover', 'powerlegal'),
                    'controls' => array(
                        array(
                            'name' => 'pricing_table_button_background_hover',
                            'type' => \Elementor\Group_Control_Background::get_type(),
                            'control_type' => 'group',
                            'types' => ['classic', 'gradient'],
                            'selector' => '{{WRAPPER}} .pricing-price-button:hover',
                        ),
                        array(
                            'name' => 'pricing_table_button_border_hover',
                            'type' => \Elementor\Group_Control_Border::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pricing-price-button:hover',
                        ),
                        array(
                            'name' => 'pricing_table_button_border_radius_hover',
                            'label' => esc_html__('Border Radius', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SLIDER,
                            'selectors' => [
                                '{{WRAPPER}} .pricing-price-button:hover' => 'border-radius: {{SIZE}}{{UNIT}};'
                            ]
                        ),
                        array(
                            'name' => 'pricing_table_button_shadow_hover',
                            'type' => \Elementor\Group_Control_Box_Shadow::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pricing-price-button:hover',
                        ),
                        array(
                            'name' => 'pricing_button_margin_hover',
                            'label' => esc_html__('Margin', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::DIMENSIONS,
                            'control_type' => 'responsive',
                            'size_units' => ['px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .pricing-price-button:hover' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                            ]
                        ),
                        array(
                            'name' => 'pricing_button_padding_hover',
                            'label' => esc_html__('Padding', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::DIMENSIONS,
                            'control_type' => 'responsive',
                            'size_units' => ['px', 'em', '%'],
                            'selectors' => [
                                '{{WRAPPER}} .pricing-price-button:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                            ]
                        ),
                    )
                ),
            )
        ),
    ),
);

//* Animation
$pricing_animation_settings = array(
    'name' => 'pricing_animation_settings',
    'label' => esc_html__('Animation', 'powerlegal'),
    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    'controls' => array(
        array(
            'name' => 'pxl_animate',
            'label' => esc_html__('Widget Animate', 'powerlegal' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => powerlegal_wow_animate(),
            'default' => '',
        ),
    ),
);

pxl_add_custom_widget(
    array(
        'name' => 'pxl_pricing_table',
        'title' => esc_html__('Pxl Pricing Table', 'powerlegal'),
        'icon' => 'eicon-price-table',
        'categories' => array('pxltheme-core'),
        'scripts'    => array(
        ),
        'params' => array(
            'sections' => array(
                $pricing_table_layout_section,
                $pricing_table_title_section,
                $pricing_table_price_section,
                $pricing_table_desc_section,
                $pricing_table_list_section,
                $pricing_table_button_section,
                $pricing_title_style_settings,
                $pricing_price_style_settings,
                $pricing_list_style_settings,
                $pricing_button_style_settings,
                $pricing_animation_settings,
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);