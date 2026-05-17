<?php
/**
 * @package Powerlegal
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="pxl-entry-content clearfix">
        <?php
            the_content();
            powerlegal()->page->get_link_pages();
        ?>
    </div> 
</article> 
