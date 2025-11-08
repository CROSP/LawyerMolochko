<?php
//Register Counter Widget
 pxl_add_custom_widget(
    array(
        'name'       => 'pxl_counter',
        'title'      => esc_html__('PXL Counter', 'powerlegal'),
        'icon'       => 'eicon-counter-circle',
        'categories' => array('pxltheme-core'),
        'scripts'    => array(
            'odometer',
            'powerlegal-counter',
        ),
        'styles' => array('odometer'),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'layout_section',
                    'label'    => esc_html__( 'Layout', 'powerlegal' ),
                    'tab'      => 'layout',
                    'controls' => array(
                        array(
                            'name'         => 'layout',
                            'label'        => esc_html__( 'Templates', 'powerlegal' ),
                            'type'         => 'layoutcontrol',
                            'default'      => '1',
                            'options'      => [
                                '1' => [
                                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_counter-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-counter-layout',
                        ) 
                    ),
                ),
                array(
                    'name'     => 'section_counter',
                    'label'    => esc_html__('Counter', 'powerlegal'),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'starting_number',
                            'label' => esc_html__('Starting Number', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'default' => 1,
                        ),
                        array(
                            'name' => 'ending_number',
                            'label' => esc_html__('Ending Number', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'default' => 100,
                        ),
                        array(
                            'name' => 'prefix',
                            'label' => esc_html__('Number Prefix', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'default' => '',
                        ),
                        array(
                            'name' => 'suffix',
                            'label' => esc_html__('Number Suffix', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'default' => '',
                        ),
                        array(
                            'name' => 'duration',
                            'label' => esc_html__('Animation Duration', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'default' => 2000,
                            'min' => 100,
                            'step' => 100,
                            'selectors' => [
                                '{{WRAPPER}} .odometer-ribbon-inner' => 'transition-duration: {{VALUE}}ms !important;',
                            ],
                        ),
                        array(
                            'name' => 'thousand_separator',
                            'label' => esc_html__('Thousand Separator', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SWITCHER,
                            'default' => 'false',
                        ),
                        array(
                            'name' => 'thousand_separator_char',
                            'label' => esc_html__('Separator', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'condition' => [
                                'thousand_separator' => 'true',
                            ],
                            'options' => [
                                '' => 'Default',
                                '(.ddd),dd' => 'Dot',
                                '(,ddd).dd' => 'Comma',
                                '( ddd),dd' => 'Space',
                            ],
                            'default' => '',
                        ),
                        array(
                            'name' => 'title',
                            'label' => esc_html__('Title', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'label_block' => true,
                        ),
                        array(
                            'name'         => 'align',
                            'label'        => esc_html__( 'Alignment', 'powerlegal' ),
                            'type'         => 'choose',
                            'control_type' => 'responsive',
                            'default'      => '',
                            'options' => [
                                'left' => [
                                    'title' => esc_html__( 'Left', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => esc_html__( 'Center', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-center',
                                ],
                                'right' => [
                                    'title' => esc_html__( 'Right', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-right',
                                ]
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-counter' => 'text-align: {{VALUE}};'
                            ],
                        ),
                    )
                ),
                array(
                    'name' => 'section_number',
                    'label' => esc_html__( 'Number', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'number_color',
                            'label' => esc_html__( 'Text Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .counter-number' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'number_typography',
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .counter-number',
                        ),
                    ),
                ),
                array(
                    'name' => 'section_title',
                    'label' => esc_html__( 'Title', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'title_color',
                            'label' => esc_html__( 'Text Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .counter-title' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'typography_title',
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .counter-title',
                        ),
                        array(
                            'name' => 'title_top_space',
                            'label' => esc_html__('Top Spacing', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::SLIDER,
                            'control_type' => 'responsive',
                            'size_units' => [ 'px' ],
                            'range' => [
                                'px' => [
                                    'min' => 0,
                                    'max' => 100,
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .counter-title' => 'margin-top: {{SIZE}}{{UNIT}};',
                            ],
                        ),
                    ),
                ),
                array(
                    'name' => 'suffix_title',
                    'label' => esc_html__( 'Suffix', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'suffix_color',
                            'label' => esc_html__( 'Text Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .counter-number-suffix' => 'color: {{VALUE}} !important;',
                            ],
                        ),
                        array(
                            'name' => 'typography_suffix',
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .counter-number-suffix',
                        ),
                    ),
                ),
            )
        )
    ),
    powerlegal_get_class_widget_path()
);