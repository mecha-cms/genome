<?php

class Form extends Cell {

    // `<input>`
    public function input($type = 'text', $name = null, $value = null, $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = $this->protect($value);
        $attr_o['placeholder'] = $placeholder;
        $attr_o['type'] = $type;
        Anemon::extend($attr_o, $attr); // allow over-write with `$attr`
        return $this->unit('input', false, $attr_o, $dent);
    }

    // `<button>`
    public function button($text = "", $name = null, $type = null, $value = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['type'] = $type;
        $attr_o['value'] = $value;
        Anemon::extend($attr_o, $attr); // allow over-write with `$attr`
        return $this->unit('button', $text, $attr_o, $dent);
    }

    // `<select>`
    public function select($name = null, $option = [], $select = null, $attr = [], $dent = 0) {
        $o = "";
        $attr_o = [];
        $select = (string) $select;
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        Anemon::extend($attr_o, $attr); // allow over-write with `$attr`
        foreach ($option as $key => $value) {
            // option list group
            if (is_array($value)) {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                }
                $s['label'] = $key;
                $o .= N . $this->begin('optgroup', $s, $dent + 1);
                foreach ($value as $k => $v) {
                    $s = [];
                    if (strpos($k, '.') === 0) {
                        $s['disabled'] = true;
                        $k = substr($k, 1);
                    }
                    $k = (string) $k;
                    if ($select === $k || $select === '.' . $k) {
                        $s['selected'] = true;
                    }
                    $s['value'] = $k;
                    $o .= N . $this->unit('option', $v, $s, $dent + 2);
                }
                $o .= N . $this->end();
            // option list
            } else {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                }
                $key = (string) $key;
                if ($select === $key || $select === '.' . $key) {
                    $s['selected'] = true;
                }
                $s['value'] = $key;
                $o .= N . $this->unit('option', $value, $s, $dent + 1);
            }
        }
        return $this->unit('select', $o . N . ($dent ? str_repeat(I, $dent) : ""), $attr_o, $dent);
    }

    // `<textarea>`
    public function textarea($name = null, $content = "", $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['placeholder'] = $placeholder;
        Anemon::extend($attr_o, $attr); // allow over-write with `$attr`
        return $this->unit('textarea', $this->protect($content), $attr_o, $dent);
    }

}