<?php
// Register Widget
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_single_info',
        'title'      => esc_html__( 'PXL Single Info', 'powerlegal' ),
        'icon' => 'eicon-price-list',
        'categories' => array('pxltheme-core'),
        'scripts'    => [],
        'params'     => array(
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
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_single_info-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-single-info-layout-',
                        ),
                    )
                ),
                array(
                    'name'     => 'content_section',
                    'label'    => esc_html__( 'Content Settings', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'el_title',
                            'label' => esc_html__('Element Title', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXTAREA,
                            'label_block' => true,
                        ),
                        array(
                            'name' => 'single_info_items',
                            'label' => esc_html__('List Items', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'controls' => array(
                                array(
                                    'name' => 'info_icon',
                                    'label' => esc_html__('Icon', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::ICONS,
                                    'fa4compatibility' => 'icon',
                                    'default' => [
                                        'value' => 'fas fa-star',
                                        'library' => 'fa-solid',
                                    ],
                                ),
                                array(
                                    'name' => 'info_label',
                                    'label' => esc_html__('Label', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                ),
                                array(
                                    'name' => 'info_text',
                                    'label' => esc_html__('Text', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                ),
                            ),
                            'title_field' => '{{{ info_label }}}',
                            'separator' => 'after',
                        ),
                        array(
                            'name' => 'share_icon',
                            'label' => esc_html__('Share Label Icon', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::ICONS,
                            'fa4compatibility' => 'icon',
                            'default' => [
                                'value' => 'fas fa-star',
                                'library' => 'fa-solid',
                            ],
                        ),
                    )
                )
            )
        )
    ),
    powerlegal_get_class_widget_path()
);