<?php
$templates = powerlegal_get_templates_option('default', []) ;
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_tabs',
        'title'      => esc_html__( 'PXL Tabs', 'powerlegal' ),
        'icon'       => 'eicon-tabs',
        'categories' => array('pxltheme-core'),
        'scripts' => [
          'powerlegal-tabs',
        ],
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
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_tabs-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-tabs-layout-',
                        ),
                    )
                ),
                array(
                    'name'     => 'content_section',
                    'label'    => esc_html__( 'Content', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'template',
                            'label' => esc_html__('Select Style', 'powerlegal'),
                            'type' => 'select',
                            'options' => [
                                'style-1' => esc_html__( 'Style 1', 'powerlegal' ),
                                'style-2' => esc_html__( 'Style 2', 'powerlegal' ),
                                'style-3' => esc_html__( 'Style 3', 'powerlegal' )
                            ],
                            'default' => 'style-1' 
                        ),
                        
                        array(
                            'name' => 'active_tab',
                            'label' => esc_html__( 'Active Tab', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'default' => 1,
                            'separator' => 'after',
                        ),
                        array(
                            'name' => 'tabs_list',
                            'label' => esc_html__('Tabs List', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'controls' => array(
                                array(
                                    'name' => 'tab_title',
                                    'label' => esc_html__('Title', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'content_type',
                                    'label' => esc_html__('Content Type', 'powerlegal'),
                                    'type' => 'select',
                                    'options' => [
                                        'df' => esc_html__( 'Default', 'powerlegal' ),
                                        'template' => esc_html__( 'From Template Builder', 'powerlegal' )
                                    ],
                                    'default' => 'df' 
                                ),
                                array(
                                    'name' => 'content_template',
                                    'label' => esc_html__('Select Templates', 'powerlegal'),
                                    'description'        => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
                                    'type' => 'select',
                                    'options' => $templates,
                                    'default' => 'df',
                                    'condition' => ['content_type' => 'template'] 
                                ),
                                array(
                                    'name' => 'tab_content',
                                    'label' => esc_html__('Enter Content', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                                    'default' => '',
                                    'condition' => ['content_type' => 'df'] 
                                ),
                            ),
                            'title_field' => '{{{ tab_title }}}',
                        ),
                        array(
                            'name' => 'title_background',
                            'label' => esc_html__('Title Background', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-tabs .tab-title' => 'background-color: {{VALUE}};'
                            ],
                        ),
                        array(
                            'name' => 'title_active_background',
                            'label' => esc_html__('Active Background', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-tabs .tab-title.active' => 'background-color: {{VALUE}}; border-color: {{VALUE}};'
                            ],
                        ),
                        array(
                            'name' => 'title_color',
                            'label' => esc_html__('Title Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .tab-title' => 'color: {{VALUE}};'
                            ],
                        ),
                        array(
                            'name' => 'content_color',
                            'label' => esc_html__('Content Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .tab-content' => 'color: {{VALUE}};'
                            ],
                        ),
                        array(
                            'name'         => 'title_alignment',
                            'label'        => esc_html__( 'Title Alignment', 'powerlegal' ),
                            'type'         => 'choose',
                            'control_type' => 'responsive',
                            'options' => [
                                'start' => [
                                    'title' => esc_html__( 'Start', 'powerlegal' ),
                                    'icon'  => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => esc_html__( 'Center', 'powerlegal' ),
                                    'icon'  => 'eicon-text-align-center',
                                ],
                                'end' => [
                                    'title' => esc_html__( 'End', 'powerlegal' ),
                                    'icon'  => 'eicon-text-align-right',
                                ]
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-tabs .tabs-title' => 'justify-content: {{VALUE}};'
                            ],
                        ),
                    ),
                )
            )
        )
    ),
    powerlegal_get_class_widget_path()
);