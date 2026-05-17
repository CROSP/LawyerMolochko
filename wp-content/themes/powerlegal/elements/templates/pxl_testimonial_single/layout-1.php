<div class="pxl-testimonial-single layout1">
    <?php
    if( !empty($settings['selected_icon'])){
        echo '<div class="pxl-testimonial-icon">';
        \Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true', 'class' => '' ], 'span' );
        echo '</div>';
    }
    ?>
    <div class="pxl-testimonial-content">
        <?php
        if(!empty($settings['tt_content'])){
            ?>
            <div class="client-said"><?php pxl_print_html($settings['tt_content']); ?></div>
            <?php
        }
        ?>
        <div class="client-info">
            <?php
            if(!empty($settings['tt_description'])){
                ?>
                <div class="client-description"><?php pxl_print_html($settings['tt_description']); ?></div>
                <?php
            }
            ?>
            <?php if(!empty($settings['rating']) && $settings['rating'] != 'none') : ?>
                <div class="client-rating <?php echo esc_attr($settings['rating']); ?>">
                    <i class="zmdi zmdi-star"></i>
                    <i class="zmdi zmdi-star"></i>
                    <i class="zmdi zmdi-star"></i>
                    <i class="zmdi zmdi-star"></i>
                    <i class="zmdi zmdi-star"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
