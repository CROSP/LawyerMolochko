<?php
if ( ! class_exists( 'ReduxFrameworkInstances' ) ) {
	return;
}
 
class Powerlegal_CSS_Generator {
	/**
     * @access protected
     * @var scssc
     */
    protected $scssc = null;

    /**
     * ReduxFramework class instance
     *
     * @access protected
     * @var ReduxFramework
     */
    protected $redux = null;

    /**
     * Debug mode is turn on or not
     *
     * @access protected
     * @var boolean
     */
    protected $dev_mode = true;

    /**
     * opt_name of ReduxFramework
     *
     * @access protected
     * @var string
     */
    protected $opt_name = '';

	function __construct() {
		$this->opt_name = powerlegal()->get_option_name();  
		if ( empty( $this->opt_name ) ) {
			return;
		}
		$this->dev_mode = (defined('THEME_DEV_MODE_SCSS') && THEME_DEV_MODE_SCSS);  

        add_filter( 'pxl_scssc_lib', function(){ return 'new';} );
		add_filter( 'pxl_scssc_on', '__return_true' );
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		if ( ! class_exists( '\ScssPhp\ScssPhp\Compiler' ) ) {
			return;
		}

		$this->redux = ReduxFrameworkInstances::get_instance( $this->opt_name );

		if ( empty( $this->redux ) || ! $this->redux instanceof ReduxFramework ) {
			return;
		}
		add_action( 'wp', array( $this, 'generate_with_dev_mode' ) );
		add_action( "redux/options/{$this->opt_name}/saved", function () {
            $this->generate_file_options();
		} );
	}

	function generate_with_dev_mode() {
		if ( $this->dev_mode === true ) {
			$this->generate_file_options();
            $this->generate_file();
		}
	}

    function generate_file_options() {
        $scss_dir = get_template_directory() . '/assets/scss/';
        $this->scssc = new \ScssPhp\ScssPhp\Compiler();
        $this->scssc->setImportPaths( $scss_dir );
        $_options = $scss_dir . '_options.scss';

        $this->scssc->setFormatter('ScssPhp\ScssPhp\Formatter\Nested');
 
        $this->redux->filesystem->execute( 'put_contents', $_options, array(
            'content' => preg_replace( "/(?<=[^\r]|^)\n/", "\r\n", $this->get_options_output() )
        ) );
    }

	function generate_file() {
		$scss_dir = get_template_directory() . '/assets/scss/';
		$css_dir  = get_template_directory() . '/assets/css/';

        $this->scssc = new \ScssPhp\ScssPhp\Compiler();
        $this->scssc->setImportPaths( $scss_dir );

		
		$css_file = $css_dir . 'style.css';
         
        $this->scssc->setFormatter('ScssPhp\ScssPhp\Formatter\Nested');
  
        $this->scssc->setSourceMap(\ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE);
        $this->scssc->setSourceMapOptions(array(
            'sourceMapWriteTo'  => $css_file . ".map",
            'sourceMapURL'      => "style.css.map",
            'sourceMapFilename' => $css_file,
            'sourceMapBasepath' => $scss_dir,
            'sourceRoot'        => $scss_dir,
        ));
  
		$this->redux->filesystem->execute( 'put_contents', $css_file, array(
			'content' => preg_replace( "/(?<=[^\r]|^)\n/", "\r\n", $this->scssc->compile( '@import "style.scss"' ) )
		) );
         
	}

    function generate_min_file(){   
        // Theme
        $scss_dir = get_template_directory() . '/assets/scss/';
        $css_dir  = get_template_directory() . '/assets/css/';
         
        $css_file = $css_dir . 'style.min.css';
          
        $this->scssc = new \ScssPhp\ScssPhp\Compiler();
        $this->scssc->setImportPaths( $scss_dir );
 
        $this->scssc->setSourceMap(\ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE);
        $this->scssc->setSourceMapOptions(array(
            'sourceMapWriteTo'  => $css_file . ".map",
            'sourceMapURL'      => "style.min.css.map",
            'sourceMapFilename' => $css_file,
            'sourceMapBasepath' => $scss_dir,
            'sourceRoot'        => $scss_dir,
        ));

        $this->scssc->setFormatter( 'ScssPhp\ScssPhp\Formatter\Crunched' );
         
        $this->redux->filesystem->execute( 'put_contents', $css_file, array(
            'content' => $this->scssc->compile( '@'.'import "style.scss"' ) 
        ) );
       
    }

