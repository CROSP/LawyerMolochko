<?php

class PxlSidebarTabs_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_sidebar_tabs';
    protected $title = 'PXL Sidebar Tabs';
    protected $icon = 'eicon-nav-menu';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"layout_section","label":"Layout","tab":"layout","controls":[{"name":"layout","label":"Layout","type":"layoutcontrol","default":"1","options":{"1":{"label":"Layout 1","image":"https:\/\/lawyermolochko.ddev.site:8443\/wp-content\/themes\/powerlegal\/elements\/assets\/layout-image\/pxl_sidebar_tabs-1.jpg"}}}]},{"name":"content_section","label":"Content","tab":"content","controls":[{"name":"sb_tabs_links","label":"Anchor Link Items","type":"repeater","description":"Anchor Link connect to inner section ID that will show when link active, click.","controls":[{"name":"sb_link_text","label":"Link Text","type":"text","label_block":true},{"name":"inner_section_ids","label":"Inner Section Ids","type":"text","label_block":true,"description":"CSS ID of inner section that will connect with Anchor Link, without #, separated by commas. Example: &quot;id1&quot;. Note: Please add Class &quot;anchor-inner-item&quot; to all inner section."}],"title_field":"{{{ sb_link_text }}}"}]}]}';
    protected $styles = array(  );
    protected $scripts = array( 'powerlegal-tabs' );
}