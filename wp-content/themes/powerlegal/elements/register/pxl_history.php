<?php
// Register Accordion Widget
$templates = powerlegal_get_templates_option('default', []) ;
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_history',
        'title'      => esc_html__( 'PXL History', 'powerlegal' ),
        'icon'       => 'eicon-history',
        'categories' => array('pxltheme-core'),
        'scripts'    => array(
        ),
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
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_history-1.jpg'
                                ],
                            ],
                        ),
                    )
                ),
                array(
                    'name'     => 'source_section',
                    'label'    => esc_html__( 'Source Settings', 'powerlegal' ),
                    'tab'      => 'content',
                    'controls' => array(
                        array(
                            'name' => 'history_year',
                            'label' => esc_html__('Year', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::TEXT,
                            'default' => '1988'
                        ),
                        array(
                            'name' => 'history_title',
                            'label' => esc_html__('History Title', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::TEXTAREA,
                            'rows' => 2,
                            'default' => 'Birth Of Company'
                        ),
                        array(
                            'name' => 'history_items',
                            'label' => esc_html__('History Items', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'controls' => array(
                                array(
                                    'name' => 'content_template',
                                    'label' => esc_html__('Select Templates', 'powerlegal'),
                                    'description'        => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
                                    'type' => \Elementor\Controls_Manager::SELECT,
                                    'default' => '',
                                    'options' => $templates,
                                ),
                            ),
                        ),
                        
                    )
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);