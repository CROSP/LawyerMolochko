<?php
if (!class_exists('Powerlegal_Header')) {
    class Powerlegal_Header
    {
        public function getHeader()
        {
            $remove_header        = (int)powerlegal()->get_opt('remove_header');
            if ($remove_header){
                return true;
            }
            $header_layout        = (int)powerlegal()->get_opt('header_layout');
            $header_sticky_layout = (int)powerlegal()->get_opt('header_sticky_layout'); 
            $header_mobile_layout = (int)powerlegal()->get_opt('header_mobile_layout');
            $args = [
                'header_layout'        => $header_layout,
                'header_sticky_layout' => $header_sticky_layout,
                'header_mobile_layout' => $header_mobile_layout
            ];
            if ($header_layout <= 0 || !class_exists('Pxltheme_Core') || !is_callable( 'Elementor\Plugin::instance' )) {
                get_template_part( 'template-parts/header/default', '' );
            } else {
                get_template_part( 'template-parts/header/elementor','', $args );
            } 
        }
         
        public function get_header_css_class($args = []){
            
            $args = wp_parse_args($args, [
                'class'                => '',
                'header_layout'        => (int)powerlegal()->get_opt('header_layout','0'), 
                'header_transparent'         => powerlegal()->get_opt('header_transparent'),
                'header_sticky_layout' => (int)powerlegal()->get_opt('header_sticky_layout'),
                'header_mobile_layout' => (int)powerlegal()->get_opt('header_mobile_layout'),
                'header_mobile_transparent'  => powerlegal()->get_opt('header_mobile_transparent')
            ]);
             
            $header_type = $args['header_layout'] <=0 ? 'df' : 'el';
            $header_mobile_type = $args['header_mobile_layout'] <=0 ? 'df' : 'el';
             
            $classes = [
                'pxl-header',
                'header-type-'.$header_type,
                'header-layout-'.$args['header_layout'],
                'header-mobile-type-'.$header_mobile_type
            ];
       
             
            if($args['header_transparent'] == '1') $classes[] = 'header-transparent transparent-desktop';
            if($args['header_mobile_transparent'] == '1') $classes[] = 'header-transparent transparent-mobile';
            if(!empty($args['class'])) $classes[] = $args['class'];

            return implode(' ', $classes);
        }
         
    }
}
 
