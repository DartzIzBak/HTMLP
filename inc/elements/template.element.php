<?php

namespace htmlpelements;

use htmlp\HTMLP;

class TemplateHE extends BaseElement {
    public $template;

    public function __construct( $htmlp, $type = '' ) {
        parent::__construct($htmlp);
    }

    public function __toString() {
        $htmlp = new HTMLP();
        global $htmlp_templates;

        $template_html = $htmlp_templates[$this->template];

        /* Temporary */
        $template_html = str_replace("@content;", $this->content, $template_html);

        $htmlp->process($template_html, false);

        return $htmlp->get_render();
    }
}