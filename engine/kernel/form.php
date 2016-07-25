<?php

class Form extends Cell {

    // `<input>`
    public static function input($type = 'text', $name = null, $value = null, $placeholder = null, $attr = [], $indent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = self::protect($value);
        $attr_o['placeholder'] = $placeholder;
        $attr_o['type'] = $type;
        Group::extend($attr_o, $attr); // allow over-write with `$attr`
        return self::unit('input', false, $attr_o, $indent);
    }

    // `<button>`
    public static function button($text = "", $name = null, $type = null, $value = null, $attr = [], $indent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['type'] = $type;
        $attr_o['value'] = $value;
        Group::extend($attr_o, $attr); // allow over-write with `$attr`
        return self::unit('button', $text, $attr_o, $indent);
    }

    // `<select>`
    public static function select($name = null, $option = [], $select = null, $attr = [], $indent = 0) {
        $o = "";
        $attr_o = [];
        $select = (string) $select;
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        Group::extend($attr_o, $attr); // allow over-write with `$attr`
        foreach ($option as $key => $value) {
            // option list group
            if (is_array($value)) {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                }
                $s['label'] = $key;
                $o .= N . self::begin('optgroup', $s, $indent + 1);
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
                    $o .= N . self::unit('option', $v, $s, $indent + 2);
                }
                $o .= N . self::end();
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
                $o .= N . self::unit('option', $value, $s, $indent + 1);
            }
        }
        return self::unit('select', $o . N . ($indent ? str_repeat(I, $indent) : ""), $attr_o, $indent);
    }

    // `<textarea>`
    public static function textarea($name = null, $content = "", $placeholder = null, $attr = [], $indent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['placeholder'] = $placeholder;
        Group::extend($attr_o, $attr); // allow over-write with `$attr`
        return self::unit('textarea', self::protect($content), $attr_o, $indent);
    }

}