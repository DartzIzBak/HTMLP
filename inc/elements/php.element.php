<?php

namespace htmlpelements;

/*
 * For IO only. Future file based processing.
 */
class PHPIOHE extends BaseElement {
    public function render_format() {
        return '<?%1$s %3$s ?>';
    }
    public function get_attributes() {
        $temp_attr = array();
        foreach($this->attributes as $key=>$value) {
            $temp_attr[] = $key;
        }

        return implode(' ', $temp_attr);
    }
}

class PHPHE extends BaseElement {
    public function get_render() {
        echo 'Executing... ' . $this->content;
        if(strlen($this->content) > 0) {
            return eval($this->content);
        }
        return '';
    }
    public function append_content($content, $new_line = false) {
        $this->content = $content;
    }
}