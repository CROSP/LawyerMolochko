<?php
?>
<div class="pxl-video-popup">
    <div class="video-content-inner">
        <a class="video-play-button <?php echo esc_attr($settings['video_type']);?>" href="<?php echo esc_url($settings['video_link']['url']);?>">
            <i class="zmdi zmdi-play"></i>
        </a>
        <?php
        if (!empty($settings['description_text'])){
            ?>
            <p class="button-text"><?php pxl_print_html(nl2br($settings['description_text'])); ?></p>
            <?php
        }
        ?>
    </div>
</div>

