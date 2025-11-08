<?php

$uri = get_template_directory_uri() . '/inc/admin/demo-data/demo-imgs/';
$pxl_server_info = apply_filters( 'pxl_server_info', ['demo_url' => ''] ) ;
// Demos
$demos = array(
	// Elementor Demos
	'powerlegal' => array(
		'title'       => 'Powerlegal',
		'description' => '',
		'screenshot'  => $uri . 'powerlegal.jpg',
		'preview'     => $pxl_server_info['demo_url'],
	),	 
);