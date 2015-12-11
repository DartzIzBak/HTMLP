<?php

class CSSParser {
    public static function Parse($string, $inline = false) {
        if(!$string) {
            return new Exception("Invalid CSS given.");
        }

        $rules = array();
        $limit = strlen($string);

        $temp_key = "";
        $temp_value = "";

        $is_value = false;

        if(!$inline) {
            for ($i = 0; $i < $limit; $i++) {
                switch ($string[$i]) {
                    case "{":
                        $is_value = true;
                        break;

                    case "}":
                        $is_value = false;
                        $i++;

                        $rules[trim($temp_key)] = self::ParseRules($temp_value);

                        $temp_key = "";
                        $temp_value = "";
                        break;

                    default:
                        if ($is_value) {
                            $temp_value .= $string[$i];
                        } else {
                            $temp_key .= $string[$i];
                        }
                        break;
                }
            }
        } else {
            $index = 0;
            $rules = self::ParseRules($string);
            return $rules;
        }

        return $rules;
    }

    private static function ParseRules($string) {
        $index = 0;

        $rules = array();
        $maxlength = strlen($string);

        $temp_key = "";
        $temp_value = "";

        $is_value = false;

        while($index < $maxlength) {
            switch($string[$index]) {
                case ":":
                    $is_value = true;
                    break;

                case ";":
                    $is_value = false;
                    $rules[trim($temp_key)] = trim($temp_value);

                    $temp_key = "";
                    $temp_value = "";
                    break;

                default:
                    if($is_value) {
                        $temp_value .= $string[$index];
                    } else {
                        $temp_key .= $string[$index];
                    }
                    break;
            }
            $index++;
        }

        return $rules;
    }
}