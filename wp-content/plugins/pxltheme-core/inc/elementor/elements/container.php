<?php
namespace Elementor\Includes\Elements;

/*use Elementor\Controls_Manager;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;
use Elementor\Element_Base;
use Elementor\Embed;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Flex_Container;
use Elementor\Group_Control_Flex_Item;
use Elementor\Group_Control_Grid_Container;
use Elementor\Plugin;
use Elementor\Shapes;
use Elementor\Utils;*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PXL_Element_Container extends Container {

	 
	public function __construct( array $data = [], array $args = null ) {
		parent::__construct( $data, $args );
	}

	   
	/**
	 * Render the element JS template.
	 *
	 * @return void
	 */
	protected function content_template() {
		?>
		<#
        var pxl_container_start = elementor.hooks.applyFilters('pxl_container_start_render', '', settings, this);
        #>
        {{{pxl_container_start}}}
          
		<# if ( 'boxed' === settings.content_width ) { #>
			<div class="e-con-inner">
		<#
		}
		if ( settings.background_video_link ) {
			let videoAttributes = 'autoplay muted playsinline';

			if ( ! settings.background_play_once ) {
				videoAttributes += ' loop';
			}

			view.addRenderAttribute( 'background-video-container', 'class', 'elementor-background-video-container' );

			if ( ! settings.background_play_on_mobile ) {
				view.addRenderAttribute( 'background-video-container', 'class', 'elementor-hidden-mobile' );
			}
			#>
			<div {{{ view.getRenderAttributeString( 'background-video-container' ) }}}>
				<div class="elementor-background-video-embed"></div>
				<video class="elementor-background-video-hosted elementor-html5-video" {{ videoAttributes }}></video>
			</div>
		<# } #>
		<div class="elementor-shape elementor-shape-top"></div>
		<div class="elementor-shape elementor-shape-bottom"></div>
		<# if ( 'boxed' === settings.content_width ) { #>
			</div>
		<# } #>
		<?php
	}
   
	/**
	 * Before rendering the container content. (Print the opening tag, etc.)
	 *
	 * @return void
	 */
	public function before_render() {
		$settings = $this->get_settings_for_display();
		$link = $settings['link'];

		if ( ! empty( $link['url'] ) ) {
			$this->add_link_attributes( '_wrapper', $link );
		}

		?><<?php $this->print_html_tag(); ?> <?php $this->print_render_attribute_string( '_wrapper' ); ?>>
		<?php 
            $pxl_container_start_render = apply_filters('pxl_container_start_render','', $settings, $this);
            if(!empty($pxl_container_start_render)) echo $pxl_container_start_render;
        ?>
		<?php
		if ( $this->is_boxed_container( $settings ) ) { ?>
			<div class="e-con-inner">
		<?php }

		$this->render_video_background();

		if ( ! empty( $settings['shape_divider_top'] ) ) {
			$this->render_shape_divider( 'top' );
		}

		if ( ! empty( $settings['shape_divider_bottom'] ) ) {
			$this->render_shape_divider( 'bottom' );
		}
	}
     
	/*protected function register_layout_tab() {
		$this->register_container_layout_controls();

		$this->register_items_layout_controls();

		do_action("ptc_container_{$this->get_name()}_register_controls", $this);
	}*/

	   

	/**
	 * Register the Container's controls.
	 *
	 * @return void
	 */
	/*protected function register_controls() {
		$this->register_layout_tab();
		$this->register_style_tab();
		$this->register_advanced_tab();
		 
		do_action("ptc_{$this->get_name()}_register_controls_tab", $this);
	}*/
 
}
