<?php

namespace htmlpelements;

class BrokenHE extends BaseElement {
    public function __construct( $htmlp, $type = '' ) {
        parent::__construct();
        $this->set_format( '<%1$s %2$s>' );
    }
}