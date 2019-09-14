<?php

class HTML extends SGML {

    public $strict = false;

    public function __construct($in = []) {
        parent::__construct($in);
        if (!empty($this->lot[2])) {
            foreach ($this->lot[2] as &$v) {
                if (is_string($v)) {
                    $v = htmlspecialchars_decode($v);
                }
            }
        }
    }

    public function __toString() {
        if (!empty($this->lot[2])) {
            $c = $this->c;
            foreach ($this->lot[2] as $k => &$v) {
                if ($v === true) {
                    continue;
                }
                if (!isset($v) || $v === false) {
                    unset($this->lot[2][$k]);
                    continue;
                }
                $v = htmlspecialchars(is_array($v) ? json_encode($v) : s($v));
            }
            unset($v);
        }
        return parent::__toString();
    }

}