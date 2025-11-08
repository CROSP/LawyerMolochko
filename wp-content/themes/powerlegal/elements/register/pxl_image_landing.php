<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_image_landing',
        'title' => esc_html__('PXL Image Landing', 'powerlegal' ),
        'icon' => 'eicon-image',
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
                            'label'   => esc_html__( 'Layout', 'powerlegal' ),
                            'type'    => 'layoutcontrol',
                            'default' => '1',
                            'options' => [
                                '1' => [
                                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_image_landing-1.jpg'
                                ],
                            ],
                        ),
                    )
                ),
                array(
                    'name' => 'content_section',
                    'label' => esc_html__('Content', 'powerlegal' ),
                    'tab' => 'content',
                    'controls' => array(
                        array(
                            'name' => 'selected_image',
                            'label' => esc_html__('Image', 'powerlegal' ),
                            'type' => 'media',
                        ),
                        array(
                            'name' => 'title_text',
                            'label' => esc_html__('Title Text', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'default' => esc_html__('Homepage', 'powerlegal'),
                        ),
                        array(
                            'name' => 'link_type',
                            'label' => esc_html__('Link Type', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'options'       => [
                                'url'   => esc_html__('URL', 'powerlegal'),
                                'page'  => esc_html__('Existing Page', 'powerlegal'),
                            ],
                            'default'       => 'url',
                        ),
                        array(
                            'name' => 'link',
                            'label' => esc_html__('Link', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::URL,
                            'placeholder' => esc_html__('https://your-link.com', 'powerlegal' ),
                            'condition'     => [
                                'link_type'     => 'url',
                            ],
                            'default' => [
                                'url' => '#',
                            ],
                        ),
                        array(
                            'name' => 'page_link',
                            'label' => esc_html__('Existing Page', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::SELECT2,
                            'options'       => pxl_get_all_page(),
                            'condition'     => [
                                'link_type'     => 'page',
                            ],
                            'multiple'      => false,
                            'label_block'   => true,
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);