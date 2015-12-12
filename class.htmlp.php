<?php
/*
 * @Version: 4.3.2
 */
namespace htmlp;

# String class
require_once('inc/string.lib.php');

require_once('class.helpers.php');

require_once('inc/abstract/abstract.element.php');
require_once('inc/elements/base.element.php');
require_once('inc/elements/document.element.php');
require_once('inc/elements/self-closing.element.php');
require_once('inc/elements/meta.element.php');
require_once('inc/elements/php.element.php');
require_once('inc/elements/comment.element.php');
require_once('inc/elements/empty.element.php');
require_once('inc/elements/template.element.php');
require_once('inc/class.email.php');
require_once('inc/class.css.php');

use htmlpelements;
use htmlpelements\BaseElement as BaseElement;
use htmlpelements\DocumentHE as DocumentHE;
use htmlpelements\BrokenHE as BrokenHE;
use htmlpelements\EmptyHE as EmptyHE;
use htmlpelements\SelfClosingHE as SelfClosingHE;
use htmlpelements\CommentHE as CommentHE;
use htmlpelements\TemplateHE as TemplateHE;
use \EmailProcessor as EmailProcessor;
use \CSSParser as CSSParser;

global $htmlp_templates;
$htmlp_templates = array();

global $emailprocessor;
$emailprocessor = new EmailProcessor();

class HTMLP
{

    /**
     * This contains the whole HTMLP document.
     *
     * @var Element
     */
    private $document = null;
    /**
     * List of self closing tags.
     *
     * @var array
     */
    private $self_closing = array(
        'img', 'link', 'meta', 'br', 'hr', 'input', 'area', 'base', 'basefont', 'param', 'embed', 'keygen', 'menuitem', 'source', 'wbr', 'track'
    );


    /**
     * E-mail friendly elements.
     *
     * @var array
     */
    private $email_safe = array(
        'img', 'link', 'br', 'hr', 'div', 'table', 'tr', 'td'
    );

