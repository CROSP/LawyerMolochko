<?php
// Register Button Widget
pxl_add_custom_widget(
    array(
        'name' => 'pxl_cart',
        'title' => esc_html__('Pxl Cart', 'powerlegal' ),
        'icon' => 'eicon-cart',
        'categories' => array('pxltheme-core'),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'content_section',
                    'label'    => esc_html__( 'Setting', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'icon_type',
                            'label' => esc_html__('Select Icon Type', 'powerlegal'),
                            'type' => 'select',
                            'options' => [
                                'none' => esc_html__('None', 'powerlegal'),
                                'lib' => esc_html__('Library', 'powerlegal'),
                                'custom' => esc_html__('Custom', 'powerlegal'),
                            ],
                            'default' => 'lib'
                        ),
                        array(
                            'name'             => 'selected_icon',
                            'label'            => esc_html__( 'Icon', 'powerlegal' ),
                            'type'             => 'icons',
                            'default'          => [
                                'library' => 'pxli',
                                'value'   => 'pxli-shopping-cart'
                            ],
                            'condition' => ['icon_type' => 'lib']
                        ),
                        array(
                            'name'  => 'icon_size',
                            'label' => esc_html__( 'Icon Size(px)', 'powerlegal' ),
                            'type'  => 'slider',
                            'range' => [
                                'px' => [
                                    'min' => 15,
                                    'max' => 300,
                                ],
                            ],
                            'condition' => ['icon_type' => 'lib'],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                                '{{WRAPPER}} .pxl-cart-icon svg' => 'width: {{SIZE}}{{UNIT}};',
                            ],

                        ),
                        array(
                            'name' => 'icon_margin',
                            'label' => esc_html__('Icon Margin(px)', 'powerlegal' ),
                            'type' => 'dimensions',
                            'control_type' => 'responsive',
                            'size_units' => [ 'px' ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                            'condition' => ['icon_type!' => 'none'],
                        ),
                        array(
                            'name' => 'icon_color',
                            'label' => esc_html__('Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .pxl-cart-icon svg' => 'fill: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'icon_color_hover',
                            'label' => esc_html__('Hover Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart:hover' => 'color: {{VALUE}};',
                                '{{WRAPPER}} .pxl-cart:hover pxl-cart-icon svg' => 'fill: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'count_background',
                            'label' => esc_html__('Count Background', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart-icon .pxl-cart-count' => 'background-color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'count_color',
                            'label' => esc_html__('Count Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-cart-icon .pxl-cart-count' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name'         => 'align',
                            'label'        => esc_html__( 'Alignment', 'powerlegal' ),
                            'type'         => 'choose',
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
                                '{{WRAPPER}} .pxl-cart-wrap' => 'justify-content: {{VALUE}};',
                            ],
                        ),
                    )
                ),  
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);