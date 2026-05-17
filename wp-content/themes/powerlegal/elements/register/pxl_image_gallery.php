<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_image_gallery',
        'title' => esc_html__('PXL Image Gallery', 'powerlegal'),
        'icon' => 'eicon-gallery-grid',
        'categories' => array('pxltheme-core'),
        'scripts' => [
            'imagesloaded',
            'isotope',
            'powerlegal-post-grid',
        ],
        'params' => array(
            'sections' => array(
                array(
                    'name' => 'layout_section',
                    'label' => esc_html__('Layout', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_LAYOUT,
                    'controls' => array(
                        array(
                            'name'    => 'layout',
                            'label'   => esc_html__( 'Layout', 'powerlegal' ),
                            'type'    => 'layoutcontrol',
                            'default' => '1',
                            'options' => [
                                '1' => [
                                    'label' => esc_html__( 'Layout 1', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_image_gallery-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-image-gallery-layout-',
                        ),
                    ),
                ),
                array(
                    'name' => 'grid_section',
                    'label' => esc_html__('Image Gallery', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array_merge(
                        array(
                            array(
                                'name' => 'wp_gallery',
                                'label' => __( 'Add Images', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::GALLERY,
                                'show_label' => false,
                                'dynamic' => [
                                    'active' => true,
                                ],
                            ),
                            array(
                                'name'    => 'layout_mode',
                                'label'   => esc_html__( 'Layout Mode', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::SELECT,
                                'options' => [
                                    'fitRows' => esc_html__( 'Basic Grid', 'powerlegal' ),
                                    'masonry' => esc_html__( 'Masonry Grid', 'powerlegal' ),
                                ],
                                'default'   => 'fitRows'
                            ),
                            array(
                                'name' => 'img_size',
                                'label' => esc_html__('Image Size', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::TEXT,
                                'description' =>  esc_html__('Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Default: 370x300 (Width x Height)).', 'powerlegal')
                            ),
                            array(
                                'name' => 'gallery_rand',
                                'label' => __( 'Order By', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::SELECT,
                                'options' => [
                                    '' => __( 'Default', 'powerlegal' ),
                                    'rand' => __( 'Random', 'powerlegal' ),
                                ],
                                'default' => '',
                            )
                        ),
                        powerlegal_grid_column_settings(),
                        powerlegal_elementor_animation_opts([
                            'name'   => 'item',
                            'label' => esc_html__('Item', 'powerlegal'),
                        ])
                    ),
                ),
                array(
                    'name' => 'gallery_images_section',
                    'label' => esc_html__('Images', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'gap',
                            'label' => esc_html__('Image Gap', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::NUMBER,
                            'control_type' => 'responsive',
                            'default' => 15,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-grid .pxl-grid-inner' => 'margin-left: -{{VALUE}}px; margin-right: -{{VALUE}}px;',
                                '{{WRAPPER}} .pxl-grid .grid-item' => 'padding-left: {{VALUE}}px; padding-right: {{VALUE}}px; margin-top: {{VALUE}}px; margin-bottom: {{VALUE}}px;',
                                '{{WRAPPER}} .pxl-grid .grid-sizer' => 'padding-left: {{VALUE}}px; padding-right: {{VALUE}}px;',
                            ],
                        ),
                    ),
                ),
                array(
                    'name' => 'caption_section',
                    'label' => esc_html__('Caption', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'gallery_display_caption',
                            'label' => __( 'Display', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'default' => 'none',
                            'options' => [
                                'none' => __( 'Hide', 'powerlegal' ),
                                '' => __( 'Show', 'powerlegal' ),
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .grid-item .image-caption' => 'display: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'caption_align',
                            'label' => __( 'Alignment', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::CHOOSE,
                            'options' => [
                                'left' => [
                                    'title' => __( 'Left', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => __( 'Center', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-center',
                                ],
                                'right' => [
                                    'title' => __( 'Right', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-right',
                                ],
                            ],
                            'default' => 'center',
                            'selectors' => [
                                '{{WRAPPER}} .grid-item .image-caption' => 'text-align: {{VALUE}};',
                            ],
                            'condition' => [
                                'gallery_display_caption' => '',
                            ],
                        ),
                        array(
                            'name' => 'caption_color',
                            'label' => __( 'Text Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .grid-item .image-caption' => 'color: {{VALUE}};',
                            ],
                            'condition' => [
                                'gallery_display_caption' => '',
                            ],
                        ),
                        array(
                            'name' => 'caption_typography',
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .grid-item .image-caption',
                            'condition' => [
                                'gallery_display_caption' => '',
                            ],
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);