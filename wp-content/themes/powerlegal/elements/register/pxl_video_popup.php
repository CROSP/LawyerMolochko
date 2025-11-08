<?php
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Utils;
pxl_add_custom_widget(
    array(
        'name' => 'pxl_video_popup',
        'title' => esc_html__('Pxl Video Popup', 'powerlegal' ),
        'icon' => 'eicon-play',
        'categories' => array('pxltheme-core'),
        'scripts' => array(

        ),
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'layout_section',
                    'label'    => esc_html__( 'Layout', 'powerlegal' ),
                    'tab'      => 'layout',
                    'controls' => array(
                        array(
                            'name' => 'layout',
                            'label' => esc_html__('Templates', 'powerlegal'),
                            'type' => 'layoutcontrol',
                            'default' => '1',
                            'options' => [
                                '1' => [
                                    'label' => esc_html__('Layout 1', 'powerlegal'),
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_video_popup-1.jpg'
                                ]
                            ],
                            'prefix_class' => 'pxl-video-layout-'
                        ),
                    ),
                ),
                array(
                    'name' => 'icon_section',
                    'label' => esc_html__('Settings', 'powerlegal' ),
                    'tab' => 'content',
                    'controls' => array(
                        array(
                            'name' => 'video_type',
                            'label' => esc_html__('Button Type', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::SELECT,
                            'default' => 'video-default',
                            'options' => [
                                'video-default' => esc_html__('Default', 'powerlegal' ),
                                'video-circle' => esc_html__('Circle', 'powerlegal' ),
                            ],
                        ),
                        array(
                            'name' => 'video_link',
                            'label' => esc_html__('Video URL', 'powerlegal'),
                            'type' => Controls_Manager::URL,
                            'default' => [
                                'url' => 'https://www.youtube.com/watch?v=occycDAXmAA',
                                'is_external' => 'on'
                            ]
                        ),
                        array(
                            'name' => 'description_text',
                            'label' => esc_html__('Description Text', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::TEXTAREA,
                            'default' => "",
                            'rows' => 5,
                            'show_label' => false,
                        ),
                    ),
                ),
                array(
                    'name' => 'section_style_button',
                    'label' => esc_html__('Button Style', 'powerlegal' ),
                    'tab' => 'style',
                    'controls' => array(
                        array(
                            'name' => 'text_align',
                            'label' => esc_html__('Alignment', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::CHOOSE,
                            'control_type' => 'responsive',
                            'options' => [
                                'flex-start' => [
                                    'title' => esc_html__('Left', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-left',
                                ],
                                'center' => [
                                    'title' => esc_html__('Center', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-center',
                                ],
                                'flex-end' => [
                                    'title' => esc_html__('Right', 'powerlegal' ),
                                    'icon' => 'eicon-text-align-right',
                                ],
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-content-inner' => 'justify-content: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'button_background',
                            'label' => esc_html__('Button Background', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button:after' => 'background-color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'hover_background',
                            'label' => esc_html__('Hover Background', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button:hover:after' => 'background-color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'icon_color',
                            'label' => esc_html__('Icon Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button i' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'hover_icon_color',
                            'label' => esc_html__('Hover Icon Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button:hover i' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'circle_color',
                            'label' => esc_html__('Circle Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button.video-circle:before' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name'  =>  'button_size',
                            'type'  =>  \Elementor\Controls_Manager::SLIDER,
                            'label' => esc_html__('Button Size', 'powerlegal'),
                            'size_units' => ['px'],
                            'control_type' => 'responsive',
                            'range' => [
                                'px' => [
                                    'min' => 50,
                                    'max' => 200,
                                ],
                            ],
                            'default'   => [
                                'unit' => 'px',
                                'size' => 54,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                            ],

                        ),
                        array(
                            'name'  =>  'icont_font_size',
                            'type'  =>  \Elementor\Controls_Manager::SLIDER,
                            'label' => esc_html__('Icon Font Size', 'powerlegal'),
                            'separator' => 'before',
                            'size_units' => ['px'],
                            'control_type' => 'responsive',
                            'range' => [
                                'px' => [
                                    'min' => 15,
                                    'max' => 80,
                                ],
                            ],
                            'default'   => [
                                'unit' => 'px',
                                'size' => 27,
                            ],
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .video-play-button i' => 'font-size: {{SIZE}}{{UNIT}};',
                            ],

                        ),
                    ),
                ),
                array(
                    'name' => 'section_style_description',
                    'label' => esc_html__('Description Style', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'controls' => array(
                        array(
                            'name' => 'description_color',
                            'label' => esc_html__('Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'default' => '',
                            'selectors' => [
                                '{{WRAPPER}} .pxl-video-popup .button-text' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'title_typography',
                            'type' => \Elementor\Group_Control_Typography::get_type(),
                            'control_type' => 'group',
                            'selector' => '{{WRAPPER}} .pxl-video-popup .button-text',
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);