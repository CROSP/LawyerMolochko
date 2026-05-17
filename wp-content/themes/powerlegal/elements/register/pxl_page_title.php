<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_page_title',
        'title' => esc_html__('PXL Page Title', 'powerlegal' ),
        'icon' => 'eicon-t-letter',
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
                                'left' => [
                                    'title' => esc_html__( 'Start', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => esc_html__( 'Center', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-center',
                                ],
                                'right' => [
                                    'title' => esc_html__( 'End', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-right',
                                ]
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pt-wrap' => 'text-align: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'title_color',
                            'label' => esc_html__('Title Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pt-wrap .main-title' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'pt_typography',
                            'label' => esc_html__('Title Typography', 'powerlegal' ),
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pxl-pt-wrap .main-title',
                        ),
                        array(
                            'name' => 'sub_title_color',
                            'label' => esc_html__('Sub Title Color', 'powerlegal' ),
                            'type' => 'color',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-pt-wrap .sub-title' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'pst_typography',
                            'label' => esc_html__('Sub Title Typography', 'powerlegal' ),
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pxl-pt-wrap .sub-title',
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);