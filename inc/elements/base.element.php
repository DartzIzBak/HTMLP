<?php

namespace htmlpelements;

use \HTMLP\Element as Element;
use htmlp\HTMLP as HTMLP;

class BaseElement extends Element {

    public static $allowed_elem_characters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "@", "(", ")", "-", "_", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "@", "(", ")", "-", "_", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");

    public $parent;
    private $htmlp;

    public function __construct( $htmlp, $type = '' ) {
        $this->set_type( \HTMLP_Helpers::parse_str($type) );
        $this->set_format( '<%1$s %2$s>%3$s</%1$s>' );
        $this->htmlp = $htmlp;
    }

    public function get_parent_tree() {
        $tree = array();

        $last = $this;
        while($last->parent != null) {
            $tree[] = $last = $last->parent;
        }

        return $tree;
    }

    public function is($rule) {
        $name  = self::get_name_from_name($rule);
        $arr  = self::get_attributes_from_name($rule, $this->htmlp);

        if($name && $this->get_the_type() != $name) {
            return false;
        } elseif(count($arr) > 0) {
            foreach($arr as $key=>$value) {
                if(!array_key_exists($key, $this->attributes)) {
                    return false;
                } else {
                    foreach($value as $attr) {
                        if(!in_array($attr, $this->attributes[$key])) {
                            return false;
                        }
                    }
                }
            }
            return true;
        } elseif($name == $this->get_the_type() && count($arr) <= 0) {
            return true;
        }
        //var_dump($arr);
        return false;
    }

    private static function get_name_from_name($elem_name)
    {
        $elem_name = trim($elem_name);
        $i = 0;
        $elem_length = strlen($elem_name);

        while ($i < $elem_length) {
            if (in_array($elem_name[$i], BaseElement::$allowed_elem_characters)) {
                $i++;
            } else {
                break;
            }
        }

        return substr($elem_name, 0, $i);
    }

    private static function get_attributes_from_name($elem_name, HTMLP $htmlp)
    {
        $i = 0;
        $elem_length = strlen($elem_name);

        while ($i < $elem_length) {
            if (in_array($elem_name[$i], BaseElement::$allowed_elem_characters)) {
                $i++;
            } else {
                break;
            }
        }

        $attr = substr($elem_name, $i, $elem_length);

        $attrs = array();
        $key = '';

        if ($attr != '') {

            $index = 0;
            $elem_length = strlen($attr);
            $string = '';

            $is_custom_attr = false;
            $is_custom_key = true;

            $custom_key = '';
            $custom_value = '';

            while ($index < $elem_length) {

                if ($is_custom_attr) {

                    switch($attr[$index]) {
                        case "]":
                            /* Set the system back into key mode, not value. */
                            $is_custom_attr = false;
                            $is_custom_key = true;

                            /* If the key is not empty, and the value is, it still needs to appear. */
                            if ($custom_key != '') {
                                if (!array_key_exists($custom_key, $attrs)) {
                                    $attrs[$custom_key] = array();
                                }
                                if($custom_value[0] == '"') {
                                    $custom_value = substr($custom_value, 1);
                                }
                                if($custom_value[strlen($custom_value) - 1] == '"') {
                                    $custom_value = substr($custom_value, 0, strlen($custom_value) - 1);
                                }
                                $attrs[$custom_key][] = $custom_value;
                                $custom_key = '';
                                $custom_value = '';
                            }
                            break;

                        case "=":
                            $is_custom_key = false;
                            break;

                        default:
                            if($is_custom_key) {
                                $custom_key .= $attr[$index];
                            } else {
                                $custom_value .= $attr[$index];
                            }
                            break;
                    }


                } elseif ($attr[$index] == "[") {
                    $is_custom_attr = true;
                } elseif ($attr[$index] == ".") {
                    if ($key != '') {
                        if (!array_key_exists($key, $attrs)) {
                            $attrs[$key] = array();
                        }
                        if($string[0] == '"') {
                            $string = substr($string, 1);
                        }
                        if($string[strlen($string - 1)] == '"') {
                            $string = substr($string, 0, $string - 2);
                        }
                        $attrs[$key][] = $string;
                        $string = '';
                    }
                    $key = 'class';
                } elseif ($attr[$index] == '#') {
                    if ($key != '') {
                        if (!array_key_exists($key, $attrs)) {
                            $attrs[$key] = array();
                        }
                        if($string[0] == '"') {
                            $string = substr($string, 1);
                        }
                        if($string[strlen($string - 1)] == '"') {
                            $string = substr($string, 0, $string - 1);
                        }
                        $attrs[$key][] = $string;
                        $string = '';
                    }
                    $key = 'id';
                } elseif ($attr[$index] == ' ') {
                    if ($key != '') {
                        if (!array_key_exists($key, $attrs)) {
                            $attrs[$key] = array();
                        }
                        if($string[0] == '"') {
                            $string = substr($string, 1);
                        }
                        if($string[strlen($string - 1)] == '"') {
                            $string = substr($string, 0, $string - 1);
                        }
                        $attrs[$key][] = $string;
                        $string = '';
                    }
                    $key = '';
                } else {
                    $string .= $attr[$index];
                }

                $index++;
            }

            if ($key != '') {
                if (!array_key_exists($key, $attrs)) {
                    $attrs[$key] = array();
                }
                $attrs[$key][] = $string;
            }

            if ($custom_key != '') {
                if (!array_key_exists($key, $attrs)) {
                    $attrs[$key] = array();
                }
                if($custom_value[0] == '"') {
                    $custom_value = substr($custom_value, 1);
                }
                if($custom_value[strlen($custom_value - 1)] == '"') {
                    $custom_value = substr($custom_value, 0, $custom_value - 1);
                }
                $attrs[$key][] = $custom_value;
            }
        }

        return $attrs;
    }

    public function add_child_element( Element $element ) {
        $this->children[] = $element;
        $element->parent = $this;
    }

    public function append_content( $content, $is_new_line = false  ) {
        if( ! ( $this instanceof EmptyHE ) ) {
            if( $is_new_line || empty( $this->last_paragraph ) ) {
                $this->last_paragraph = new EmptyHE($this->htmlp);
                $this->add_child_element( $this->last_paragraph );
            }
            $this->last_paragraph->append_content( $content );
        } else {
            $this->content .= $content;
        }
    }

    public function set_format( $format ) {
        $this->render_format = $format;
    }

    public function add_attribute( $key, $value ) {
        if(!array_key_exists($key, $this->attributes)) {
            $this->attributes[$key] = array($value);
        } else {
            $this->attributes[$key][] = $value;
        }
    }

    public function set_attributes( $attributes ) {
        $this->attributes = $attributes;
    }

    public function set_content( $content ) {
        $this->content = $content;
    }

    public function set_type( $type ) {
        $this->type = $type;
    }

    public function get_children() {
        return $this->children;
    }

    public function get_the_type() {
        return $this->type;
    }

    public function get_the_attributes() {
        $temp_attr = array();
        foreach( $this->attributes as $key => $value ) {
            if( empty( $value ) || !$value ) {
                $temp_attr[] = $key;
            } else {
                $temp_attr[] = $key . '="' . implode( ' ', $value ) . '"';
            }
        }

        return implode( ' ', $temp_attr );
    }

    public function get_the_content() {
        $renders = '';
        foreach($this->children as $child) {
            $renders .= (string)$child;
        }
        return $renders;
    }

    public function get_the_format() {
        return $this->render_format;
    }

    public function __toString() {
        return sprintf( $this->get_the_format(), $this->get_the_type(), $this->get_the_attributes(), $this->get_the_content() );
    }
}