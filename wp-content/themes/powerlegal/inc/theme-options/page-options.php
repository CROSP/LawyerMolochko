<?php
 
add_action( 'pxl_post_metabox_register', 'powerlegal_page_options_register' );
function powerlegal_page_options_register( $metabox ) {
	$panels = [
		'post' => [
			'opt_name'            => 'post_option',
			'display_name'        => esc_html__( 'Post Settings', 'powerlegal' ),
			'show_options_object' => false,
			'context'  => 'advanced',
			'priority' => 'default',
			'sections'  => [
				'post_settings' => [
					'title'  => esc_html__( 'Post Settings', 'powerlegal' ),
					'icon'   => 'el el-refresh',
					'fields' => array_merge(
						powerlegal_sidebar_pos_opts(['prefix' => 'post_', 'default' => true, 'default_value' => '-1']),
						powerlegal_page_title_opts([
							'default'         => true,
							'default_value'   => '-1'
						]),
                        array(
                            array(
                                'id'          => 'featured-video-url',
                                'type'        => 'text',
                                'title'       => esc_html__( 'Video URL', 'powerlegal' ),
                                'description' => esc_html__( 'Video will show when set post format is video', 'powerlegal' ),
                                'validate'    => 'url',
                                'msg'         => 'Url error!',
                            ),
                            array(
                                'id'          => 'featured-audio-url',
                                'type'        => 'text',
                                'title'       => esc_html__( 'Audio URL', 'powerlegal' ),
                                'description' => esc_html__( 'Audio that will show when set post format is audio', 'powerlegal' ),
                                'validate'    => 'url',
                                'msg'         => 'Url error!',
                            ),
                            array(
                                'id'=>'featured-quote-text',
                                'type' => 'textarea',
                                'title' => esc_html__('Quote Text', 'powerlegal'),
                                'default' => '',
                            ),
                            array(
                                'id'          => 'featured-quote-cite',
                                'type'        => 'text',
                                'title'       => esc_html__( 'Quote Cite', 'powerlegal' ),
                                'description' => esc_html__( 'Quote will show when set post format is quote', 'powerlegal' ),
                            ),
                            array(
                                'id'       => 'featured-link-url',
                                'type'     => 'text',
                                'title'    => esc_html__( 'Format Link URL', 'powerlegal' ),
                                'description' => esc_html__( 'Link will show when set post format is link', 'powerlegal' ),
                            ),
                            array(
                                'id'          => 'featured-link-text',
                                'type'        => 'text',
                                'title'       => esc_html__( 'Format Link Text', 'powerlegal' ),
                            ),
                        )
					)
				]
			]
		],
		'page' => [
			'opt_name'            => 'pxl_page_options',
			'display_name'        => esc_html__( 'Page Settings', 'powerlegal' ),
			'show_options_object' => false,
			'context'  => 'advanced',
			'priority' => 'default',
			'sections'  => [
				'header' => [
					'title'  => esc_html__( 'Header', 'powerlegal' ),
					'icon'   => 'el-icon-website',
					'fields' => array_merge(
				        powerlegal_header_opts([
							'default'         => true,
							'default_value'   => '-1'
						]),
                        array(
                            array(
                                'id'       => 'remove_header',
                                'title'    => esc_html__('Remove Header', 'powerlegal'),
                                'subtitle' => esc_html__('Header will not display.', 'powerlegal'),
                                'type'     => 'button_set',
                                'options'  => array(
                                    '1'  => esc_html__('Yes','powerlegal'),
                                    '0'  => esc_html__('No','powerlegal'),
                                ),
                                'default'  => '0',
                            ),
                        ),
						array(
					        array(
				                'id'       => 'pd_menu',
				                'type'     => 'select',
				                'title'    => esc_html__( 'Menu', 'powerlegal' ),
				                'options'  => powerlegal_get_nav_menu_slug(),
				                'default' => '-1',
				            ),
					    )
				    )
				],
				'header_mobile' => [
					'title'  => esc_html__( 'Header Mobile', 'powerlegal' ),
					'icon'   => 'el-icon-website',
					'fields' => array_merge(
				        powerlegal_header_mobile_opts([
							'default'         => true,
							'default_value'   => '-1'
						]),
						array(
					        array(
				                'id'       => 'pm_menu',
				                'type'     => 'select',
				                'title'    => esc_html__( 'Menu', 'powerlegal' ),
				                'options'  => powerlegal_get_nav_menu_slug(),
				                'default' => '-1',
				            ),
					    )
				    )
				],
				'page_title' => [
					'title'  => esc_html__( 'Page Title', 'powerlegal' ),
					'icon'   => 'el el-indent-left',
					'fields' => array_merge(
				        powerlegal_page_title_opts([
							'default'         => true,
							'default_value'   => '-1'
						]),
                        array(
                            array(
                                'id'           => 'custom_main_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Main Title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Custom heading text title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            ),
                            array(
                                'id'           => 'custom_sub_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Sub title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Add short description for page title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            )
                        )
				    )

				],
				'content' => [
					'title'  => esc_html__( 'Content', 'powerlegal' ),
					'icon'   => 'el-icon-pencil',
					'fields' => array_merge(
						powerlegal_sidebar_pos_opts(['prefix' => 'page_', 'default' => false, 'default_value' => '0']),
						array(
							array(
								'id'             => 'content_padding',
								'type'           => 'spacing',
								'output'         => array( '#pxl-main' ),
								'right'          => false,
								'left'           => false,
								'mode'           => 'padding',
								'units'          => array( 'px' ),
								'units_extended' => 'false',
								'title'          => esc_html__( 'Content Padding', 'powerlegal' ),
								'default'        => array(
									'padding-top'    => '',
									'padding-bottom' => '',
									'units'          => 'px',
								)
							),
                            array(
                                'id'       => 'content_bg_color',
                                'type'     => 'color_rgba',
                                'title'    => esc_html__( 'Background Color', 'powerlegal' ),
                                'subtitle' => esc_html__( 'Content background color.', 'powerlegal' ),
                                'output'   => array( 'background-color' => 'body' )
                            ),
						)  
					)
				],
				'footer' => [
					'title'  => esc_html__( 'Footer', 'powerlegal' ),
					'icon'   => 'el el-website',
					'fields' => array_merge(
				        powerlegal_footer_opts([
							'default'         => true,
							'default_value'   => '-1'
						]),
                        array(
                            array(
                                'id'       => 'remove_footer',
                                'title'    => esc_html__('Remove Footer', 'powerlegal'),
                                'subtitle' => esc_html__('Footer will not display.', 'powerlegal'),
                                'type'     => 'button_set',
                                'options'  => array(
                                    '1'  => esc_html__('Yes','powerlegal'),
                                    '0'  => esc_html__('No','powerlegal'),
                                ),
                                'default'  => '0',
                            ),
                        ),
				    )
				],
				'colors' => [
					'title'  => esc_html__( 'Colors', 'powerlegal' ),
					'icon'   => 'el el-website',
					'fields' => array(
				        array(
				            'id'          => 'primary_color',
				            'type'        => 'color',
				            'title'       => esc_html__('Primary Color', 'powerlegal'),
				            'transparent' => false,
				            'default'     => ''
				        ), 
				        array(
				            'id'          => 'secondary_color',
				            'type'        => 'color',
				            'title'       => esc_html__('Secondary Color', 'powerlegal'),
				            'transparent' => false,
				            'default'     => ''
				        ),
				    )
				]
			]
		],
		'product' => [ //product
			'opt_name'            => 'pxl_product_options',
			'display_name'        => esc_html__( 'Product Settings', 'powerlegal' ),
			'show_options_object' => false,
			'context'  => 'advanced',
			'priority' => 'default',
			'sections'  => [
				'general' => [
					'title'  => esc_html__( 'General', 'powerlegal' ),
					'icon'   => 'el-icon-website',
					'fields' => array_merge(
						array(
							array(
					            'id'=> 'product_feature_text',
					            'type' => 'text',
					            'title' => esc_html__('Featured Text', 'powerlegal'),
					            'default' => '',
					        ),
                            array(
                                'id'       => 'gallery_layout',
                                'type'     => 'button_set',
                                'title'    => esc_html__('Single Gallery', 'powerlegal'),
                                'options'  => array(
                                    'simple' => esc_html__('Simple', 'powerlegal'),
                                    'horizontal' => esc_html__('Horizontal', 'powerlegal'),
                                    'vertical' => esc_html__('Vertical', 'powerlegal'),
                                ),
                                'default'  => 'simple'
                            ),
						)
				    )
				],
			]
		],
		'pxl-case-study' => [ //post_type
			'opt_name'            => 'pxl_case_study_options',
			'display_name'        => esc_html__( 'Page Settings', 'powerlegal' ),
			'show_options_object' => false,
			'context'  => 'advanced',
			'priority' => 'default',
			'sections'  => [
                'page_title' => [
                    'title'  => esc_html__( 'Page Title', 'powerlegal' ),
                    'icon'   => 'el el-indent-left',
                    'fields' => array_merge(
                        powerlegal_page_title_opts([
                            'default'         => true,
                            'default_value'   => '-1'
                        ]),
                        array(
                            array(
                                'id'           => 'custom_main_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Main Title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Custom heading text title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            ),
                            array(
                                'id'           => 'custom_sub_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Sub title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Add short description for page title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            )
                        )
                    )

                ],
                'content' => [
                    'title'  => esc_html__( 'Content', 'powerlegal' ),
                    'icon'   => 'el-icon-pencil',
                    'fields' => array_merge(
                        array(
                            array(
                                'id'             => 'content_padding',
                                'type'           => 'spacing',
                                'output'         => array( '#pxl-main' ),
                                'right'          => false,
                                'left'           => false,
                                'mode'           => 'padding',
                                'units'          => array( 'px' ),
                                'units_extended' => 'false',
                                'title'          => esc_html__( 'Content Padding', 'powerlegal' ),
                                'default'        => array(
                                    'padding-top'    => '',
                                    'padding-bottom' => '',
                                    'units'          => 'px',
                                )
                            ),
                            array(
                                'id'       => 'title_tag_on',
                                'title'    => esc_html__('Title & Tags', 'powerlegal'),
                                'subtitle' => esc_html__('Display the Title & Tags at top of post.', 'powerlegal'),
                                'type'     => 'switch',
                                'default'  => '0'
                            ),
                        )
                    )
                ],
			]
		],
        'pxl-practice-area' => [ //post_type
            'opt_name'            => 'pxl_practice_area_options',
            'display_name'        => esc_html__( 'Page Settings', 'powerlegal' ),
            'show_options_object' => false,
            'context'  => 'advanced',
            'priority' => 'default',
            'sections'  => [
                'page_title' => [
                    'title'  => esc_html__( 'Page Title', 'powerlegal' ),
                    'icon'   => 'el el-indent-left',
                    'fields' => array_merge(
                        powerlegal_page_title_opts([
                            'default'         => true,
                            'default_value'   => '-1'
                        ]),
                        array(
                            array(
                                'id'           => 'custom_main_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Main Title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Custom heading text title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            ),
                            array(
                                'id'           => 'custom_sub_title',
                                'type'         => 'text',
                                'title'        => esc_html__( 'Custom Sub title', 'powerlegal' ),
                                'subtitle'     => esc_html__( 'Add short description for page title', 'powerlegal' ),
                                'required' => array( 'pt_mode', '=', 'bd' )
                            )
                        )
                    )

                ],
                'content' => [
                    'title'  => esc_html__( 'Content', 'powerlegal' ),
                    'icon'   => 'el-icon-pencil',
                    'fields' => array_merge(
                        array(
                            array(
                                'id'             => 'content_padding',
                                'type'           => 'spacing',
                                'output'         => array( '#pxl-main' ),
                                'right'          => false,
                                'left'           => false,
                                'mode'           => 'padding',
                                'units'          => array( 'px' ),
                                'units_extended' => 'false',
                                'title'          => esc_html__( 'Content Padding', 'powerlegal' ),
                                'default'        => array(
                                    'padding-top'    => '',
                                    'padding-bottom' => '',
                                    'units'          => 'px',
                                )
                            ),
                        )
                    )
                ],
                'additional_data' => [
                    'title'  => esc_html__( 'Additional Data', 'powerlegal' ),
                    'icon'   => 'el el-list-alt',
                    'fields' => array(
                        array(
                            'id'       => 'area_icon_type',
                            'type'     => 'button_set',
                            'title'    => esc_html__('Icon Type', 'powerlegal'),
                            'desc'     => esc_html__( 'This image icon will display in post grid or carousel', 'powerlegal' ),
                            'options'  => array(
                                'icon'  => esc_html__('Icon', 'powerlegal'),
                                'image'  => esc_html__('Image', 'powerlegal'),
                            ),
                            'default'  => 'image'
                        ),
                        array(
                            'id'       => 'area_icon',
                            'type'     => 'pxl_iconpicker',
                            'title'    => esc_html__( 'Select Icon', 'powerlegal' ),
                            'default'  => '',
                            'required' => array( 0 => 'area_icon_type', 1 => 'equals', 2 => 'icon' ),
                        ),
                        array(
                            'id'       => 'area_img',
                            'type'     => 'media',
                            'title'    => esc_html__('Select Image', 'powerlegal'),
                            'default' => '',
                            'required' => array( 0 => 'area_icon_type', 1 => 'equals', 2 => 'image' ),
                            'force_output' => true
                        ),

                    ),

                ],
            ]
        ],
		'pxl-template' => [ //post_type
			'opt_name'            => 'pxl_hidden_template_options',
			'display_name'        => esc_html__( 'Template Settings', 'powerlegal' ),
			'show_options_object' => false,
			'context'  => 'advanced',
			'priority' => 'default',
			'sections'  => [
				'general' => [
					'title'  => esc_html__( 'General', 'powerlegal' ),
					'icon'   => 'el-icon-website',
					'fields' => array(
						array(
							'id'    => 'template_type',
							'type'  => 'select',
							'title' => esc_html__('Template Type', 'powerlegal'),
				            'options' => [
                                'default'      => esc_html__('Default', 'powerlegal'),
								'header'       => esc_html__('Header', 'powerlegal'), 
								'footer'       => esc_html__('Footer', 'powerlegal'), 
								'mega-menu'    => esc_html__('Mega Menu', 'powerlegal'), 
								'page-title'   => esc_html__('Page Title', 'powerlegal'), 
								'hidden-panel' => esc_html__('Hidden Panel', 'powerlegal'),
                            ],
				            'default' => 'default',
				        ),
				        array(
							'id'       => 'template_position',
							'type'     => 'select',
							'title'    => esc_html__('Hidden Panel Position', 'powerlegal'),
							'options'  => [
                                'full' => esc_html__('Full Screen', 'powerlegal'),
                                'top'   => esc_html__('Top Position', 'powerlegal'),
								'left'   => esc_html__('Left Position', 'powerlegal'),
								'right'  => esc_html__('Right Position', 'powerlegal'),
                                'center'  => esc_html__('Center Position', 'powerlegal'),
				            ],
							'default'  => 'full',
							'required' => [ 'template_type', '=', 'hidden-panel']
				        ),
					),
				    
				],
			]
		],
	];
 
	$metabox->add_meta_data( $panels );
}
 