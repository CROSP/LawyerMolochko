<?php

class PxlMailchimp_Widget extends Pxltheme_Core_Widget_Base{
    protected $name = 'pxl_mailchimp';
    protected $title = 'PXL Mailchimp';
    protected $icon = 'eicon-email-field';
    protected $categories = array( 'pxltheme-core' );
    protected $params = '{"sections":[{"name":"section_style","label":"Style","tab":"style","controls":[{"name":"style","label":"Style","type":"select","options":{"style-default":"Default","style-1":"Style 1"},"default":"style-default"}]}]}';
    protected $styles = array(  );
    protected $scripts = array(  );
}