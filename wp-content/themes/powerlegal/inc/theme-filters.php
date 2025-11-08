<?php
/**
 * Filters hook for the theme
 *
 * @package Powerlegal
 */

add_filter( 'pxl_server_info', 'powerlegal_add_server_info');
function powerlegal_add_server_info($infos){
    $infos = [
        'api_url' => 'https://api.7iquid.com/',
        'docs_url' => 'https://7iquid.gitbook.io/powerlegal-law-lawyer-and-attorney-wordpress-them',
        'plugin_url' => 'https://7iquid.com/plugins/',
        'demo_url' => 'https://demo.7iquid.com/powerlegal/',
        'support_url' => '#',
        'help_url' => 'https://7iquid.gitbook.io/powerlegal-law-lawyer-and-attorney-wordpress-them/',
        'email_support' => '7iquid.agency@gmail.com',
        'video_url' => '#'
    ];

    return $infos;
}

//* Change Register Folder
add_filter('pxl-register-widgets-folder','powerlegal_custom_register_folder');
function powerlegal_custom_register_folder($folder_path){
    return get_template_directory() . '/elements/register/';
}

//* Post Type Support Elementor
add_filter( 'pxl_add_cpt_support', 'powerlegal_add_cpt_support' );
function powerlegal_add_cpt_support($cpt_support) { 
    $cpt_support[] = 'pxl-case-study';
    $cpt_support[] = 'pxl-practice-area';
    return $cpt_support;
}


