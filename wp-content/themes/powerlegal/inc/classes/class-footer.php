<?php
//use Elementor\Core\Files\CSS\Post as Post_CSS;
if (!class_exists('Powerlegal_Footer')) {
     
    class Powerlegal_Footer
    {
         
        public function getFooter(){
            $remove_footer        = (int)powerlegal()->get_opt('remove_footer');
            if ($remove_footer){
                return true;
            }
            $footer_layout = (int)powerlegal()->get_opt('footer_layout');
            $footer_type = $footer_layout <= 0 ? 'df' : 'el';
            $css_classes = [
                'pxl-footer',
                'footer-type-'.$footer_type,
                'footer-layout-'.$footer_layout
            ];
            $footer_wrap_cls = trim(implode(' ', $css_classes));

            if ($footer_layout <= 0 || !class_exists('Pxltheme_Core') || !is_callable( 'Elementor\Plugin::instance' )) {  
                ?>
                <footer id="pxl-footer" class="<?php echo esc_attr($footer_wrap_cls);?>">
                    <?php do_action('powerlegal_before_footer'); ?>
                    <div class="pxl-footer-bottom">
                        <div class="container">
                            <div class="row justify-content-center align-items-center">
                                <div class="col-12 col-md-auto text-center">
                                    <div class="pxl-copyright-text pxl-footer-copyright">
                                        <?php 
                                        printf( esc_html__('Copyright © %s powerlegal by %s. All Rights Reserved','powerlegal'), date('Y'), '<a href="'.esc_url('https://themeforest.net/user/7iquid/portfolio').'" target="_blank" rel="nofollow">7iquid</a>');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php do_action('powerlegal_after_footer');  ?>
                </footer>
                <?php 
            } else { 
                ?>
                <footer id="pxl-footer" class="<?php echo esc_attr($footer_wrap_cls);?>">
                    <?php 
                        do_action('powerlegal_before_footer');
                        echo Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $footer_layout);
                        do_action('powerlegal_after_footer');
                    ?>
                </footer> 
                <?php  
            } 
        }
 
    }
}
 
 