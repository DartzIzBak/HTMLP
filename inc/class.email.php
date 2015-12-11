<?php
use htmlpelements\BaseElement as BaseElement;

class EmailProcessor {
    private $config = array(
        'styleHandling' => false,
    );

    private $css_rules = array();

    public function addRules(array $rules) {
        $this->css_rules = array_merge($this->css_rules, $rules);
    }

    public function __construct() {

    }

    public function isEnabled($rule) {
        if(array_key_exists($rule, $this->config)) {
            return (bool) $this->config[$rule];
        }
        return false;
    }

    public function enableStyleHandling($bool = true) {
        $this->config['styleHandling'] = (bool) $bool;
    }

    public function enable($rule, $bool = true) {
        $this->config[$rule] = (bool) $bool;
    }

    public function processDocument(htmlpelements\DocumentHE $document, \htmlp\HTMLP $htmlp) {
        $css = $this->css_rules;

        $css_string = '';
        foreach($css as $value) {
            foreach($value as $key=>$rule) {
                $css_string .= $key.': '.$rule.';';
            }
        }

        foreach($css as $key=>$value) {
            $arr = $this->findInDocument($key, $document, $htmlp);
            foreach($arr as $element) {
                $element->add_attribute('style', $css_string);
            }
        }
    }

    private function findInDocument($search, htmlpelements\BaseElement $document, \htmlp\HTMLP $htmlp) {
        if(strpos($search, ',') > 0) {
            $css_rules = explode(',', $search);
        } else {
            $css_rules = array($search);
        }

        $base_matches = array();

        foreach($css_rules as $rule) {
            $rule_set = explode(' ', $rule);
            $base_matches = $this->getAllMatches($rule_set[0], $document, $htmlp);

            for($i = 1; $i < count($rule_set); $i++) {

                $search_children = true;

                if($rule_set[$i] == '>') {
                    $search_children = false;
                    $i++;
                }

                $matches = array();
                foreach($base_matches as $match) {
                    $matches = array_merge($this->getAllMatches($rule_set[$i], $match, $htmlp, $search_children), $matches);
                }
                $base_matches = $matches;
            }
        }

        return $base_matches;
    }

    private function getAllMatches($search, htmlpelements\BaseElement $searchIn, \htmlp\HTMLP $htmlp, $children = true) {
        $matches = array();
        $search = trim($search);

        if($searchIn->is($search)) {
            $matches[] = $searchIn;
        }

        if($children) {
            foreach ($searchIn->children as $child) {
                $matches = array_merge($this->getAllMatches($search, $child, $htmlp), $matches);
            }
        }

        return $matches;
    }
}