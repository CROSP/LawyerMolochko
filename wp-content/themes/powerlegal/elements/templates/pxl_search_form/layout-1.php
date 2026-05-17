<?php 
$default_settings = [
    'search_type' => '1',
    'placeholder' => '',
];

$settings = array_merge($default_settings, $settings);
extract($settings); 

?>
<?php if($search_type == '1'): ?>
    <div class="pxl-search-wrap search-normal">
        <form role="search" method="get" class="pxl-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" class="pxl-search-field" placeholder="<?php echo esc_attr( $settings['placeholder']); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off"/>
            <button type="submit" class="pxl-search-submit" value=""><span class="pxli-search-400"></span></button>
        </form>
    </div>
<?php endif; ?>
<?php if($search_type == '2'): ?>
    <div class="pxl-search-wrap search-ajax">
        <div class="pxl-ajax-search">
            <form role="search" method="get" class="pxl-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <fieldset>
                    <div class="search-button-group">
                        <a href="#" class="search-clear remove" title="Clear"></a>
                        <span class="search-icon"><span class="pxli-search-400"></span></span>
                        <input type="search" class="pxl-search-field" placeholder="<?php echo esc_attr( $settings['placeholder']); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off"/>
                        <button type="submit" class="pxl-search-submit" value="<?php echo esc_attr__( 'Search', 'powerlegal' ); ?>"><span class="pxli-search-400"></span></button>
                    </div>
                    <input type="hidden" name="post_type" value="product">
                    <div class="autocomplete-wrapper"><ul class="product_list_result row" style="display: none;"></ul></div>
                </fieldset>
            </form>
        </div>
    </div>
<?php endif; ?>

 