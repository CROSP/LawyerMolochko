<?php

class PxlHistory_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_history';
    protected $title = 'PXL History';
    protected $icon = 'eicon-history';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"layout_section","label":"Layout","tab":"layout","controls":[{"name":"layout","label":"Layout","type":"layoutcontrol","default":"1","options":{"1":{"label":"Layout 1","image":"https:\/\/lawyermolochko.ddev.site:8443\/wp-content\/themes\/powerlegal\/elements\/assets\/layout-image\/pxl_history-1.jpg"}}}]},{"name":"source_section","label":"Source Settings","tab":"content","controls":[{"name":"history_year","label":"Year","type":"text","default":"1988"},{"name":"history_title","label":"History Title","type":"textarea","rows":2,"default":"Birth Of Company"},{"name":"history_items","label":"History Items","type":"repeater","controls":[{"name":"content_template","label":"Select Templates","description":"Please create your layout before choosing. <a href=\"https:\/\/lawyermolochko.ddev.site:8443\/wp-admin\/edit.php?post_type=pxl-template\">Click Here<\/a>","type":"select","default":"","options":{"0":"None","4602":"New York Office","4651":"Washington Office","5063":"History_01","5075":"History_02","5120":"History_03","5134":"History_04","5137":"History_05","5140":"History_06","5434":"Accordion_01","6085":"History_Accordion","6518":"About_Me_01","6531":"About_Me_02","7523":"Tab_Why_Choose_Us","7549":"Tab_Mission_Vision","7584":"Tab_History","7591":"Tab_Awards"}}]}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}