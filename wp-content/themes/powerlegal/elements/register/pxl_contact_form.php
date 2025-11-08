<?php
// Register Contact Form 7 Widget
if(class_exists('WPCF7')) {
    $cf7 = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');
    $contact_forms = array();
    if ($cf7) {
        foreach ($cf7 as $cform) {
            $contact_forms[$cform->post_name] = $cform->post_title;
        }
    } else {
        $contact_forms[esc_html__('No contact forms found', 'powerlegal')] = 0;
    }

    pxl_add_custom_widget(
        array(
            'name'       => 'pxl_contact_form',
            'title'      => esc_html__('Pxl Contact Form 7', 'powerlegal'),
            'icon'       => 'eicon-form-horizontal',
            'categories' => array('pxltheme-core'),
            'scripts'    => array(),
            'params'     => array(
                'sections' => array(
                    array(
                        'name' => 'source_section',
                        'label' => esc_html__('Source Settings', 'powerlegal'),
                        'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                        'controls' => array(
                            array(
                                'name' => 'ctf7_slug',
                                'label' => esc_html__('Select Form', 'powerlegal'),
                                'type' => \Elementor\Controls_Manager::SELECT,
                                'options' => $contact_forms,
                                'default' => array_key_first($contact_forms),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'section_style_button',
                        'label' => esc_html__('Button Style', 'powerlegal' ),
                        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                        'controls' => array(
                            array(
                                'name' => 'text_align',
                                'label' => esc_html__('Alignment', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::CHOOSE,
                                'control_type' => 'responsive',
                                'options' => [
                                    'left' => [
                                        'title' => esc_html__('Left', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-left',
                                    ],
                                    'center' => [
                                        'title' => esc_html__('Center', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-center',
                                    ],
                                    'right' => [
                                        'title' => esc_html__('Right', 'powerlegal' ),
                                        'icon' => 'eicon-text-align-right',
                                    ],
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-contact-form7' => 'text-align: {{VALUE}};',
                                ],
                            ),
                            array(
                                'name' => 'button_style_tabs',
                                'control_type' => 'tab',
                                'tabs' => array(
                                    array(
                                        'name' => 'button_style_normal',
                                        'label' => esc_html__('Normal', 'powerlegal'),
                                        'controls' => array(
                                            array(
                                                'name' => 'button_color',
                                                'label' => esc_html__('Text Color', 'powerlegal'),
                                                'type' => \Elementor\Controls_Manager::COLOR,
                                                'selectors' => [
                                                    '{{WRAPPER}} .wpcf7-form input[type="submit"]' => 'color: {{VALUE}};'
                                                ]
                                            ),
                                            array(
                                                'name' => 'button_background',
                                                'type' => \Elementor\Group_Control_Background::get_type(),
                                                'control_type' => 'group',
                                                'types'             => [ 'classic' , 'gradient' ],
                                                'selector' => '{{WRAPPER}} .wpcf7-form input[type="submit"]',
                                            ),

                                        )
                                    ),
                                    array(
                                        'name' => 'button_style_hover',
                                        'label' => esc_html__('Hover', 'powerlegal'),
                                        'controls' => array(
                                            array(
                                                'name' => 'button_color_hover',
                                                'label' => esc_html__('Text Color', 'powerlegal'),
                                                'type' => \Elementor\Controls_Manager::COLOR,
                                                'selectors' => [
                                                    '{{WRAPPER}} .wpcf7-form input[type="submit"]:hover' => 'color:{{VALUE}};'
                                                ]
                                            ),
                                            array(
                                                'name' => 'button_background_hover',
                                                'type' => \Elementor\Group_Control_Background::get_type(),
                                                'control_type' => 'group',
                                                'types'             => [ 'classic' , 'gradient' ],
                                                'selector' => '{{WRAPPER}} .wpcf7-form input[type="submit"]:hover',
                                            ),
                                        )
                                    ),
                                )
                            ),
                        ),
                    ),
                    array(
                        'name' => 'textarea_size',
                        'label' => esc_html__('Texarea Size', 'powerlegal'),
                        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                        'controls' => array(
                            array(
                                'name'  =>  'message_height',
                                'type'  =>  \Elementor\Controls_Manager::SLIDER,
                                'label' => esc_html__('Message Height', 'powerlegal'),
                                'size_units' => ['px'],
                                'range' => [
                                    'px' => [
                                        'min' => 120,
                                        'max' => 350,
                                    ],
                                ],
                                'default'   => [
                                    'unit' => 'px',
                                    'size' => '',
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .wpcf7-form textarea.wpcf7-textarea' => 'height: {{SIZE}}{{UNIT}};',
                                ],
                            ),
                        ),
                    ),
                ),
            )
        ),
            powerlegal_get_class_widget_path()
    );
}