	protected function print_scss_opt_colors($variable,$param){
        if(is_array($variable)){
            $k = [];
            $v = [];
            foreach ($variable as $key => $value) {
                $k[] = str_replace('-', '_', $key);
                $v[] = 'var(--'.str_replace(['#',' '], [''],$key).'-color)';
            }
            if($param === 'key'){
                return implode(',', $k);
            }else{
                return implode(',', $v);
            }
            
        } else {
            return $variable;
        }
    }

	protected function get_options_output() {
		$theme_colors                    = powerlegal_configs('theme_colors');
        $links                           = powerlegal_configs('link');
        $body                            = powerlegal_configs('body');
        $header                          = powerlegal_configs('header');
        $heading                         = powerlegal_configs('heading');
        $heading_font_size               = powerlegal_configs('heading_font_size');
        $menu                            = powerlegal_configs('menu');
        $submenu                        = powerlegal_configs('dropdown');
        $mobile_menu                     = powerlegal_configs('mobile_menu');
        $mobile_submenu                  = powerlegal_configs('mobile_submenu');
        $border                          = powerlegal_configs('border');
        $divider                         = powerlegal_configs('divider');
        $logo                            = powerlegal_configs('logo');
        $button                          = powerlegal_configs('button');

		ob_start();

        foreach ($theme_colors as $key => $value) {
            printf('$%1$s_color: %2$s;', str_replace('-', '_', $key), 'var(--'.str_replace(['#',' '], [''],$key).'-color)' );
        }
   
        foreach ($links as $key => $value) {
            printf('$link_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--link-'.$key.')');
        }
        foreach ($body as $key => $value) {
            printf('$body_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--body-'.$key.')');
        }
        foreach ($heading as $key => $value) {
            printf('$heading_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--heading-'.$key.')');
        }
        foreach ($heading_font_size as $key => $value) {
            printf('$heading_font_size_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--heading-font-size-'.$key.')'); 
        }
        foreach ($logo as $key => $value) {
            printf('$logo_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--logo-'.$key.')');
        }
        foreach ($header as $key => $value) {
            printf('$header_%1$s: %2$s;', str_replace('-', '_', $key), $value);
        }
        foreach ($menu as $key => $value) {
            // Use CSS variable for font_family instead of hardcoded value
            if ($key === 'font_family') {
                printf('$menu_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--heading-font-family)');
            } else {
                printf('$menu_%1$s: %2$s;', str_replace('-', '_', $key), $value);
            }
        }
        foreach ($submenu as $key => $value) {
            printf('$submenu_%1$s: %2$s;', str_replace('-', '_', $key), $value);
        }
        foreach ($mobile_menu as $key => $value) {
            // Use CSS variable for font_family instead of hardcoded value
            if ($key === 'font_family') {
                printf('$mobile_menu_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--heading-font-family)');
            } else {
                printf('$mobile_menu_%1$s: %2$s;', str_replace('-', '_', $key), $value);
            }
        }
        foreach ($mobile_submenu as $key => $value) {
            // Use CSS variable for font_family instead of hardcoded value
            if ($key === 'font_family') {
                printf('$mobile_submenu_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--body-font-family)');
            } else {
                printf('$mobile_submenu_%1$s: %2$s;', str_replace('-', '_', $key), $value);
            }
        }
        foreach ($border as $key => $value) {
            printf('$border_%1$s: %2$s;', str_replace('-', '_', $key), $value);
            printf('$border_%1$s_rgb: %2$s;', str_replace('-', '_', $key), $value);
        }
        foreach ($divider as $key => $value) {
            printf('$divider_%1$s: %2$s;', str_replace('-', '_', $key), $value);
        }
        foreach ($button as $key => $value) {
            // Use CSS variable for font_family instead of hardcoded value
            if ($key === 'font_family') {
                printf('$button_%1$s: %2$s;', str_replace('-', '_', $key), 'var(--body-font-family)');
            } else {
                printf('$button_%1$s: %2$s;', str_replace('-', '_', $key), $value);
            }
        }
		return ob_get_clean();
	}

}
 

new Powerlegal_CSS_Generator();
