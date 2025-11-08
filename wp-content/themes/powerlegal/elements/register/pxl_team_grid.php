<?php
use Elementor\Icons_Manager;
Icons_Manager::enqueue_shim();
pxl_add_custom_widget(
    array(
        'name' => 'pxl_team_grid',
        'title' => esc_html__('PXL Team Grid', 'powerlegal'),
        'icon' => 'eicon-person',
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
                                    'image' => get_template_directory_uri() . '/elements/assets/layout-image/pxl_team_grid-1.jpg'
                                ],
                            ],
                            'prefix_class' => 'pxl-team-grid-layout-',
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
                            'label' => esc_html__('Team List', 'powerlegal'),
                            'type' => \Elementor\Controls_Manager::REPEATER,
                            'controls' => array(
                                array(
                                    'name' => 'image',
                                    'label' => esc_html__('Image', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::MEDIA,
                                ),
                                array(
                                    'name' => 'title',
                                    'label' => esc_html__('Title', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'position',
                                    'label' => esc_html__('Position', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'description',
                                    'label' => esc_html__('Description', 'powerlegal' ),
                                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                                    'rows' => 4,
                                ),
                                array(
                                    'name' => 'phone',
                                    'label' => esc_html__('Phone', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::TEXT,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'link',
                                    'label' => esc_html__('Link', 'powerlegal'),
                                    'type' => \Elementor\Controls_Manager::URL,
                                    'label_block' => true,
                                ),
                                array(
                                    'name' => 'social',
                                    'label' => esc_html__( 'Social', 'powerlegal' ),
                                    'type' => 'pxl_icons',
                                ),
                            ),
                            'title_field' => '{{{ title }}}',
                        ),
                    ),
                ),
                array(
                    'name' => 'grid_section',
                    'label' => esc_html__('Grid', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array_merge(
                        powerlegal_grid_column_settings(),
                        array(
                            array(
                                'name' => 'img_size',
                                'label' => esc_html__('Image Size', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::TEXT,
                                'description' =>  esc_html__('Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Default: 370x300 (Width x Height)).', 'powerlegal')
                            )
                        ),
                        powerlegal_elementor_animation_opts([
                            'name'   => 'item',
                            'label' => esc_html__('Item', 'powerlegal'),
                        ])
                    ),
                ),
                array(
                    'name' => 'style_section',
                    'label' => esc_html__('Style', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array(
                        array(
                            'name' => 'title_color',
                            'label' => esc_html__('Title Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-team-grid .item-title' => 'color: {{VALUE}};',
                            ],
                        ),
                        array(
                            'name' => 'position_color',
                            'label' => esc_html__('Position Color', 'powerlegal' ),
                            'type' => \Elementor\Controls_Manager::COLOR,
                            'selectors' => [
                                '{{WRAPPER}} .pxl-team-grid .item-position' => 'color: {{VALUE}};',
                            ],
                        ),
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);