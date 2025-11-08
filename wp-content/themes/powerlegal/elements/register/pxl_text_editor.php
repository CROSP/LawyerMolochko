<?php
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
$text_columns = range( 1, 10 );
$text_columns = array_combine( $text_columns, $text_columns );
$text_columns[''] = esc_html__( 'Default', 'powerlegal' );
pxl_add_custom_widget(
    array(
        'name' => 'pxl_text_editor',
        'title' => esc_html__( 'PXL Text Editor', 'powerlegal' ),
        'icon' => 'eicon-text',
        'categories' => array('pxltheme-core'),
        'params' => array(
            'sections' => array(
                array(
                    'name' => 'editor_section',
                    'label' => esc_html__( 'Text Editor', 'powerlegal' ),
                    'tab' => Controls_Manager::TAB_CONTENT,
                    'controls' => array(
                        array(
                            'name' => 'text_editor',
                            'label' => '',
                            'type' => Controls_Manager::WYSIWYG,
                            'default' => esc_html__( 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'powerlegal' ),
                        ),
                        array(
                            'name' => 'dropcap',
                            'label' => esc_html__('Dropcap', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SWITCHER,
                            'default' => 'false',
                        ),
                        array(
                            'name' => 'dropcap_color',
                            'label' => esc_html__( 'Dropcap Text Color', 'powerlegal' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '',
                            'condition' => [
                                'dropcap' => 'true',
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-text-editor .pxl-dropcap::first-letter, {{WRAPPER}} .pxl-text-editor .pxl-dropcap > *:first-child::first-letter' => 'color: {{VALUE}};',
                            ],
                        ),
                    ),
                ),
                array(
                    'name' => 'section_style_content',
                    'label' => esc_html__( 'Content Alignment', 'powerlegal' ),
                    'tab' => Controls_Manager::TAB_STYLE,
                    'controls' => array(
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
                                '{{WRAPPER}} .pxl-text-editor' => 'max-width: {{SIZE}}{{UNIT}};',
                            ],
                        ),
                        array(
                          'name' => 'align',
                            'label' => esc_html__( 'Alignment', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::CHOOSE,
                            'control_type' => 'responsive',
                            'options' => [
                                'start' => [
                                    'title' => esc_html__( 'Left', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => esc_html__( 'Center', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-center',
                                ],
                                'end' => [
                                    'title' => esc_html__( 'Right', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-right',
                                ]
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-text-editor-wrap' => 'justify-content: {{VALUE}};',
                                '{{WRAPPER}} .pxl-text-editor' => 'text-align: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'text_color',
                            'label' => esc_html__( 'Text Color', 'powerlegal' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-text-editor' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'link_color',
                            'label' => esc_html__( 'Link Color', 'powerlegal' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-text-editor a' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .pxl-text-editor a.link-underline' => 'border-color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'link_color_hover',
                            'label' => esc_html__( 'Link Color Hover', 'powerlegal' ),
                            'type' => Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-text-editor a:hover' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'typography',
                            'type' => Group_Control_Typography::get_type(),
                            'label' => esc_html__( 'Text Typography', 'powerlegal' ),
                            'selector' => '{{WRAPPER}} .pxl-text-editor, {{WRAPPER}} .pxl-text-editor h1, {{WRAPPER}} .pxl-text-editor h2, {{WRAPPER}} .pxl-text-editor h3, {{WRAPPER}} .pxl-text-editor h4, {{WRAPPER}} .pxl-text-editor h5, {{WRAPPER}} .pxl-text-editor h6',
                            'control_type' => 'group',
                        ),
                        array(
                            'name' => 'link_typography',
                            'type' => Group_Control_Typography::get_type(),
                            'label' => esc_html__( 'Link Typography', 'powerlegal' ),
                            'selector' => '{{WRAPPER}} .pxl-text-editor a',
                            'control_type' => 'group',
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);