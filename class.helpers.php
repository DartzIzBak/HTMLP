<?php

class HTMLP_Helpers {
    public static function parse_str($string, $pattern = '/[^a-zA-Z0-9]/') {
        return preg_replace($pattern, '', $string);
    }
}