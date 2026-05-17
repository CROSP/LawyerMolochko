<?php

if (!defined('ABSPATH')) {
    die();
}
if (!class_exists('PXL_Woo_Attributes_Handle')) {
    class PXL_Woo_Attributes_Handle {
        public function __construct() {
            add_action('init', array($this, 'pxl_import_woo_term'), 29);
        }

        function pxl_import_woo_term(){

            $upload_dir = wp_upload_dir();

            $current_id = get_option('pxl_import_demo_id',true);

            if( !$current_id){

                return;

            }

            $folder_name = sanitize_title($current_id);

            $folder_dir = $upload_dir['basedir'].DIRECTORY_SEPARATOR.'pxlart_temp'.DIRECTORY_SEPARATOR.$folder_name.DIRECTORY_SEPARATOR; 
       
            $this->pxl_woo_attributes_term_import($folder_dir . 'woo_attributes.json');
      
        }

        function pxl_woo_attributes_term_import($file){

            if ( ! file_exists( $file ) ) {
                return new WP_Error( 'file_missing', 'File not found.' );
            }
   
            update_option("pxl_woo_term_imported","imported");

            $data = file_get_contents($file);

            $atts_data = json_decode($data, true);

            $attributes = [];

            foreach ( wc_get_attribute_taxonomies() as $attr ) {

                $attributes[ $attr->attribute_name ] = $attr;

            }      

            foreach ( $atts_data['tax'] as $slug => $att ) {
     
                if ( empty( $att['data'] ) ) {
                    continue;
                }

                $data = $att['data'];

                wc_update_attribute( $attributes[ $data['attribute_name']]->attribute_id, array(
                    'name'         => $data['attribute_label'],
                    'slug'         => $data['attribute_name'],
                    'type'         => $data['attribute_type'],
                    'order_by'     => $data['attribute_orderby'],
                    'has_archives'     => $data['attribute_public']
                ) );                
                
                if ( ! empty( $att['terms'] ) && is_array( $att['terms'] ) ) {

                    foreach ( $att['terms'] as $term_slug => $term_data ) {
                    
                        $term = get_term_by( 'slug', $term_slug, $slug );
                        if ( ! $term || is_wp_error( $term ) ) {
                            continue; 
                        }

                        if ( ! empty( $term_data['meta'] ) && is_array( $term_data['meta'] ) ) {
                            foreach ( $term_data['meta'] as $meta_key => $meta_value ) {
                                update_term_meta( $term->term_id, $meta_key, $meta_value );
                            }
                        }
                    }
                }
            }        
        }
    }
    new PXL_Woo_Attributes_Handle(); 
}