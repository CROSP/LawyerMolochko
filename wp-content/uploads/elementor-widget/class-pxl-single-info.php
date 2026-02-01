<?php

class PxlSingleInfo_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_single_info';
    protected $title = 'PXL Single Info';
    protected $icon = 'eicon-price-list';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"layout_section","label":"Layout","tab":"layout","controls":[{"name":"layout","label":"Layout","type":"layoutcontrol","default":"1","options":{"1":{"label":"Layout 1","image":"https:\/\/lawyermolochko.ddev.site:8443\/wp-content\/themes\/powerlegal\/elements\/assets\/layout-image\/pxl_single_info-1.jpg"}},"prefix_class":"pxl-single-info-layout-"}]},{"name":"content_section","label":"Content Settings","tab":"content","controls":[{"name":"el_title","label":"Element Title","type":"textarea","label_block":true},{"name":"single_info_items","label":"List Items","type":"repeater","controls":[{"name":"info_icon","label":"Icon","type":"icons","fa4compatibility":"icon","default":{"value":"fas fa-star","library":"fa-solid"}},{"name":"info_label","label":"Label","type":"text"},{"name":"info_text","label":"Text","type":"text"}],"title_field":"{{{ info_label }}}","separator":"after"},{"name":"share_icon","label":"Share Label Icon","type":"icons","fa4compatibility":"icon","default":{"value":"fas fa-star","library":"fa-solid"}}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}