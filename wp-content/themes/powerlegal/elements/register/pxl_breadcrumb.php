<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_breadcrumb',
        'title' => esc_html__('PXL Breadcrumb', 'powerlegal' ),
        'icon' => 'eicon-navigation-horizontal',
        'categories' => array('pxltheme-core'),
        'params' => array(
            'sections' => array(
                array(
                    'name' => 'content_section',
                    'label' => esc_html__('Style', 'powerlegal' ),
                    'tab' => 'style',
                    'controls' => array(
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
                                '{{WRAPPER}} .pxl-brc-wrap' => 'justify-content: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'brc_color',
                            'label' => esc_html__('Text Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-brc-wrap, .pxl-brc-wrap .br-item' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'link_color',
                            'label' => esc_html__('Link Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-brc-wrap a' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'link_color_hover',
                            'label' => esc_html__('Link Color Hover', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-brc-wrap a:hover' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name'             => 'selected_icon',
                            'label'            => esc_html__( 'Divider Icon', 'powerlegal' ),
                            'type'             => 'icons',
                            'default'          => [
                                'library' => 'pxli',
                                'value'   => 'pxli-long-arrow-right'
                            ],
                        ),
                        array(
                            'name' => 'icon_color',
                            'label' => esc_html__('Icon Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-brc-wrap .br-divider' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'brc_typography',
                            'label' => esc_html__('Typography', 'powerlegal' ),
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pxl-brc-wrap, {{WRAPPER}} .pxl-brc-wrap a, {{WRAPPER}} .pxl-brc-wrap .br-item, {{WRAPPER}} .pxl-brc-wrap .br-divider',
                        ),
                         
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);