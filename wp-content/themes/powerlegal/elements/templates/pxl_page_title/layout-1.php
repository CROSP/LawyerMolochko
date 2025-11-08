<?php
if (!class_exists('Powerlegal_Page_Title')) return;
$titles = powerlegal()->pagetitle->get_title();
?>

<div class="pxl-pt-wrap">
    <h1 class="main-title"><?php pxl_print_html($titles['title']) ?></h1>
    <div class="sub-title"><?php pxl_print_html($titles['sub_title']) ?></div>
</div>