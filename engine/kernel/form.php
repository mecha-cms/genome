<?php

class Form extends HTML {

    public static $config = [
        'session' => 'form'
    ];

    // `<input>`
    public static function input($type = 'text', $name = null, $value = null, $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = self::protect($value);
        $attr_o['placeholder'] = $placeholder;
        $attr_o['type'] = $type;
        return self::unite('input', false, Anemon::extend($attr_o, $attr), $dent);
    }

    // `<button>`
    public static function button($text = "", $name = null, $value = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['value'] = $value;
        return self::unite('button', $text, Anemon::extend($attr_o, $attr), $dent);
    }

    // `<select>`
    public static function select($name = null, $option = [], $select = null, $attr = [], $dent = 0) {
        $o = "";
        $attr_o = [];
        $select = (string) $select;
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        Anemon::extend($attr_o, $attr);
        foreach ($option as $key => $value) {
            // option list group
            if (is_array($value)) {
                $s = [];
                if (strpos($key, '.') === 0) {
                    $s['disabled'] = true;
                    $key = substr($key, 1);
                }
                $s['label'] = $key;
                $o .= N . self::open('optgroup', $s, $dent + 1);
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
                    $o .= N . self::unite('option', $v, $s, $dent + 2);
                }
                $o .= N . self::close();
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
                $o .= N . self::unite('option', $value, $s, $dent + 1);
            }
        }
        return self::unite('select', $o . N . self::dent($dent), $attr_o, $dent);
    }

    // `<textarea>`
    public static function textarea($name = null, $text = "", $placeholder = null, $attr = [], $dent = 0) {
        $attr_o = [];
        if (strpos($name, '.') === 0) {
            $attr_o['disabled'] = true;
            $name = substr($name, 1);
        }
        $attr_o['name'] = $name;
        $attr_o['placeholder'] = $placeholder;
        return self::unite('textarea', self::x($text), Anemon::extend($attr_o, $attr), $dent);
    }

    // set state
    public static function set($k = null, $v = "") {
        if ($k === null) {
            $k = $_POST ?? $_GET ?? [];
        }
        if (!is_array($k)) {
            $k = [$k => $v];
        }
        $memo = Session::get(self::$config['session'], []);
        Session::set(self::$config['session'], Anemon::extend($memo, $k));
    }

    // get sate
    public static function get($k = null, $fail = "") {
        $memo = Session::get(self::$config['session']);
        self::reset($k);
        return $k !== null ? Anemon::get($memo, $k, $fail ?? "") : $memo;
    }

    // reset state
    public static function reset($k = null) {
        Session::reset(self::$config['session'] . ($k !== null ? '.' . $k : ""));
    }

}