add_filter( 'pxl_extra_post_types', 'powerlegal_add_posttype' );
function powerlegal_add_posttype( $postypes ) {
    $postypes['portfolio'] = array(
        'status' => false,
        'args' => array(
            'rewrite' => array(
                'slug' => ''
            ),
        ),
    );
    $case_study_slug = powerlegal()->get_theme_opt('pxl_case_study_slug', 'case-study');
    $case_study_label = powerlegal()->get_theme_opt('pxl_case_study_label', 'Case Study');
    $postypes['pxl-case-study'] = array(
        'status'     => true,
        'item_name'  => esc_html__('Case Studies', 'powerlegal'),
        'items_name' => esc_html__('Case Studies', 'powerlegal'),
        'args'       => array(
            'menu_icon'          => 'dashicons-portfolio',
            'supports'           => array(
                'title',
                'thumbnail',
                'editor',
                'excerpt',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'has_archive' => true,
            'rewrite'             => array(
                'slug'       => $case_study_slug
            ),
        ),
        'labels'     => array(
            'name' => $case_study_label,
            'add_new_item' => esc_html__('Add New Case Study', 'powerlegal'),
            'edit_item' => esc_html__('Edit Case Study', 'powerlegal'),
            'view_item' => esc_html__('View Case Study', 'powerlegal'),
        )

    );

    $practice_area_slug= powerlegal()->get_theme_opt('pxl_practice_area_slug', 'practice-area');
    $practice_area_label = powerlegal()->get_theme_opt('pxl_practice_area_label', 'Practice Area');
    $postypes['pxl-practice-area'] = array(
        'status'     => true,
        'item_name'  => esc_html__('Practice Areas', 'powerlegal'),
        'items_name' => esc_html__('Practice Areas', 'powerlegal'),
        'args'       => array(
            'menu_icon'          => 'dashicons-image-filter',
            'supports'           => array(
                'title',
                'thumbnail',
                'editor',
                'excerpt',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'has_archive' => true,
            'rewrite'             => array(
                'slug'       => $practice_area_slug
            ),
        ),
        'labels'     => array(
            'name' => $practice_area_label,
            'add_new_item' => esc_html__('Add New Practice Area', 'powerlegal'),
            'edit_item' => esc_html__('Edit Practice Area', 'powerlegal'),
            'view_item' => esc_html__('View Practice Area', 'powerlegal'),
        )

    );
	return $postypes;
}

add_filter( 'pxl_extra_taxonomies', 'powerlegal_add_tax' );
function powerlegal_add_tax( $taxonomies ) {
	$taxonomies['pxl-case-study-category'] = array(
		'status'     => true,
		'post_type'  => array( 'pxl-case-study' ),
		'taxonomy'   => 'Categories',
		'taxonomies' => 'Categories',
        'args' => array(),
		'labels'     => array()
	);
	$taxonomies['pxl-case-study-tag'] = array(
		'status'     => true,
		'post_type'  => array( 'pxl-case-study' ),
		'taxonomy'   => 'Tags',
		'taxonomies' => 'Tags',
        'args' => array(),
		'labels'     => array()
	);
    $taxonomies['pxl-practice-area-category'] = array(
        'status'     => true,
        'post_type'  => array( 'pxl-practice-area' ),
        'taxonomy'   => 'Categories',
        'taxonomies' => 'Categories',
        'args' => array(),
        'labels'     => array()
    );
	return $taxonomies;
}

add_filter( 'pxl_theme_builder_layout_ids', 'powerlegal_theme_builder_layout_id' );
function powerlegal_theme_builder_layout_id($layout_ids){
	//default [], 
	$header_layout        = (int)powerlegal()->get_opt('header_layout');
	$header_sticky_layout = (int)powerlegal()->get_opt('header_sticky_layout');
	$header_mobile_layout = (int)powerlegal()->get_opt('header_mobile_layout');
	$ptitle_layout 	      = (int)powerlegal()->get_opt('ptitle_layout');
	$footer_layout        = (int)powerlegal()->get_opt('footer_layout');
	if( $header_layout > 0) 
		$layout_ids[] = $header_layout;
	if( $header_sticky_layout > 0) 
		$layout_ids[] = $header_sticky_layout;
	if( $header_mobile_layout > 0) 
		$layout_ids[] = $header_mobile_layout;
	if( $ptitle_layout > 0) 
		$layout_ids[] = $ptitle_layout;
	if( $footer_layout > 0) 
		$layout_ids[] = $footer_layout;
	return $layout_ids;
}

add_filter( 'pxl_wg_get_source_id_builder', 'powerlegal_wg_get_source_builder' );
function powerlegal_wg_get_source_builder($wg_datas){
	$wg_datas['pxl_slider'] = 'slider_source';
	$wg_datas['pxl_tabs'] = ['control_name' => 'tabs_list', 'source_name' => 'content_template'];
	return $wg_datas;
}

add_filter( 'pxl_template_type_support', 'powerlegal_template_type_support' );
function powerlegal_template_type_support($type){
    //default ['header','footer','mega-menu']
    $extra_type = [
        'page-title'   => esc_html__('Page Title', 'powerlegal'),
        'hidden-panel' => esc_html__('Hidden Panel', 'powerlegal'),
        'default'      => esc_html__('Default', 'powerlegal'),
    ];
    $template_type = array_merge($type,$extra_type);
    return $template_type;
}

 
add_filter( 'get_the_archive_title', 'powerlegal_archive_title_remove_label' );
function powerlegal_archive_title_remove_label( $title ) {
	if ( is_category() ) {
		$title = single_cat_title( '', false );
	} elseif ( is_tag() ) {
		$title = single_tag_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_home() ) {
		$title = single_post_title( '', false );
	}

	return $title;
}

add_filter( 'comment_reply_link', 'powerlegal_comment_reply_text' );
function powerlegal_comment_reply_text( $link ) {
	$link = str_replace( 'Reply', ''.esc_attr__('Reply', 'powerlegal').'', $link );
	return $link;
}
 

add_filter( 'pxl_enable_megamenu', 'powerlegal_enable_megamenu' );
function powerlegal_enable_megamenu() {
	return true;
}
add_filter( 'pxl_enable_onepage', 'powerlegal_enable_onepage' );
function powerlegal_enable_onepage() {
	return true;
}

add_filter( 'pxl_support_awesome_pro', 'powerlegal_support_awesome_pro' );
function powerlegal_support_awesome_pro() {
	return false;
}
 
add_filter( 'redux_pxl_iconpicker_field/get_icons', 'powerlegal_add_icons_to_pxl_iconpicker_field' );
function powerlegal_add_icons_to_pxl_iconpicker_field($icons){
	$custom_icons = []; //'Flaticon' => array(array('flaticon-marker' => 'flaticon-marker')),
	$icons = array_merge($custom_icons, $icons);
	return $icons;
}


add_filter("pxl_mega_menu/get_icons", "powerlegal_add_icons_to_megamenu");
function powerlegal_add_icons_to_megamenu($icons){
	$custom_icons = []; //'Flaticon' => array(array('flaticon-marker' => 'flaticon-marker')),
	$icons = array_merge($custom_icons, $icons);
	return $icons;
}

add_filter( 'body_class', 'powerlegal_body_classes' );
function powerlegal_body_classes( $classes )
{
    $header_sticky_layout = (int)powerlegal()->get_opt('header_sticky_layout');
    $footer_fixed = powerlegal()->get_opt('footer_fixed', '0');

    if (class_exists('ReduxFramework')) {
        $classes[] = 'redux-page';
    }

    if ($header_sticky_layout > 0) {
        $classes[] = 'header-sticky';
    }

    if($footer_fixed == '1') $classes[] = 'pxl-footer-fixed';

    if(get_option( 'woosw_page_id',0) == get_the_ID())
        $classes[] = 'pxl-wishlist-page';
    return $classes;
}

/**
 * Move comment field to bottom
 */
add_filter( 'comment_form_fields', 'powerlegal_comment_field_to_bottom' );
function powerlegal_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	return $fields;
}

/** 
 * Custom Widget Archive 
 * This code filters the Archive widget to include the post count inside the link 
 * @since 1.0.0
*/
if(!function_exists('powerlegal_get_archives_link_text')){
    add_filter('get_archives_link', 'powerlegal_get_archives_link_text', 10, 6);
    function powerlegal_get_archives_link_text($link_html, $url, $text, $format, $before, $after ){
        $text = wptexturize( $text );
        $url  = esc_url( $url );
     
        if ( 'link' == $format ) {
            $link_html = "\t<link rel='archives' title='" . esc_attr( $text ) . "' href='$url' />\n";
        } elseif ( 'option' == $format ) {
            $link_html = "\t<option value='$url'>$before $text $after</option>\n";
        } elseif ( 'html' == $format ) {
            $link_html = "\t<li>$before<a href='$url'><span class='title'>$text</span></a>$after</li>\n";
        } else { // custom
            $link_html = "\t$before<a href='$url'><span class='title'>$text</span>$after</a>\n";
        }
        return $link_html;
    }
}

if(!function_exists('powerlegal_archive_count_span')){
    add_filter('get_archives_link', 'powerlegal_archive_count_span');
    function powerlegal_archive_count_span($links) {
        $links = str_replace('<li>', '<li class="pxl-list-item pxl-archive-item">', $links);
        $links = str_replace('</a>&nbsp;(', ' <span class="count">', $links);
        $links = str_replace(')</li>', '</span></a></li>', $links);
        return $links;
    }
}
//* Disable Lazy loading
add_filter( 'wp_lazy_loading_enabled', '__return_false' );

//* Change default Elementor Settings.
add_action( 'pxl-import-finish', 'powerlegal_update_after_import' );
function powerlegal_update_after_import(){
    $kit_id = powerlegal_get_id_by_slug('default-kit', 'elementor_library');
    if(!empty($kit_id))
        update_option( 'elementor_active_kit', $kit_id, 'yes' );

    update_option( 'permalink_structure', '/%postname%/', 'yes' );

    update_option( 'elementor_experiment-container', 'inactive', 'yes' );
}
add_filter( 'pxl-e-font-icon-svg-force-inactive', 'powerlegal_inactive_e_font_icon_svg' );
function powerlegal_inactive_e_font_icon_svg() {
    return true;
}

 
