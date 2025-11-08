<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_mailchimp',
        'title' => esc_html__('PXL Mailchimp', 'powerlegal'),
        'icon' => 'eicon-email-field',
        'categories' => array('pxltheme-core'),
        'params' => array(
            'sections' => array(
                array(
                    'name' => 'section_style',
                    'label' => esc_html__('Style', 'powerlegal'),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'style',
                            'label' => esc_html__('Style', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'options' => [
                                'style-default' => esc_html__('Default', 'powerlegal' ),
                                'style-1' => esc_html__('Style 1', 'powerlegal' ),
                            ],
                            'default' => 'style-default',
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);