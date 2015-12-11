<?php

namespace htmlpelements;

class EmptyHE extends BaseElement {
    public function __construct( $htmlp, $type = '' ) {
        parent::__construct($htmlp);
        $this->set_format( '%1$s' );
    }

    public function get_the_content() {
        return $this->content;
    }

    public function __toString() {
        return sprintf( $this->get_the_format(), $this->get_the_content() );
    }
}