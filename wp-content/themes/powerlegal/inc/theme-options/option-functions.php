<?php 
/**
 * Get Post List 
*/
if(!function_exists('powerlegal_list_post')){
    function powerlegal_list_post($post_type = 'post', $default = false){
        $post_list = array();
        $posts = get_posts(array('post_type' => $post_type, 'orderby' => 'date', 'order' => 'ASC', 'posts_per_page' => '-1'));
        if($default){
        	$post_list[-1] = esc_html__( 'Inherit', 'powerlegal' );
        }
        foreach($posts as $post){
            $post_list[$post->ID] = $post->post_title;
        }
        return $post_list;
    }
}
 
if(!function_exists('powerlegal_get_templates_option')){
	function powerlegal_get_templates_option($meta_value = 'df', $default = false){
        $post_list = array();
        if($default && !is_array($default)){
            $post_list[-1] = esc_html__('Inherit','powerlegal');
        }
        if(is_array($default)){
        	$key = isset($default['key']) ? $default['key'] : '0';
        	$post_list[$key] = !empty($default['value']) ? $default['value'] : esc_html__('None','powerlegal');
        }
        $args = array(
            'post_type' => 'pxl-template',
            'posts_per_page' => '-1',
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key'       => 'template_type',
                    'value'     => $meta_value,
                    'compare'   => '='
                )
            )
        );

        $posts = get_posts($args);
        
        foreach($posts as $post){  
        	$template_type = get_post_meta( $post->ID, 'template_type', true );
        	if($template_type == 'df') continue;
            $post_list[$post->ID] = $post->post_title;
        }
         
        return $post_list;
    }
}
if(!function_exists('powerlegal_get_templates_option_slug')){
    function powerlegal_get_templates_option_slug($meta_value = 'df'){
        $post_list = array();
        $args = array(
            'post_type' => 'pxl-template',
            'posts_per_page' => '-1',
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key'       => 'template_type',
                    'value'     => $meta_value,
                    'compare'   => '='
                )
            )
        );

        $posts = get_posts($args);
        
        foreach($posts as $post){  
        	$template_type = get_post_meta( $post->ID, 'template_type', true );
        	if($template_type == 'df') continue;

        	$template_position = get_post_meta( $post->ID, 'template_position', true );
        	$pos = !empty($template_position) ? $template_position : '';
        	$keys = [$post->post_name, $post->ID, $pos];
        	$key = implode('||', $keys);
            $post_list[$key] = $post->post_title;
        }
        return $post_list;
    }
}

if(!function_exists('powerlegal_get_slider_option')){
    function powerlegal_get_slider_option(){
        $post_list = array();
         
        $args = array(
            'post_type' => 'pxl-slider',
            'posts_per_page' => '-1',
            'orderby' => 'date',
            'order' => 'ASC',
        );

        $posts = get_posts($args);
        
        foreach($posts as $post){  
            $post_list[$post->ID] = $post->post_title;
        }
         
        return $post_list;
    }
}

if(!function_exists('powerlegal_get_templates_slug')){
    function powerlegal_get_templates_slug($meta_value = 'df'){
        $post_list = array();
        $posts = get_posts(
        	array(
        		'post_type' => 'pxl-template', 
        		'orderby' => 'date', 
        		'order' => 'ASC', 
        		'posts_per_page' => '-1',
        		'meta_query' => array(
	                array(
	                    'key'       => 'template_type',
	                    'value'     => $meta_value,
	                    'compare'   => '='
	                )
	            )
        	)
        );
         
        foreach($posts as $post){
        	$template_type = get_post_meta( $post->ID, 'template_type', true );
            $template_position = get_post_meta( $post->ID, 'template_position', true );
            $pos = !empty($template_position) ? $template_position : '';
        	if($template_type == 'df') continue;
        	$value_args = [
        		'post_id' => $post->ID, 
        		'title' => $post->post_title,
                'position' => $pos
        	];
        	$template_position = get_post_meta( $post->ID, 'template_position', true );
        	 
    		$value_args['position'] = !empty($template_position) ? $template_position : '';

            $post_list[$post->post_name] = $value_args;
        }
        return $post_list;
    }
}



