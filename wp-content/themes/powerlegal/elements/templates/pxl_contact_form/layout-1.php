<?php
$default_settings = [
    'ctf7_slug' => '',
];
$settings = array_merge($default_settings, $settings);
extract($settings);
$html_id = pxl_get_element_id($settings);
$ctf7_id = '';

if(class_exists('WPCF7') && !empty($ctf7_slug)){
    $cf7 = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');
    if ($cf7) {
        foreach ($cf7 as $cform) {
            if ($cform->post_name == $ctf7_slug){
                $ctf7_id = $cform->ID;
                break;
            }
        }
    }
    if (!empty($ctf7_id)){
        ?>
        <div id="<?php echo esc_attr($html_id); ?>" class="pxl-contact-form7">
            <div class="pxl-contact-form-inner">
                <?php echo do_shortcode('[contact-form-7 id="'.esc_attr( $ctf7_id ).'"]'); ?>
            </div>
        </div>
        <?php
    }
}
