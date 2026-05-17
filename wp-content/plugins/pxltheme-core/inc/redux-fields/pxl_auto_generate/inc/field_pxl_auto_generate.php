<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ReduxFramework_pxl_auto_generate')) {
    class ReduxFramework_pxl_auto_generate {
        function __construct($field = array(), $value = '', $parent) {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;

            $this->extension_url = pxl_redux_fields()->extensions_url . 'pxl_auto_generate/';
            
        }

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since ReduxFramework 1.0.0
         */
        function render() {
            ?>
                <input type="text" class="pxl-auto-generate regular-text" name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>" value="<?php echo esc_attr($this->value) ?>" readonly />
                <span class="btn-regenerate" style="cursor: pointer;"><i class="fa fa-refresh" aria-hidden="true"></i></span>
            <?php
        }

        public function enqueue() {
            if (!wp_script_is('pxl-auto-generate-js')) {
                wp_enqueue_script(
                    'pxl-auto-generate-js',
                    $this->extension_url . 'inc/field_pxl_auto_generate.js',
                    array('jquery'),
                    time(),
                    true
                );
                wp_localize_script('pxl-auto-generate-js', 'pxl_ajax_url', admin_url('admin-ajax.php'));
            }
        }
    }
}