if(!function_exists('powerlegal_header_opts')){
	function powerlegal_header_opts($args=[]){
		$args = wp_parse_args($args,[
			'default'         => false,
			'default_value'   => ''
		]);
		
		if($args['default']){  
			$options = [
				'-1' => esc_html__('Default','powerlegal'),
                '1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '-1';
		} else {
			 
			$options = [
				'1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '0';
		} 
		$opts = array(
	        array(
				'id'      => 'header_layout',
				'type'    => 'select',
				'title'   => esc_html__('Main Header Layout', 'powerlegal'),
				'desc'    => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
				'options' => powerlegal_get_templates_option('header',$args['default']),
				'default' => $args['default_value']  
	        ),
	        array(
	            'id'       => 'header_transparent',
	            'title'    => esc_html__('Header Transparent', 'powerlegal'),
	            'subtitle' => esc_html__('Header will be overlay on next content when applicable.', 'powerlegal'),
	            'type'     => 'button_set',
                'options'  => $options,
                'default'  => $default_value,
	        ), 
            array(
				'id'      => 'header_sticky_layout',
				'type'    => 'select',
				'title'   => esc_html__('Sticky Header Layout', 'powerlegal'),
				'desc'    => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
				'options' => powerlegal_get_templates_option('header',$args['default']),
				'default' => $args['default_value'],
	        ),
        );
 
		return $opts;
	}
}
if(!function_exists('powerlegal_header_mobile_opts')){
	function powerlegal_header_mobile_opts($args=[]){
		$args = wp_parse_args($args,[
			'default'         => false,
			'default_value'   => ''
		]);
		if($args['default']){
			$options = [
				'-1' => esc_html__('Default','powerlegal'),
                '1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '-1';
			
		} else {
			$options = [
				'1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '0';
		} 
		$opts = array(
			array(
	            'id'       => 'header_mobile_layout',
	            'type'     => 'select',
	            'title'    => esc_html__('Header Mobile Layout', 'powerlegal'),
	            'subtitle' => esc_html__('Select a layout for header mobile.', 'powerlegal'),
	            'options'  => powerlegal_get_templates_option('header',$args['default']),
	            'default'  => $args['default_value'],
	            'required' => array( 'header_layout' , '!=', '' )  
	        ),
	        array(
	            'id'       => 'logo_m',
	            'type'     => 'media',
	            'title'    => esc_html__('Logo Mobile', 'powerlegal'),
	            'default' => array(
	                'url'=>''
	            ),
	            'required' => array( ['header_layout' , '!=', ''], ['header_mobile_layout' , '=', ''] )
	        ),
	        array(
	            'id'       => 'logo_mobile_size',
	            'type'     => 'dimensions',
	            'title'    => esc_html__('Logo Size', 'powerlegal'),
	            'subtitle' => esc_html__('Enter demensions for your logo', 'powerlegal'),
	            'height'    => false,
	            'unit'     => 'px',
	            'required' => array( 'header_mobile_layout' , '=', '' )
	        ) 
	    );
 
		return $opts;
	}
}
if(!function_exists('powerlegal_page_title_opts')){
	function powerlegal_page_title_opts($args=[]){
		$args = wp_parse_args($args,[
			'default'         => false,
			'default_value'   => ''
		]);
		if($args['default']){
			$options = [
				'-1' => esc_html__('Default','powerlegal'),
                '1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '-1';
			
		} else {
			$options = [
				'1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$default_value = '0';
		} 
		
		if($args['default']){
			$pt_mode_options = [
				'-1'  => esc_html__('Inherit', 'powerlegal'),
	            'bd'   => esc_html__('Builder', 'powerlegal'),
	            'none'  => esc_html__('Disable', 'powerlegal')
			];
			$pt_mode_default = '-1';
		}else{
			$pt_mode_options = [
				'df'  => esc_html__('Default', 'powerlegal'),
	            'bd'   => esc_html__('Builder', 'powerlegal'),
	            'none'  => esc_html__('Disable', 'powerlegal')
			];
			$pt_mode_default = 'df';
		}

		$opts = array(
		 	array(
                'id'       => 'pt_mode',
                'type'     => 'button_set',
                'title'    => esc_html__('Select Page title Mode', 'powerlegal'),
                'options' => $pt_mode_options, 
                'default' => $pt_mode_default
            ),
			array(
	            'id'       => 'ptitle_layout',
	            'type'     => 'select',
	            'title'    => esc_html__('Page Title Layout (not empty)', 'powerlegal'),
	            'subtitle' => esc_html__('Select a layout for page title.', 'powerlegal'),
	            'options'  => powerlegal_get_templates_option('page-title',false),
	            'default'  => $args['default_value'],
	            'required' => array( 'pt_mode', '=', 'bd' )
	        ),
	        array(
				'id'       => 'ptitle_bg',
				'type'     => 'background',
				'title'    => esc_html__('Background', 'powerlegal'),
				'subtitle' => esc_html__('Page title background.', 'powerlegal'),
				'output'   => array('.pxl-pagetitle'),
				'required' => array( 'pt_mode', '!=', 'none' ),
                'force_output' => true
	        ),
	        array(
				'id'       => 'ptitle_overlay_color',
				'type'     => 'color_rgba',
				'title'    => esc_html__('Overlay Background Color', 'powerlegal'),
				'output'   => array('background-color' => '.pxl-pagetitle.layout-df .pxl-page-title-overlay'),
				'required' => array( 'pt_mode', '=', 'df' )
	        ),
            array(
                'id'      => 'breadcrumb_on',
                'type'    => 'switch',
                'title'   => esc_html__( 'Breadcrumb', 'powerlegal' ),
                'default' => false,
                'required' => array( 'pt_mode', '=', 'df' )
            ),
		);

		return $opts;
	}
}

if(!function_exists('powerlegal_footer_opts')){
	function powerlegal_footer_opts($args=[]){
		$args = wp_parse_args($args,[
			'default'         => false,
			'default_value'   => ''
		]);
		if($args['default']){
			$footer_fixed_options = [
				'-1' => esc_html__('Default','powerlegal'),
                '1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$footer_fixed_default_value = '-1';
		} else {
			$footer_fixed_options = [
				'1'  => esc_html__('Yes','powerlegal'),
                '0'  => esc_html__('No','powerlegal'),
			];
			$footer_fixed_default_value = '0';
		} 
		$opts = array(
	        array(
	            'id'          => 'footer_layout',
	            'type'        => 'select',
	            'title'       => esc_html__('Footer Layout', 'powerlegal'),
	            'desc'        => sprintf(esc_html__('Please create your layout before choosing. %sClick Here%s','powerlegal'),'<a href="' . esc_url( admin_url( 'edit.php?post_type=pxl-template' ) ) . '">','</a>'),
	            'options'     => powerlegal_get_templates_option('footer', $args['default']),
	            'default'     => $args['default_value'],
	        ),
	        array(
                'title'    => esc_html__('Footer Fixed', 'powerlegal'),
                'subtitle' => esc_html__('Make footer fixed at bottom?', 'powerlegal'),
                'id'       => 'footer_fixed',
                'type'     => 'button_set',
                'options'  => $footer_fixed_options,
                'default'  => $footer_fixed_default_value,
            )
	    );
 
		return $opts;
	}
}
if(!function_exists('powerlegal_sidebar_pos_opts')){
	function powerlegal_sidebar_pos_opts($args=[]){
		$args = wp_parse_args($args,[
			'prefix'        => 'blog_',
			'default'       => false,
			'default_value' => 'right'
		]);

		if($args['default']){
			$options = [
				'-1'    => esc_html__('Inherit','powerlegal'),
				'left'  => esc_html__('Left','powerlegal'),
				'right' => esc_html__('Right','powerlegal'),
				'0'     => esc_html__('Disabled','powerlegal'),
			];
			 
		} else {
			$options = [
				'left'  => esc_html__('Left','powerlegal'),
				'right' => esc_html__('Right','powerlegal'),
				'0'     => esc_html__('Disabled','powerlegal'),
			]; 
		}  
		$opts = array(
	        array(
	            'id'       => $args['prefix'].'sidebar_pos',
	            'type'     => 'button_set',
	            'title'    => esc_html__('Sidebar Position', 'powerlegal'),
	            'subtitle' => esc_html__('Select a sidebar position is displayed.', 'powerlegal'),
	            'options'  => $options,
	            'default'  => $args['default_value'],
	        ),
	    );
 
		return $opts;
	}
}


//* Get list menu
function powerlegal_get_nav_menu_slug(){

    $menus = array(
        '-1' => esc_html__('Inherit', 'powerlegal')
    );

    $obj_menus = wp_get_nav_menus();

    foreach ($obj_menus as $obj_menu){
        $menus[$obj_menu->slug] = $obj_menu->name;
    }
    return $menus;
}

//* Shop Wishlist
function powerlegal_product_single_opts_wishlist(){
    $arr = [];
    if(class_exists('WPCleverWoosw'))
        $arr[] = array(
            'id'       => 'product_wishlist',
            'title'    => esc_html__('Show Wishlist', 'powerlegal'),
            'type'     => 'switch',
            'default'  => '1',
        );
    return $arr;
}