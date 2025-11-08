<?php
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_heading',
        'title'      => esc_html__( 'PXL Heading', 'powerlegal' ),
        'icon'       => 'eicon-t-letter',
        'categories' => array('pxltheme-core'),
        'scripts'    => array(
            'powerlegal-typewrite',
            'powerlegal-animation'
        ),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'layout_section',
                    'label'    => esc_html__( 'Layout', 'powerlegal' ),
                    'tab'      => 'layout',
                    'controls' => array(
                        array(
                            'name'    => 'layout',
                            'label'   => esc_html__( 'Layout', 'powerlegal' ),
                            'type'    => 'layoutcontrol',
                            'default' => '1',
                            'options' => [
                                '1' => [
                                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_heading-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-heading-layout-',
                        ),
                    )
                ),
                array(
                    'name' => 'title_section',
                    'label' => esc_html__('Title', 'powerlegal' ),
                    'tab' => 'content',
                    'controls' => array_merge(
                        array(
                            array(
                                'name' => 'title',
                                'label' => esc_html__('Title', 'powerlegal' ),
                                'type' => 'textarea',
                                'default' => 'Alfred Nofiat',
                                'label_block' => true,
                            ),
                            array(
                                'name' => 'title_tag',
                                'label' => esc_html__('Heading HTML Tag', 'powerlegal' ),
                                'type' => 'select',
                                'options' => [
                                    'h1' => 'H1',
                                    'h2' => 'H2',
                                    'h3' => 'H3',
                                    'h4' => 'H4',
                                    'h5' => 'H5',
                                    'h6' => 'H6',
                                    'div' => 'div',
                                    'span' => 'span',
                                    'p' => 'p',
                                ],
                                'default' => 'h2',
                            ),
                            array(
                                'name' => 'title_color',
                                'label' => esc_html__('Title Color', 'powerlegal' ),
                                'type' => 'color',
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-wrap .heading-title' => 'color: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'title_typography',
                                'label' => esc_html__('Title Typography', 'powerlegal' ),
                                'type' => \Elementor\Group_Control_Typography::get_type(),
                                'control_type' => 'group',
                                'selector' => '{{WRAPPER}} .pxl-heading-wrap .heading-title',
                            ),
                            array(
                                'name' => 'text_align',
                                'label' => esc_html__('Alignment', 'powerlegal' ),
                                'type' => 'choose',
                                'control_type' => 'responsive',
                                'options' => [
                                    'start' => [
                                        'title' => esc_html__( 'Start', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-left',
                                    ],
                                    'center' => [
                                        'title' => esc_html__( 'Center', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-center',
                                    ],
                                    'end' => [
                                        'title' => esc_html__( 'End', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-right',
                                    ]
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-wrap' => 'justify-content: {{VALUE}};',
                                    '{{WRAPPER}} .pxl-heading-inner' => 'text-align: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name'  => 'max_width',
                                'label' => esc_html__( 'Max Width (px)', 'powerlegal' ),
                                'type'  => 'slider',
                                'control_type' => 'responsive',
                                'range' => [
                                    'px' => [
                                        'min' => 100,
                                        'max' => 1920,
                                    ],
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-inner' => 'max-width: {{SIZE}}{{UNIT}};',
                                ]
                            ),
                            array(
                                'name' => 'link',
                                'label' => esc_html__('Link', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::URL,
                            ),
                        ),
                        powerlegal_elementor_animation_opts([
                            'name'   => 'title',
                            'label' => '',
                        ])
                    ),
                ),
                array(
                    'name' => 'sub_title_section',
                    'label' => esc_html__('Sub Title', 'powerlegal' ),
                    'tab' => 'content',
                    'controls' => array_merge(
                        array(
                            array(
                                'name' => 'sub_title',
                                'label' => esc_html__('Sub Title', 'powerlegal' ),
                                'type' => 'textarea',
                                'label_block' => true,
                            ),
                            array(
                                'name' => 'sub_title_color',
                                'label' => esc_html__('Sub Title Color', 'powerlegal' ),
                                'type' => 'color',
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-wrap .heading-subtitle' => 'color: {{VALUE}};',
                                    '{{WRAPPER}} .pxl-heading-wrap .heading-subtitle span:before' => 'background-color: {{VALUE}};',
                                    '{{WRAPPER}} .pxl-heading-wrap .heading-subtitle span:after' => 'background-color: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'sub_title_typography',
                                'label' => esc_html__('Sub Title Typography', 'powerlegal' ),
                                'type' => \Elementor\Group_Control_Typography::get_type(),
                                'control_type' => 'group',
                                'selector' => '{{WRAPPER}} .pxl-heading-wrap .heading-subtitle',
                            ),
                            array(
                                'name'         => 'sub_title_atbot',
                                'label'        => esc_html__( 'At Bottom', 'powerlegal' ),
                                'type'         => 'switcher',
                                'default'      => '',
                                'label_on'     => 'Hide',
                                'label_off'    => 'Show',
                            ),
                            array(
                                'name'         => 'sub_title_line',
                                'label'        => esc_html__( 'Line Text', 'powerlegal' ),
                                'type'         => 'switcher',
                                'default'      => '',
                                'label_on'     => 'Hide',
                                'label_off'    => 'Show',
                            ),
                            array(
                                'name' => 'sub_title_space',
                                'label' => esc_html__('Margin(px)', 'powerlegal' ),
                                'type' => 'dimensions',
                                'allowed_dimensions' => 'vertical',
                                'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''],
                                'control_type' => 'responsive',
                                'size_units' => [ 'px' ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-wrap.layout1 .heading-subtitle, {{WRAPPER}} .pxl-heading-wrap.layout2 .sub-stroke-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                                ],
                            ),
                        ),
                        powerlegal_elementor_animation_opts([
                            'name'   => 'sub_title',
                            'label' => '',
                        ])
                    ),
                ),
                array(
                    'name' => 'highlight_section',
                    'label' => esc_html__('Highlight Text', 'powerlegal' ),
                    'tab' => 'content',
                    'controls' => array_merge(
                        array(
                            array(
                                'name' => 'text_list',
                                'label' => esc_html__('Text List', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::REPEATER,
                                'controls' => array(
                                    array(
                                        'name' => 'highlight_text',
                                        'label' => esc_html__('Text', 'powerlegal'),
                                        'type' => \Elementor\Controls_Manager::TEXT,
                                        'label_block' => true,
                                    ),
                                ),
                                'title_field' => '{{{ highlight_text }}}',
                            ),
                            array(
                                'name' => 'highlight_color',
                                'label' => esc_html__('Highlight Color', 'powerlegal' ),
                                'type' => 'color',
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-heading-wrap .heading-highlight' => 'color: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'highlight_typography',
                                'label' => esc_html__('Highlight Typography', 'powerlegal' ),
                                'type' => \Elementor\Group_Control_Typography::get_type(),
                                'control_type' => 'group',
                                'selector' => '{{WRAPPER}} .pxl-heading-wrap .heading-highlight',
                            ),
                        )
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);