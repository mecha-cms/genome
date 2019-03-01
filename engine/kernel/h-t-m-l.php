<?php

class HTML extends SGML {

    public $strict = false;

    public function __construct($in = []) {
        parent::__construct($in);
        if (!empty($this->lot[2])) {
            foreach ($this->lot[2] as &$v) {
                if (is_string($v)) {
                    $v = e(htmlspecialchars_decode($v));
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

    public function offsetGet($i) {
        // Shortcut for `$baz = $foo[2]['bar']` with `$baz = $foo['bar']`
        if (isset($i) && !is_numeric($i)) {
            return $this->lot[2][$i] ?? null;
        }
        return parent::offsetGet($i);
    }

    public function offsetSet($i, $value) {
        // Shortcut for `$foo[2]['bar'] = 'baz'` with `$foo['bar'] = 'baz'`
        if (isset($i) && !is_numeric($i)) {
            $this->lot[2][$i] = $value;
        } else {
            parent::offsetSet($i, $value);
        }
    }

    public function offsetUnset($i) {
        // Shortcut for `unset($foo[2]['bar'])` with `unset($foo['bar'])`
        if (isset($i) && !is_numeric($i)) {
            unset($this->lot[2][$i]);
        } else {
            parent::offsetUnset($i);
        }
    }

}