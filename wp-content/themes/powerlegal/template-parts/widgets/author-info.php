<?php
defined( 'ABSPATH' ) or exit( -1 );

/**
 * Author Information widgets
 *
 */

if(!function_exists('pxl_register_wp_widget')) return;
add_action( 'widgets_init', function(){
    pxl_register_wp_widget( 'PXL_Author_Info_Widget' );
});
class PXL_Author_Info_Widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
            'pxl_author_info_widget',
            esc_html__('* Pxl Author Information', 'powerlegal'),
            array('description' => esc_html__('Show Author Information', 'powerlegal'),)
        );
    }

    function widget($args, $instance)
    {
        extract($args);
        $author_image_id = isset($instance['author_image']) ? (!empty($instance['author_image']) ? $instance['author_image'] : '') : '';
        $author_image_url = wp_get_attachment_image_url($author_image_id, '');
        $author_name = isset($instance['author_name']) ? (!empty($instance['author_name']) ? $instance['author_name'] : '') : '';
        $author_position = isset($instance['author_position']) ? (!empty($instance['author_position']) ? $instance['author_position'] : '') : '';
        $description = isset($instance['description']) ? (!empty($instance['description']) ? $instance['description'] : '') : '';
        ?>
        <div class="pxl-author-info widget" >
            <div class="content-inner">
                <div class="author-image">
                    <div class="image-wrap flash-hover">
                        <img src="<?php echo esc_url($author_image_url)?>" alt="<?php echo esc_attr__('Author Image', 'powerlegal');?>">
                    </div>
                    <?php
                    if (!empty($author_name) || !empty($author_position)){
                        ?>
                        <div class="name-position">
                            <?php if (!empty($author_position)): ?>
                                <div class="author-position">
                                    <?php echo esc_html($author_position);?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($author_name)): ?>
                                <h4 class="author-name"><?php echo esc_html($author_name);?></h4>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php if (!empty($description)): ?>
                    <div class="author-desc">
                        <?php
                        if (function_exists('lcb_print_html')){
                            lcb_print_html(nl2br($description));
                        }else{
                            echo wp_kses_post($description);
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['author_image'] = strip_tags($new_instance['author_image']);
        $instance['author_position'] = strip_tags($new_instance['author_position']);
        $instance['author_name'] = strip_tags($new_instance['author_name']);
        $instance['description'] = strip_tags($new_instance['description']);
        return $instance;
    }

    function form($instance)
    {
        $author_image = isset($instance['author_image']) ? esc_attr($instance['author_image']) : '';
        $author_name = isset($instance['author_name']) ? esc_html($instance['author_name']) : '';
        $author_position = isset($instance['author_position']) ? esc_html($instance['author_position']) : '';
        $description = isset($instance['description']) ? esc_html($instance['description']) : '';
        ?>
        <div class="powerlegal-image-wrap">
            <label for="<?php echo esc_url($this->get_field_id('author_image')); ?>"><?php esc_html_e('Author Image', 'powerlegal'); ?></label>
            <input type="hidden" class="widefat hide-image-url"
                   id="<?php echo esc_attr($this->get_field_id('author_image')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('author_image')); ?>"
                   value="<?php echo esc_attr($author_image) ?>"/>
            <div class="pxl-show-image">
                <?php
                if ($author_image != "") {
                    ?>
                    <img src="<?php echo wp_get_attachment_image_url($author_image) ?>">
                    <?php
                }
                ?>
            </div>
            <?php
            if ($author_image != "") {
                ?>
                <a href="#" class="pxl-select-image" style="display: none;"><?php esc_html_e('Select Image', 'powerlegal'); ?></a>
                <a href="#" class="pxl-remove-image"><?php esc_html_e('Remove Image', 'powerlegal'); ?></a>
                <?php
            } else {
                ?>
                <a href="#" class="pxl-select-image"><?php esc_html_e('Select Image', 'powerlegal'); ?></a>
                <a href="#" class="pxl-remove-image" style="display: none;"><?php esc_html_e('Remove Image', 'powerlegal'); ?></a>
                <?php
            }
            ?>
        </div>
        <p>
            <label for="<?php echo esc_url($this->get_field_id('author_position')); ?>"><?php esc_html_e( 'Author Position', 'powerlegal' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('author_position') ); ?>" name="<?php echo esc_attr( $this->get_field_name('author_position') ); ?>" type="text" value="<?php echo esc_attr( $author_position ); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_url($this->get_field_id('author_name')); ?>"><?php esc_html_e( 'Author Name', 'powerlegal' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('author_name') ); ?>" name="<?php echo esc_attr( $this->get_field_name('author_name') ); ?>" type="text" value="<?php echo esc_attr( $author_name ); ?>" />
        </p>

        <p>
            <label for="<?php echo esc_url($this->get_field_id('description')); ?>"><?php esc_html_e('Description', 'powerlegal'); ?></label>
            <textarea class="widefat" rows="4" cols="20" id="<?php echo esc_attr($this->get_field_id('description')); ?>" name="<?php echo esc_attr($this->get_field_name('description')); ?>"><?php echo wp_kses_post($description); ?></textarea>
        </p>
        <?php
    }

}