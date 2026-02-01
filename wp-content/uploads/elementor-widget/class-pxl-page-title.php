<?php

class PxlPageTitle_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_page_title';
    protected $title = 'PXL Page Title';
    protected $icon = 'eicon-t-letter';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"content_section","label":"Style","tab":"style","controls":[{"name":"text_align","label":"Alignment","type":"choose","control_type":"responsive","options":{"left":{"title":"Start","icon":"eicon-text-align-left"},"center":{"title":"Center","icon":"eicon-text-align-center"},"right":{"title":"End","icon":"eicon-text-align-right"}},"selectors":{"{{WRAPPER}} .pxl-pt-wrap":"text-align: {{VALUE}};"}},{"name":"title_color","label":"Title Color","type":"color","selectors":{"{{WRAPPER}} .pxl-pt-wrap .main-title":"color: {{VALUE}};"}},{"name":"pt_typography","label":"Title Typography","type":"typography","control_type":"group","selector":"{{WRAPPER}} .pxl-pt-wrap .main-title"},{"name":"sub_title_color","label":"Sub Title Color","type":"color","selectors":{"{{WRAPPER}} .pxl-pt-wrap .sub-title":"color: {{VALUE}};"}},{"name":"pst_typography","label":"Sub Title Typography","type":"typography","control_type":"group","selector":"{{WRAPPER}} .pxl-pt-wrap .sub-title"}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}