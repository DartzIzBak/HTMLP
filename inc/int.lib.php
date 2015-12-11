<?php

class Int {
    private $value = '';

    public function get( ) {
        return $this->value;
    }

    public function set( $number ) {
        $this->value = $number;
    }

    public function increment( $number = 1 ) {
        $this->value += $number;
    }

    public function decrement( $number = 1 ) {
        $this->value += $number;
    }

    public function smallerThan( $number ) {
        return $this->value < $number;
    }

    public function greaterThan( $number ) {
        return $this->value > $number;
    }
}