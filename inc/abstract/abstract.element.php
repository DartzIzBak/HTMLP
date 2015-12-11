<?php

namespace HTMLP;

abstract class Element {
    public $type = '',
           $render_format = '',
           $content = '',
           $attributes = array(),
           $children = array(),
           $last_paragraph = null;

    public abstract function add_child_element( Element $element );

    public abstract function append_content( $content, $is_new_line );

    public abstract function set_format( $format );
    public abstract function set_attributes( $attributes );
    public abstract function set_content( $content );
    public abstract function set_type( $type );

    public abstract function get_children();
    public abstract function get_the_type();
    public abstract function get_the_attributes();
    public abstract function get_the_content();
    public abstract function get_the_format();
}