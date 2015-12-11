<?php

namespace htmlpelements;

class SelfClosingHE extends BaseElement {
    public function render_format() {
        return '<%1$s %2$s />';
    }
}