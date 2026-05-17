<?php

class PxlDownload_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_download';
    protected $title = 'PXL Download';
    protected $icon = 'eicon-file-download';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"layout_section","label":"Layout","tab":"layout","controls":[{"name":"layout","label":"Layout","type":"layoutcontrol","default":"1","options":{"1":{"label":"Layout 1","image":"https:\/\/lawyermolochko.ddev.site:8443\/wp-content\/themes\/powerlegal\/elements\/assets\/layout-image\/pxl_download-1.jpg"}},"prefix_class":"pxl-download-layout-"}]},{"name":"list_section","label":"Content Settings","tab":"content","controls":[{"name":"el_title","label":"Element Title","type":"textarea","label_block":true},{"name":"download_description","label":"Description","type":"textarea","label_block":true},{"name":"download","label":"Download Lists","type":"repeater","default":[],"controls":[{"name":"file_name","label":"File Name","type":"textarea","label_block":true},{"name":"file_type_icon","label":"File Icon","type":"icons","fa4compatibility":"icon","default":{"value":"fas fa-star","library":"fa-solid"}},{"name":"file_size","label":"File Size","type":"text"},{"name":"link","label":"Link","type":"url"}],"title_field":"{{{ file_name }}}"}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}