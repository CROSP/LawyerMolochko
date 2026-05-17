<?php
//namespace Pxl_Core_Elements;

use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor List Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Pxl_Widget_Base extends \Elementor\Widget_Base {
	protected $name;
    protected $title;
    protected $icon;
    protected $categories = array('pxltheme-core');
    protected $keywords;
    protected $custom_help_url;
    protected $style_depends;
    protected $script_depends;
    protected $params;

	public function __construct($data = [], $args = null ) {
		parent::__construct($data, $args);
		
		$name = isset($args['name']) && !empty($args['name']) ? $args['name'] : Utils::generate_random_string();
		$title = isset($args['title']) && !empty($args['title']) ? $args['title'] : strtoupper($name);
		$icon = isset($args['icon']) && !empty($args['icon']) ? $args['icon'] : 'eicon-tools';
		$categories = isset($args['categories']) && !empty($args['categories']) ? $args['categories'] : [];
		$categories = !empty($categories) ? $categories : [ 'pxltheme-core' ];
		$custom_help_url = isset($args['custom_help_url']) && !empty($args['custom_help_url']) ? $args['custom_help_url'] : '';
		$keywords = isset($args['keywords']) && !empty($args['keywords']) ? $args['keywords'] : [];
		$script_depends = isset($args['scripts']) && !empty($args['scripts']) ? $args['scripts'] : [];
		$style_depends = isset($args['styles']) && !empty($args['styles']) ? $args['styles'] : [];
		$params = isset($args['params']) && !empty($args['params']) ? $args['params'] : [];

		$this->set_name($name);
		$this->set_title($title);
		$this->set_icon($icon);
		$this->set_categories($categories);
		$this->set_custom_help_url($custom_help_url);
		$this->set_keywords($keywords);
		$this->set_script_depends($script_depends);
		$this->set_style_depends($style_depends);
		$this->set_params($params);
	}

	protected function set_name($name){
		$this->name = $name;
	}

	protected function set_title($title){
		$this->title = $title;
	}

	protected function set_icon($icon){
		$this->icon = $icon;
	}

	protected function set_custom_help_url($custom_help_url){
		$this->custom_help_url = $custom_help_url;
	}

	protected function set_categories($categories){
		$this->categories = $categories;
	}

	protected function set_keywords($keywords){
		$this->keywords = $keywords;
	}

	protected function set_script_depends($script_depends){
		$this->script_depends = $script_depends;
	}

	protected function set_style_depends($style_depends){
		$this->style_depends = $style_depends;
	}

	protected function set_params($params){
		$this->params = $params;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_icon() {
		return $this->icon;
	}

	public function get_custom_help_url() {
		return $this->custom_help_url;
	}

	public function get_categories() {
		return $this->categories;
	}

	public function get_keywords() {
		return $this->keywords;
	}

	public function get_script_depends() {
		return $this->script_depends;
	}

	public function get_style_depends() {
		return $this->style_depends;
	}

	public function get_settings_for_display( $setting_key = null, $setting_default = null ){
		$settings = parent::get_settings_for_display($setting_key);

		$settings = !empty($settings) ? $settings : $setting_default;

		return $settings;
	}

	public function get_params() {
        return $this->params;
    }

	protected function register_controls() {
		$params = apply_filters("ptc_widget_{$this->get_name()}_register_extra_controls", $this->params);
		$common_params = apply_filters("pxl_register_widget_common_controls", $this);
		$this->render_controls($params);
		if( !empty( $common_params)){
			$this->render_controls($common_params);
		}
		do_action("ptc_widget_{$this->get_name()}_register_controls", $this);
		do_action("pxl_register_widget_common_controls_action", $this);
	}

	public function render_controls($params) {
        if(!empty($params)){
            foreach($params as $param){
            	if(isset($param['controls']) && !empty($param['controls'])){
            		$controls = isset($param['controls']) ? $param['controls'] : [];
                    $this->start_controls_section(
                        $param['name'],
                        [
                            'label' => $param['label'],
                            'tab' => $param['tab'],
                            'condition' => isset($param['condition']) ? $param['condition'] : '',
                            'conditions' => isset($param['conditions']) ? $param['conditions'] : '',
                        ]
                    );
                    foreach ($controls as $control){
                    	$control_type = isset($control['control_type']) ? $control['control_type'] : '';
                    	if($control_type == 'responsive'){
                    		$c_name = $control['name'];
                    		unset($control['name']);
                            $this->add_responsive_control($c_name, $control);
                        }
                        elseif($control_type == 'group'){
                            $this->add_group_control(
                                $control['type'],
                                $control
                            );
                        }
                        elseif($control_type == 'tab'){
                            if(isset($control['tabs']) && !empty($control['tabs'])){
                                $this->start_controls_tabs( $control['name'] );
                                foreach ($control['tabs'] as $tab){
                                    if(isset($tab['controls']) && !empty($tab['controls'])){
                                        $this->start_controls_tab(
                                            $tab['name'],
                                            [
                                                'label' => $tab['label'],
                                            ]
                                        );
                                        foreach ($tab['controls'] as $tab_control){
                                            $tab_control_type = isset($tab_control['control_type']) ? $tab_control['control_type'] : '';
                                            if($tab_control_type == 'responsive'){
                                            	$tc_name = $tab_control['name'];
                                            	unset($tab_control['name']);
                                                $this->add_responsive_control($tc_name, $tab_control);
                                            }
                                            elseif($tab_control_type == 'group'){
                                                $this->add_group_control(
                                                    $tab_control['type'],
                                                    $tab_control
                                                );
                                            }
                                            else{
                                            	$tc_name = $tab_control['name'];
                                            	unset($tab_control['name']);
                                                $this->add_control($tc_name, $tab_control);
                                            }
                                        }
                                        $this->end_controls_tab();
                                    }
                                }
                                $this->end_controls_tabs();
                            }
                        }
                        else{
                            if($control['type'] == \Elementor\Controls_Manager::REPEATER){
                                $repeater = new \Elementor\Repeater();
                                if(isset($control['controls']) && !empty($control['controls'])){
                                    foreach ($control['controls'] as $rp_control){
                                         
                                        if(isset($rp_control['control_type']) && $rp_control['control_type'] == 'responsive'){
                                        	$rp_name = $rp_control['name'];
                                        	unset($rp_control['name']);
                                            $repeater->add_responsive_control($rp_name, $rp_control);
                                        }elseif(isset($rp_control['control_type']) && $rp_control['control_type'] == 'group'){
                                            $repeater->add_group_control($rp_control['type'],$rp_control);
                                        }else{
                                        	$rp_name = $rp_control['name'];
                                        	unset($rp_control['name']);
                                            $repeater->add_control($rp_name, $rp_control);
                                        }
                                    }
                                }
                                $this->add_control($control['name'], [
									'label'       => isset($control['label']) ? $control['label'] : '',
									'type'        => isset($control['type']) ? $control['type'] : '',
									'fields'      => $repeater->get_controls(),
									'default'     => isset($control['default']) ? $control['default'] : [],
									'description' => isset($control['description']) ? $control['description'] : '',
									'condition'   => isset($control['condition']) ? $control['condition'] : '',
									'conditions'  => isset($control['conditions']) ? $control['conditions'] : '',
									'title_field' => isset($control['title_field']) ? $control['title_field'] : '',
                                ]);
                            }
                            elseif($control['type'] == 'pxl_start_popover'){
                                $this->start_popover();
                            }
                            elseif($control['type'] == 'pxl_end_popover'){
                                $this->end_popover();
                            }
                            else{
                            	$c_name = $control['name'];
                            	unset($control['name']);
                                $this->add_control($c_name, $control);
                            }
                        }
                    }
                    $this->end_controls_section();
            	}
            }
        }
	}
 
	protected function render() {
		do_action("pxl_before_widget_render_content", $this);
        pxl_get_template($this);
        do_action("pxl_after_widget_render_content", $this);
	}

	public function add_inline_editing_attributes( $key, $toolbar = 'basic' ) {
        parent::add_inline_editing_attributes( $key, $toolbar );
    }

    public function get_repeater_setting_key( $setting_key, $repeater_key, $repeater_item_index ) {
        return parent::get_repeater_setting_key( $setting_key, $repeater_key, $repeater_item_index );
    }

    public function parse_text_editor( $content ) {
        return parent::parse_text_editor($content);
    }

	public function get_setting($setting, $default = ''){
        $setting_value = parent::get_settings($setting);
        $setting_value = !empty($setting_value)?$setting_value:$default;
        return $setting_value;
    }
}