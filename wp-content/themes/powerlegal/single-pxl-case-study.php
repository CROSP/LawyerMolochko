<?php
/**
 * @package Powerlegal
 */
get_header();
?>
<div class="container">
    <div class="row">
        <div id="pxl-content-area" class="col-12">
            <main id="pxl-content-main" class="pxl-content-main">
                <?php while ( have_posts() ) {
                    the_post();
                    get_template_part( 'template-parts/content/content','pxl-case-study' );
                    if ( comments_open() || get_comments_number() ) {
                        comments_template();
                    }
                } ?>
            </main>
        </div>
    </div>
</div>
<?php get_footer();