    /**
     * List of allowed characters for element names.
     *
     * @var array
     */
    private $allowed_elem_characters = array(
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
        "@", "(", ")", "-", "_", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");

    private $config = array();

    /**
     * Prints out the HTML output of the processed HTMLP document.
     */
    public function render()
    {
        echo $this->get_render();
    }

    /**
     * Returns the HTML output of the processed HTMLP document.
     *
     * @return string
     */
    public function get_render()
    {
        return (string)$this->document;
    }

    /**
     * Test whether the element given is self-closing
     *
     * @param string $type Element Name
     *
     * @return bool
     */
    public function is_self_closing_element($type)
    {
        return in_array($type, $this->self_closing);
    }

    /**
     * Process the given content.
     *
     * @param string $file HTMLP Document content
     */
    public function process($file, $is_file = true)
    {

        # If the file does not exist, throw an error.
        if ($is_file) {
            $content = file_get_contents($file);
        } else {
            $content = $file;
        }

        # Load the file.

        $this->document = new \htmlpelements\DocumentHE($this);
        $file = implode('', explode("\n", $content));

        $index = 0;
        $max_index = strlen($file);

        while ($index < $max_index) {
            $this->nextElement($file, $index, $this->document);
        }

        global $emailprocessor;
        if($emailprocessor->isEnabled('emailProcessing')) {
            $emailprocessor->processDocument($this->document, $this);
        }
    }

    /**
     * The name is a little mis-leading, this will be changed in the future. It gets the name
     * of the elements from the nice piece of data we send to it.
     *
     * @param string $elem_name Element name, this also contains attributes. Example: div.my-class
     *
     * @return string
     */
    public function get_name_from_name($elem_name)
    {
        $elem_name = trim($elem_name);
        $i = 0;
        $elem_length = strlen($elem_name);

        while ($i < $elem_length) {
            if (in_array($elem_name[$i], $this->allowed_elem_characters)) {
                $i++;
            } else {
                break;
            }
        }

        return substr($elem_name, 0, $i);
    }

    /**
     * Get the attributed from the nice piece of data that we send to it.
     *
     * @param string $elem_name Element name, this also contains attributes - which we return. Example: div.my-class
     * @return array
     */
    public function get_attributes_from_name($elem_name)
    {

        $i = 0;
        $elem_length = strlen($elem_name);

        while ($i < $elem_length) {
            if (in_array($elem_name[$i], $this->allowed_elem_characters)) {
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
                                    $custom_value = substr($custom_value, 1, strlen($custom_value));
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
                            if($is_custom_key) {
                                $is_custom_key = false;
                            } else {
                                $custom_value .= $attr[$index];
                            }
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
                        $attrs[$key][] = $string;
                        $string = '';
                    }
                    $string = '';
                    $key = 'class';
                } elseif ($attr[$index] == '#') {
                    if ($key != '') {
                        if (!array_key_exists($key, $attrs)) {
                            $attrs[$key] = array();
                        }

                        $attrs[$key][] = $string;
                    }
                    $string = '';
                    $key = 'id';
                } elseif ($attr[$index] == ' ') {
                    if ($key != '') {
                        if (!array_key_exists($key, $attrs)) {
                            $attrs[$key] = array();
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
                $attrs[$key][] = $custom_value;
            }
        }

        return $attrs;
    }

    /**
     * Get the next HTMLElement
     *
     * @param string $file The entire file content.
     * @param int $index The current position in our walker.
     * @param BaseElement $parent The parent of the next element.
     *
     * @return BaseElement
     */
    public function nextElement($file, &$index, $parent)
    {

        $string = new \StringHelpers;
        $string->set($file);

        $elements_name = '';
        $elements_text = '';

        $comment = false;
        $script_closed = true;
        $script_depth = 0;

        $is_alt_script = $is_inclusion = false;

        $thisElement = new BaseElement($this);

        $first_character = true;

        $single_line_element = false;

        while (false !== ($char = $string->charAt($index))) {
            if($char == '{') {
                break;
            } elseif($char == ';') {
                $single_line_element = true;
                break;
            }
            if($first_character && $char == " ") {
                $index++;
                continue;
            } elseif($first_character) {
                $first_character = false;
            }
            $elements_name .= $char;
            $index++;
        }

        $index++;

        if ($elements_name && $elements_name[strlen($elements_name) - 1] == ' ') {
            $elements_name = substr($elements_name, 0, strlen($elements_name) - 1);
        }


        $elements_true_name = $this->get_name_from_name($elements_name);


        $thisElement->set_type($elements_true_name);
        $thisElement->set_attributes($this->get_attributes_from_name($elements_name));

        $is_attribute = false;
        $is_style = false;

        switch ($elements_true_name) {
            case 'style':
                $is_style = true;
                $is_alt_script = true;
                break;

            case 'script':
                $is_alt_script = true;
                break;

            case 'pre':
                $is_alt_script = true;
                break;

            case 'import':
                $is_inclusion = true;
                $thisElement = new EmptyHE($this);
                break;
        }

        if(strlen($elements_true_name) <= 0) {
            return $this->document;
        }

        if($elements_true_name[0] == '@') {
            $is_alt_script = true;
            $is_attribute = true;
        }

        while (($file[$index] != '}' || !$script_closed || $comment) && !$single_line_element) {

            if (!$is_alt_script) {

                if (($file[$index] == " " && !$comment) || $file[$index] == "\t" || $file[$index] == "\r") {

                    # Skip any empty characters

                } elseif (($file[$index] == '"' && $file[$index - 1] != "\\") || $comment) {

                    if (!$comment && $file[$index] == '"') {

                        $comment = true;
                        $index++;

                    }

                    if ($comment && $file[$index] == '"' && $file[$index - 1] != "\\") {

                        $comment = false;
                        $index++;

                        if ($is_inclusion) {

                            $htmlp = new \htmlp\HTMLP();
                            if(file_exists($elements_text . '.template')) {
                                $htmlp->process($elements_text . '.template');
                                $thisElement->append_content($htmlp->get_render(), true);
                            } else {
                                $thisElement->append_content('File does not exist: '.$elements_text . '.template', true);
                            }

                        } else {
                            $thisElement->append_content($elements_text, true);
                        }

                        $elements_text = '';
                        continue;
                    }
                    if ($file[$index] == '\\' && $file[$index - 1] != '\\') {
                        $index++;
                        continue;
                    }

                    $elements_text .= $file[$index];
                } else {
                    $this->nextElement($file, $index, $thisElement);
                }
            } else {
                $elements_text .= $file[$index];

                if ($file[$index] == '{') {
                    $script_depth++;
                    $script_closed = false;
                } elseif ($file[$index] == '}') {
                    $script_depth--;

                    if ($script_depth == 0) {
                        $script_closed = true;
                    }
                }
            }
            $index++;
        }

        if($single_line_element) {

        } elseif($is_attribute) {
            $attribute_name = substr($elements_true_name, 1);

            $line = preg_replace('/[\t\n]/', "", $elements_text);
            $script = trim($line);

            switch($attribute_name) {
                case "style":
                    $parent->add_attribute($attribute_name, $script);
                    break;
                case "include":
                    $template_name = explode(' ', $elements_name);
                    $template_name = $template_name[1];

                    $element = new TemplateHE($this);
                    $element->template = $template_name;
                    $element->set_content($script);
                    $parent->add_child_element($element);
                    break;
                case "template":
                    global $htmlp_templates;
                    $template_name = explode(' ', $elements_name);
                    $template_name = $template_name[1];

                    $htmlp_templates[$template_name] = $script;
                    break;
                case "config":
                    $rules = CSSParser::Parse($script, true);
                    $this->config = array_merge($this->config, $rules);

                    $this->reloadConfig();
                    break;
                default:
                    //$parent->add_attribute($attribute_name, $script);
                    break;
            }
        } elseif ($is_alt_script) {
            global $emailprocessor;
            if($is_style && $emailprocessor->isEnabled('styleHandling')) {
                $rules = CSSParser::Parse($elements_text, false);
                $emailprocessor->addRules($rules);
            } else {
                if($elements_true_name != 'pre') {
                    $line = str_replace("\t", "", $elements_text);
                    $script = substr($line, 1 + (($line[1] == ' ') ? 1 : 0), strlen($line));

                    $thisElement->append_content($script);

                    $parent->add_child_element($thisElement);
                } else {
                    $script = $elements_text;
                    $thisElement->append_content($script);
                    $parent->add_child_element($thisElement);
                }
            }
        } else {

            $parent->add_child_element($thisElement);
        }

        $index++;

        return $this->document;
    }

    public function reloadConfig() {
        foreach($this->config as $key=>$value) {
            //switch($key) {
                /* Enable processing methods for e-mails (CSS) */
            //    case "emailProcessing":
                /* Enable inline-style handling, requires emailProcessing */
            //    case "inlineStyling":
                /*
                 * Enable strict mode, this will force the application
                 * to stop working if an error is detected (email mode only)
                 */
            //    case "strict":
                    global $emailprocessor;
                    $emailprocessor->enable($key, $value == 'true' ? true : false);
            //        break;
            //}
        }
    }
}