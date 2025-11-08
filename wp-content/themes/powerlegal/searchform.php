<?php
/**
 * Search Form
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url( '/' )); ?>">
    <div class="searchform-wrap">
        <input type="text" placeholder="<?php esc_attr_e('Search...', 'powerlegal'); ?>" required />
        <button type="submit" class="search-submit"><span class="pxli-search-400"></span></button>
    </div>
</form>