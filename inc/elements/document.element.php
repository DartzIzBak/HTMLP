<?php

namespace htmlpelements;

class DocumentHE extends BaseElement {
    public function __construct( $htmlp, $type = '' ) {
        parent::__construct($htmlp);
        $this->set_format( '%3$s' );
    }
}