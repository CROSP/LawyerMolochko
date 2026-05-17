<?php

class PxlLogo_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_logo';
    protected $title = 'PXL Logo';
    protected $icon = 'eicon-image';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"content_section","label":"Content","tab":"content","controls":[{"name":"logo","label":"Logo","type":"media"},{"name":"logo_max_width","label":"Max Width","type":"slider","description":"Enter number.","range":{"px":{"min":0,"max":3000}},"control_type":"responsive","selectors":{"{{WRAPPER}} .pxl-logo img":"max-width: {{SIZE}}{{UNIT}};"}},{"name":"logo_align","label":"Alignment","type":"choose","control_type":"responsive","options":{"start":{"title":"Start","icon":"eicon-text-align-left"},"center":{"title":"Center","icon":"eicon-text-align-center"},"end":{"title":"End","icon":"eicon-text-align-right"}},"selectors":{"{{WRAPPER}} .pxl-logo":"justify-content: {{VALUE}};"}},{"name":"logo_link","label":"Link","type":"url"}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}