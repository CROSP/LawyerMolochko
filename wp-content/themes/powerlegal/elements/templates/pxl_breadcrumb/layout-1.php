<?php
if (!class_exists('Powerlegal_Breadcrumb')) return;
 
$breadcrumb = new Powerlegal_Breadcrumb();
$entries = $breadcrumb->get_entries();
 
if(! empty( $settings['selected_icon']['value'] ))
    $brc_divider = '<span class="br-divider '.$settings['selected_icon']['value'].' rtl-flip"></span>';
else
    $brc_divider = '<span class="br-divider pxli pxli-long-arrow-right rtl-flip"></span>';
?>
<div class="pxl-breadcrumb hover-underline">
    <?php
    foreach ( $entries as $entry ){
        $entry = wp_parse_args( $entry, array(
            'label' => '',
            'url'   => ''
        ) );

        if ( empty( $entry['label'] ) ){
            continue;
        }

        echo '<div class="br-item">';
        if ( ! empty( $entry['url'] ) ){
            printf(
                '<a class="br-link" href="%1$s">%2$s</a>%3$s',
                esc_url( $entry['url'] ),
                esc_attr( $entry['label'] ),
                $brc_divider
            );
        }else{
            printf( '<span class="br-text" >%s</span>%2$s', $entry['label'], $brc_divider );
        }
        echo '</div>';
    }
    ?>
</div>