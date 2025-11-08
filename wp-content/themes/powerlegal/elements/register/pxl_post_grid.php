<?php
$pt_supports = ['post','pxl-case-study', 'pxl-practice-area'];
use Elementor\Controls_Manager;
pxl_add_custom_widget(
    array(
        'name'       => 'pxl_post_grid',
        'title'      => esc_html__('PXL Post Grid', 'powerlegal' ),
        'icon'       => 'eicon-posts-grid',
        'categories' => array('pxltheme-core'),
        'scripts'    => [
            'imagesloaded',
            'isotope',
            'powerlegal-post-grid',
        ],
        'params' => array(
            'sections' => array(
                array(
                    'name'     => 'layout_section',
                    'label'    => esc_html__( 'Layout', 'powerlegal' ),
                    'tab'      => 'layout',
                    'controls' => array_merge(
                        array(
                            array(
                                'name'     => 'post_type',
                                'label'    => esc_html__( 'Select Post Type', 'powerlegal' ),
                                'type'     => 'select',
                                'multiple' => true,
                                'options'  => powerlegal_get_post_type_options($pt_supports),
                                'default'  => 'post'
                            ) 
                        ),
                        powerlegal_get_post_grid_layout($pt_supports)
                    ),
                ),
                 
                array(
                    'name' => 'source_section',
                    'label' => esc_html__('Source', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array_merge(
                        array(
                            array(
                                'name'     => 'select_post_by',
                                'label'    => esc_html__( 'Select posts by', 'powerlegal' ),
                                'type'     => 'select',
                                'multiple' => true,
                                'options'  => [
                                    'term_selected' => esc_html__( 'Terms selected', 'powerlegal' ),
                                    'post_selected' => esc_html__( 'Posts selected ', 'powerlegal' ),
                                ],
                                'default'  => 'term_selected'
                            ) 
                        ),
                        powerlegal_get_grid_term_by_posttype($pt_supports, ['custom_condition' => ['select_post_by' => 'term_selected']]),
                        powerlegal_get_grid_ids_by_posttype($pt_supports, ['custom_condition' => ['select_post_by' => 'post_selected']]),
                        array(
                            array(
                                'name'    => 'orderby',
                                'label'   => esc_html__('Order By', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::SELECT,
                                'default' => 'date',
                                'options' => [
                                    'date'   => esc_html__('Date', 'powerlegal' ),
                                    'ID'     => esc_html__('ID', 'powerlegal' ),
                                    'author' => esc_html__('Author', 'powerlegal' ),
                                    'title'  => esc_html__('Title', 'powerlegal' ),
                                    'rand'   => esc_html__('Random', 'powerlegal' ),
                                ],
                            ),
                            array(
                                'name'    => 'order',
                                'label'   => esc_html__('Sort Order', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::SELECT,
                                'default' => 'desc',
                                'options' => [
                                    'desc' => esc_html__('Descending', 'powerlegal' ),
                                    'asc'  => esc_html__('Ascending', 'powerlegal' ),
                                ],
                            ),
                            array(
                                'name'    => 'limit',
                                'label'   => esc_html__('Total items', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::NUMBER,
                                'default' => '6',
                            ),
                        )
                    ),
                ),
                array(
                    'name' => 'general_section',
                    'label' => esc_html__('General Settings', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array_merge(
                        array(
                            array(
                                'name'        => 'img_size',
                                'label'       => esc_html__('Image Size', 'powerlegal' ),
                                'type'        => \Elementor\Controls_Manager::TEXT,
                                'description' =>  esc_html__('Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Default: 370x300 (Width x Height)).', 'powerlegal')
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
                                'name'    => 'filter',
                                'label'   => esc_html__('Term Filter', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::SELECT,
                                'default' => 'false',
                                'options' => [
                                    'true'  => esc_html__('Enable', 'powerlegal' ),
                                    'false' => esc_html__('Disable', 'powerlegal' ),
                                ],
                                'condition' => [
                                    'select_post_by' => 'term_selected',
                                ],
                                
                            ),
                            array(
                                'name'      => 'filter_default_title',
                                'label'     => esc_html__('Filter Default Title', 'powerlegal' ),
                                'type'      => \Elementor\Controls_Manager::TEXT,
                                'default'   => esc_html__('All', 'powerlegal' ),
                                'condition' => [
                                    'select_post_by' => 'term_selected',
                                    'filter'         => 'true',
                                ],
                            ),
                            array(
                                'name'         => 'filter_alignment',
                                'label'        => esc_html__( 'Filter Alignment', 'powerlegal' ),
                                'type'         => 'choose',
                                'control_type' => 'responsive',
                                'options' => [
                                    'start' => [
                                        'title' => esc_html__( 'Start', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-left',
                                    ],
                                    'center' => [
                                        'title' => esc_html__( 'Center', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-center',
                                    ],
                                    'end' => [
                                        'title' => esc_html__( 'End', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-right',
                                    ]
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .grid-filter-wrap' => 'justify-content: {{VALUE}};'
                                ],
                                'default'      => 'center',
                                'condition' => [
                                    'select_post_by' => 'term_selected',
                                    'filter'         => 'true',
                                ],
                            ),
                            array(
                                'name'    => 'pagination_type',
                                'label'   => esc_html__('Pagination Type', 'powerlegal' ),
                                'type'    => \Elementor\Controls_Manager::SELECT,
                                'default' => 'false',
                                'options' => [
                                    'pagination' => esc_html__('Pagination', 'powerlegal' ),
                                    'loadmore'   => esc_html__('Loadmore', 'powerlegal' ),
                                    'false'      => esc_html__('Disable', 'powerlegal' ),
                                ],
                            ),
                            array(
                                'name'      => 'loadmore_text',
                                'label'     => esc_html__( 'Load More text', 'powerlegal' ),
                                'type'      => \Elementor\Controls_Manager::TEXT,
                                'default'   => esc_html__('Load More','powerlegal'),
                                'condition' => [
                                    'pagination_type' => 'loadmore'
                                ]
                            ),
                            array(
                                'name'             => 'loadmore_icon',
                                'label'            => esc_html__( 'Loadmore Icon', 'powerlegal' ),
                                'type'             => 'icons',
                                'default'          => [],
                                'condition' => [
                                    'pagination_type' => 'loadmore'
                                ]
                            ),
                            array(
                                'name' => 'icon_align',
                                'label' => esc_html__('Icon Position', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::SELECT,
                                'default' => 'right',
                                'options' => [
                                    'right' => esc_html__('After', 'powerlegal' ),
                                    'left' => esc_html__('Before', 'powerlegal' ),
                                ],
                                'condition' => [
                                    'pagination_type' => 'loadmore'
                                ]
                            ),
                            array(
                                'name'         => 'pagination_alignment',
                                'label'        => esc_html__( 'Pagination Alignment', 'powerlegal' ),
                                'type'         => 'choose',
                                'control_type' => 'responsive',
                                'options' => [
                                    'start' => [
                                        'title' => esc_html__( 'Start', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-left',
                                    ],
                                    'center' => [
                                        'title' => esc_html__( 'Center', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-center',
                                    ],
                                    'end' => [
                                        'title' => esc_html__( 'End', 'powerlegal' ),
                                        'icon'  => 'eicon-text-align-right',
                                    ]
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-grid-pagination, {{WRAPPER}} .pxl-load-more' => 'justify-content: {{VALUE}};'
                                ],
                                'default'      => 'start',
                                'condition' => [
                                    'pagination_type' => ['pagination', 'loadmore'],
                                ],
                            ),
                            array(
                                'name' => 'item_padding',
                                'label' => esc_html__('Item Padding', 'powerlegal' ),
                                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                                'size_units' => [ 'px' ],
                                'default' => [
                                    'top' => '15',
                                    'right' => '15',
                                    'bottom' => '15',
                                    'left' => '15'
                                ],
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-grid-inner' => 'margin-top: -{{TOP}}{{UNIT}}; margin-right: -{{RIGHT}}{{UNIT}}; margin-bottom: -{{BOTTOM}}{{UNIT}}; margin-left: -{{LEFT}}{{UNIT}};',
                                    '{{WRAPPER}} .pxl-grid-inner .grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                                ],
                                'control_type' => 'responsive',
                            ),
         
                            array(
                                'name'         => 'gap_extra',
                                'label'        => esc_html__( 'Item Gap Bottom', 'powerlegal' ),
                                'description'  => esc_html__( 'Add extra space at bottom of each items','powerlegal'),
                                'type'         => \Elementor\Controls_Manager::NUMBER,
                                'default'      => 0,
                                'control_type' => 'responsive',
                                'selectors' => [
                                    '{{WRAPPER}} .pxl-grid-inner .grid-item' => 'margin-bottom: {{VALUE}}px;',
                                ],
                            )
                        ),
                        powerlegal_elementor_animation_opts([
                            'name'   => 'item',
                            'label' => esc_html__('Item', 'powerlegal'),
                        ])
                    )
                ),
                array(
                    'name' => 'grid_section',
                    'label' => esc_html__('Grid Settings', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array_merge(
                        powerlegal_grid_column_settings(),
                        array(
                            array(
                                'name'      => 'grid_custom_columns',
                                'label'     => esc_html__('Custom Items Columns', 'powerlegal'),
                                'type'      => \Elementor\Controls_Manager::REPEATER,
                                'condition' => [
                                    'layout_mode' => ['masonry'],
                                ],
                                'controls' => array_merge(
                                    powerlegal_grid_custom_column_settings(),
                                    array(
                                        array(
                                            'name'        => 'img_size_c',
                                            'label'       => esc_html__('Image Size', 'powerlegal' ),
                                            'type'        => \Elementor\Controls_Manager::TEXT,
                                            'description' => esc_html__('Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Default: 370x300 (Width x Height)).', 'powerlegal'),
                                        ),
                                    ),
                                    powerlegal_elementor_animation_opts([
                                        'name'  => 'item_c',
                                        'label' => esc_html__('Item', 'powerlegal'),
                                    ])
                                ),
                            ),
                        )
                    ),
                ),
                array(
                    'name' => 'display_section',
                    'label' => esc_html__('Display Options', 'powerlegal' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                    'controls' => array(
                        array(
                            'name'      => 'show_date',
                            'label'     => esc_html__('Show Date', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                            'condition' => ['post_type' => 'post']
                        ),
                        array(
                            'name'      => 'show_author',
                            'label'     => esc_html__('Show Author', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                            'condition' => ['post_type' => 'post']
                        ),
                        array(
                            'name'      => 'show_category',
                            'label'     => esc_html__('Show Category', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                            'condition' => ['post_type' => array('post', 'pxl-case-study')]
                        ),
                        array(
                            'name'      => 'show_comment',
                            'label'     => esc_html__('Show Comment', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                            'condition' => ['post_type' => 'post']
                        ),
                        array(
                            'name'      => 'show_excerpt',
                            'label'     => esc_html__('Show Excerpt', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                        ),
                        array(
                            'name'      => 'num_words',
                            'label'     => esc_html__('Number of Words', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::NUMBER,
                            'condition' => [
                                'show_excerpt' => 'true',
                            ],
                        ),
                        array(
                            'name'      => 'show_button',
                            'label'     => esc_html__('Show Button Readmore', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::SWITCHER,
                            'default'   => 'true',
                        ),
                        array(
                            'name'      => 'button_text',
                            'label'     => esc_html__('Button Text', 'powerlegal' ),
                            'type'      => \Elementor\Controls_Manager::TEXT,
                            'condition' => [
                                'show_button' => 'true',
                            ],
                        ),
                        
                    ),
                ),
            ),
        ),
    ),
    powerlegal_get_class_widget_path()
);