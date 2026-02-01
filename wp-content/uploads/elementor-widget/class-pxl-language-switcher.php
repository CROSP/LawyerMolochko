<?php

class PxlLanguageSwitcher_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_language_switcher';
    protected $title = 'PXL Language Switcher';
    protected $icon = 'eicon-editor-list-ul';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"section_list","label":"Content","tab":"content","controls":[{"name":"current","label":"Current Item","type":"text","label_block":true},{"name":"current_item_typography","label":"Current Item Typography","type":"typography","control_type":"group","selector":"{{WRAPPER}} .pxl-language-switcher .current-item"},{"name":"current_item_color","label":"Current Item Color","type":"color","selectors":{"{{WRAPPER}} .pxl-language-switcher .current-item":"color: {{VALUE}};","{{WRAPPER}} .pxl-language-switcher .current-item svg":"fill: {{VALUE}};"}},{"name":"menu_item","label":"Item","type":"repeater","controls":[{"name":"text","label":"Text","type":"text","label_block":true},{"name":"link","label":"Link","type":"url","label_block":true}],"title_field":"{{{ text }}}"}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}