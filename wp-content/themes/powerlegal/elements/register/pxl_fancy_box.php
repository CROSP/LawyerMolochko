<?php
// Register Fancy Box Widget
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_fancy_box',
        'title'      => esc_html__( 'PXL Fancy Box', 'powerlegal' ),
        'icon'       => 'eicon-icon-box',
        'categories' => array('pxltheme-core'),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'layout_section',
                    'label'    => esc_html__( 'Layout', 'powerlegal' ),
                    'tab'      => 'layout',
                    'controls' => array(
                        array(
                            'name'    => 'layout',
                            'label'   => esc_html__( 'Templates', 'powerlegal' ),
                            'type'    => 'layoutcontrol',
                            'default' => '1',
                            'options' => [
                                '1' => [
                                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_fancy_box-1.jpg'
                                ],
                                '2' => [
                                    'label' => esc_html__( 'Layout 2', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_fancy_box-2.jpg'
                                ],
                                '3' => [
                                    'label' => esc_html__( 'Layout 3', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_fancy_box-3.jpg'
                                ],
                                '4' => [
                                    'label' => esc_html__( 'Layout 4', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_fancy_box-4.jpg'
                                ],
                                '5' => [
                                    'label' => esc_html__( 'Layout 5', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_fancy_box-5.jpg'
                                ],
                            ],
                        )
                    )
                ),
                array(
                    'name'     => 'content_section',
                    'label'    => esc_html__( 'Content', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name'             => 'selected_icon',
                            'label'            => esc_html__( 'Icon', 'powerlegal' ),
                            'type'             => 'icons',
                            'default'          => [
                                'library' => 'flaticon',
                                'value'   => 'flaticon-calling'  
                            ],
                            'condition' => [
                                'layout!'    => ['2']
                            ],
                        ),
                        array(
                            'name'  => 'icon_size',
                            'label' => esc_html__( 'Icon Size', 'powerlegal' ),
                            'type'  => 'slider',
                            'range' => [
                                'px' => [
                                    'min' => 15,
                                    'max' => 300,
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-fancy-box .box-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                                '{{WRAPPER}} .pxl-fancy-box .box-icon svg' => 'width: {{SIZE}}{{UNIT}} !important;',
                            ],
                            'condition' => [
                                'layout!'    => ['2']
                            ],
                        ),
                        array(
                            'name' => 'icon_margin',
                            'label' => esc_html__('Icon Margin', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::DIMENSIONS,
                            'size_units' => [ 'px' ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-fancy-box .box-icon i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                                '{{WRAPPER}} .pxl-fancy-box .box-icon svg' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                            'control_type' => 'responsive',
                            'condition' => [
                                'layout!'    => ['2']
                            ],
                        ),
                        array(
                            'name'             => 'selected_img',
                            'label'            => esc_html__( 'Image', 'powerlegal' ),
                            'type'             => 'media',
                            'default'          => '',
                            'condition' => [
                                'layout'    => ['2','4']
                            ],
                        ),
                        array(
                            'name'     => 'title',
                            'label'    => esc_html__('Title', 'powerlegal'),
                            'type'     => 'textarea',
                            'default'  => esc_html__('Your Title', 'powerlegal')
                        ),
                        array(
                            'name'     => 'description',
                            'label'    => esc_html__('Description', 'powerlegal'),
                            'type'     => 'textarea',
                            'default'  => esc_html__('Lorem Ipsum is simply dummy text of the printing and typesetting industry', 'powerlegal'),
                        ),
                        array(
                            'name'        => 'link',
                            'label'       => esc_html__( 'Custom Link', 'powerlegal' ),
                            'type'        => 'url',
                            'placeholder' => esc_html__( 'https://your-link.com', 'powerlegal' ),
                            'default'     => [
                                'url'         => '#',
                                'is_external' => 'on'
                            ],
                            'condition' => [
                                'layout!'    => ['2']
                            ]
                        ),
                        array(
                            'name' => 'button_text',
                            'label' => esc_html__('Button Text', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'label_block' => true,
                            'condition' => [
                                'layout'    => ['4']
                            ]
                        ),
                        array(
                            'name' => 'item_index',
                            'label' => esc_html__('Item Index', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'default' => '1',
                            'min' => '1',
                            'max' => '7',
                            'condition' => [
                                'layout' => ["3"],
                            ],
                        ),
                    )
                ),
            )
        )
    ),
    powerlegal_get_class_widget_path()
);