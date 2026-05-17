<?php
// Register Accordion Widget
$templates = powerlegal_get_templates_option('default', []) ;
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_accordion',
        'title'      => esc_html__( 'PXL Accordion', 'powerlegal' ),
        'icon'       => 'eicon-accordion',
        'categories' => array('pxltheme-core'),
        'scripts'    => array(
            'powerlegal-accordion'
        ),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'source_section',
                    'label'    => esc_html__( 'Source Settings', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'style',
                            'label' => esc_html__('Style', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'options' => [
                                'style1' => esc_html__( 'Style 1', 'powerlegal' ),
                                'style2' => esc_html__( 'Style 2', 'powerlegal' ),
                                'style3' => esc_html__( 'Style 3', 'powerlegal' ),
                                'style4' => esc_html__( 'Style 4', 'powerlegal' ),
                            ],
                            'default' => 'style1',
                        ),
                        array(
                            'name' => 'active_section',
                            'label' => esc_html__('Active section', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'separator' => 'after',
                            'default' => '1',
                        ),
                        array(
                            'name' => 'ac_items',
                            'label' => esc_html__('Accordion Items', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'controls' => array(
                                array(
                                    'name' => 'ac_title',
                                    'label' => esc_html__('Title', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                                    'rows' => 3,
                                ),
                                array(
                                    'name'             => 'selected_icon',
                                    'label'            => esc_html__( 'Left Title Icon', 'powerlegal' ),
                                    'type'             => 'icons',
                                    'default'          => [
                                        'library' => 'pxli',
                                        'value'   => 'pxli-auction'
                                    ],
                                    'description' => 'Use for style4 only'
                                ),
                                array(
                                    'name' => 'ac_content_type',
                                    'label' => esc_html__( 'Content Type', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::SELECT,
                                    'default' => 'text_editor',
                                    'options' => [
                                        'text_editor' => esc_html__( 'Text Editor', 'powerlegal' ),
                                        'template' => esc_html__( 'Template', 'powerlegal' ),
                                    ],
                                ),
                                array(
                                    'name' => 'ac_content',
                                    'label' => esc_html__('Content', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                                    'show_label' => false,
                                    'condition' => [
                                        'ac_content_type' => 'text_editor'
                                    ],
                                ),
                                array(
                                    'name' => 'ac_content_template',
                                    'label' => esc_html__('Select Templates', 'powerlegal'),
                                    'description'        => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
                                    'type' => \Elementor\Controls_Manager::SELECT,
                                    'default' => '',
                                    'options' => $templates,
                                    'condition' => [
                                        'ac_content_type' => 'template'
                                    ],
                                ),
                            ),
                            'default' => [
                                [
                                    'ac_title'   => esc_html__( 'FAQ Title #1', 'powerlegal' ),
                                    'ac_content' => esc_html__( 'Lorem ipsum dolor sit amet consecte tur adipiscing elit sed do eiu smod tempor incididunt ut labore.', 'powerlegal' ),
                                ],
                                [
                                    'ac_title'   => esc_html__( 'FAQ Title #2', 'powerlegal' ),
                                    'ac_content' => esc_html__( 'Lorem ipsum dolor sit amet consecte tur adipiscing elit sed do eiu smod tempor incididunt ut labore.', 'powerlegal' ),
                                ],
                            ],
                            'title_field' => '{{{ ac_title }}}',
                            'separator' => 'after',
                        ),
                        
                    )
                ),
                array(
                    'name'     => 'style_section',
                    'label'    => esc_html__( 'Style', 'powerlegal' ),
                    'tab'      => 'style',
                    'controls' => array_merge(
                        array(
                            array(
                                'name' => 'title_color',
                                'label' => esc_html__('Title Color', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-accordion .ac-item .ac-title' => 'color: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'title_typography',
                                'label' => esc_html__('Title Typography', 'powerlegal' ),
                                'type' => \Elementor\Group_Control_Typography::get_type(),
                                'control_type' => 'group',
                                'selector' => '{{WRAPPER}} .pxl-accordion .ac-item .ac-title',
                            ),
                            array(
                                'name' => 'desc_color',
                                'label' => esc_html__('Description Color', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-accordion .ac-item .ac-desc' => 'color: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'desc_typography',
                                'label' => esc_html__('Description Typography', 'powerlegal' ),
                                'type' => \Elementor\Group_Control_Typography::get_type(),
                                'control_type' => 'group',
                                'selector' => '{{WRAPPER}} .pxl-accordion .ac-item .ac-desc',
                            ),
                        )
                    ),
                ),
                
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);