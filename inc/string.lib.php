<?php

class StringHelpers {
    private $value = '';

    public function set( $string ) {
        $this->value = $string;
    }

    public function startsWith( $string ) {
        return strpos( $this->value, $string ) == 0;
    }

    public function endsWith( $string ) {
        return strpos( $this->value, $string ) == ( strlen( $this->value ) - strlen( $string ) );
    }

    public function contains( $string ) {
        return strpos( $this->value, $string ) > -1;
    }

    public function charAt( $index ) {
        if($index >= strlen($this->value)) return false;
        return $this->value[ $index ];
    }

    public function __toString() {
        return $this->value;
    }
}