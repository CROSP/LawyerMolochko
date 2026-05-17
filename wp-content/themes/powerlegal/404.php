<?php
/**
 * @package Powerlegal
 */
get_header(); ?>
<?php
    $img_404_bg         = powerlegal()->get_theme_opt('img_404_background', []);
    $wrap_class = "";
    if (!empty($img_404_bg["background-image"])){
        $wrap_class = "has-background";
    }
    $img_404_1          = powerlegal()->get_theme_opt('img_404_1', []);
    $heading_404_page   = powerlegal()->get_theme_opt('heading_404_page', '');
    $desc_404_page      = powerlegal()->get_theme_opt('desc_404_page', '');
    $btn_text_404_page  = powerlegal()->get_theme_opt('btn_text_404_page', esc_html__('back to homepage', 'powerlegal'));
?>
<div class="page-404-wrap pxl-404-page relative">
    <div class="container">
        <main id="pxl-content-main" class="d-flex justify-content-center text-center <?php echo esc_attr($wrap_class);?>">
            <div class="pxl-error-inner">
                <?php if(!empty($img_404_1['url'])): ?>
                    <div class="image-wrap">
                        <img src="<?php echo esc_url($img_404_1['url']) ?>" class="img-layer img-1 shape-animate1"/>
                    </div>
                <?php endif; ?>
                <div class="number-wrap">
                    <span>404</span>
                </div>
                <?php if(!empty($heading_404_page)): ?>
                    <h2 class="pxl-error-title">
                        <?php  echo esc_html( $heading_404_page ) ?>
                    </h2>
                <?php else:  ?>
                    <h2 class="pxl-error-title df">
                        <?php echo esc_html__( 'OOPS! Page Not Found!', 'powerlegal' );?>
                    </h2>
                <?php endif; ?>
                <?php if(!empty($desc_404_page)): ?>
                    <div class="desc">
                        <?php echo esc_html( $desc_404_page);  ?>
                    </div>
                <?php else: ?>
                    <div class="desc">
                        <span><?php echo esc_html__( 'The page you are looking is not available or has been removed. Try going to Home Page by using the button below.', 'powerlegal' );?></span>
                    </div>
                <?php endif; ?>
                <div class="button-wrapper">
                    <a class="btn" href="<?php echo esc_url(home_url('/')); ?>">
                        <span><?php echo esc_html($btn_text_404_page); ?></span>
                    </a>
                </div>
            </div>
        </main>    
    </div>
         
    <?php if(!empty($img_404_foot['url'])): ?> <img src="<?php echo esc_url($img_404_foot['url']) ?>" class="img-404-foot"/> <?php endif; ?>
     
</div>
 
<?php get_footer();
