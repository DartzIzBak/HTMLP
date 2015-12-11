<?php

namespace htmlpelements;

class CommentHE extends BaseElement {
    public function render_format() {
        return '<!-- %3$s -->';
    }
}