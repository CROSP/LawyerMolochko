<?php
pxl_add_custom_widget(
    array(
        'name' => 'pxl_testimonial_carousel',
        'title' => esc_html__('PXL Testimonial Carousel', 'powerlegal'),
        'icon' => 'eicon-blockquote',
        'categories' => array('pxltheme-core'),
        'scripts' => [
            'swiper',
            'powerlegal-swiper',
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
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_testimonial_carousel-1.jpg'
                                ],
                                '2' => [
                                    'label' => esc_html__( 'Layout 2', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_testimonial_carousel-2.jpg'
                                ],
                                '3' => [
                                    'label' => esc_html__( 'Layout 3', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_testimonial_carousel-3.jpg'
                                ],
                                '4' => [
                                    'label' => esc_html__( 'Layout 4', 'powerlegal' ),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_testimonial_carousel-4.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-testimonial-carousel-layout-',
                        ),
                        
                    ),
                ),
                array(
                    'name' => 'section_list',
                    'label' => esc_html__('Content', 'powerlegal'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array(
                        array(
                            'name' => 'content_list',
                            'label' => esc_html__('Testimonial Items', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'default' => [],
                            'controls' => array(
                                array(
                                    'name' => 'image',
                                    'label' => esc_html__('Image', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::MEDIA,
                                ),
                                array(
                                    'name' => 'title',
                                    'label' => esc_html__('Name', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'position',
                                    'label' => esc_html__('Position', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                ),
                                array(
                                    'name' => 'description',
                                    'label' => esc_html__('Description', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                                    'rows' => 10,
                                ),
                                array(
                                    'name' => 'rating',
                                    'label' => esc_html__('Rating', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::SELECT,
                                    'default' => 'none',
                                    'options' => [
                                        'none' => esc_html__('None', 'powerlegal' ),
                                        'star1' => esc_html__('1 Star', 'powerlegal' ),
                                        'star2' => esc_html__('2 Star', 'powerlegal' ),
                                        'star3' => esc_html__('3 Star', 'powerlegal' ),
                                        'star4' => esc_html__('4 Star', 'powerlegal' ),
                                        'star5' => esc_html__('5 Star', 'powerlegal' ),
                                    ],
                                ),
                            ),
                            'title_field' => '{{{ title }}}',
                        ),
                    ),
                ),
                array(
                    'name' => 'carousel_setting',
                    'label' => esc_html__('Carousel Settings', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
                    'controls' => array_merge(
                        powerlegal_carousel_column_settings(),
                        array( 
                            array(
                                'name' => 'slides_to_scroll',
                                'label' => esc_html__('Slides to scroll', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::SELECT,
                                'default' => '1',
                                'options' => [
                                    '1' => '1',
                                    '2' => '2',
                                    '3' => '3',
                                    '4' => '4',
                                    '5' => '5',
                                    '6' => '6',
                                ],
                            ),
                            array(
                                'name' => 'arrows',
                                'label' => esc_html__('Show Arrows', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SWITCHER,
                            ),
                            array(
                                'name' => 'dots',
                                'label' => esc_html__('Show Dots', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SWITCHER,
                            ),
                            array(
                                'name' => 'dots_color',
                                'label' => esc_html__('Dots Color', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-swiper-slider .pxl-swiper-dots .pxl-swiper-pagination-bullet:after' => 'background-color: {{VALUE}};',
                                ],
                                'condition' => [
                                    'dots' => "true",
                                ],
                            ),
                            array(
                                'name' => 'dots_color_active',
                                'label' => esc_html__('Active Color', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::COLOR,
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-swiper-slider .pxl-swiper-dots .pxl-swiper-pagination-bullet.swiper-pagination-bullet-active:after' => 'background-color: {{VALUE}};',
                                ],
                                'condition' => [
                                    'dots' => "true",
                                ],
                            ),
                            array(
                                'name' => 'pause_on_hover',
                                'label' => esc_html__('Pause on Hover', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SWITCHER,
                            ),
                            array(
                                'name' => 'autoplay',
                                'label' => esc_html__('Autoplay', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SWITCHER,
                            ),
                            array(
                                'name' => 'autoplay_speed',
                                'label' => esc_html__('Autoplay Speed', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::NUMBER,
                                'default' => 5000,
                                'condition' => [
                                    'autoplay' => 'true'
                                ]
                            ),
                            array(
                                'name' => 'infinite',
                                'label' => esc_html__('Infinite Loop', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SWITCHER,
                            ),
                            array(
                                'name' => 'speed',
                                'label' => esc_html__('Animation Speed', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::NUMBER,
                                'default' => 400,
                            ),
                        )
                    ),
                ),
                array(
                    'name' => 'style_section',
                    'label' => esc_html__('Style', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array(
                        array(
                            'name' => 'quote_icon',
                            'label' => esc_html__('Quote Icon', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::ICONS,
                            'fa4compatibility' => 'icon',
                            'condition' => ['layout' => '2']
                        ),
                        array(
                            'name' => 'bg_color',
                            'label' => esc_html__('Background Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-testimonial-carousel' => 'background-color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'title_color',
                            'label' => esc_html__('Title Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-testimonial-carousel .item-title' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'position_color',
                            'label' => esc_html__('Position Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-testimonial-carousel .item-position' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'description_color',
                            'label' => esc_html__('Description Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-testimonial-carousel .item-desc' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'quote_color',
                            'label' => esc_html__('Quote Icon Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-testimonial-carousel .pxl-icon' => 'color: {{VALUE}};',
                            ],
                        ),
                        
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);