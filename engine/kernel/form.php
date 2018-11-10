<?php

class Form extends HTML {

    // `<button>`
    public static function button($name = null, $value = null, $text = "", $attr = [], $dent = 0) {
        if (!array_key_exists('type', $attr)) {
            $attr['type'] = 'button';
        }
        $a = ['value' => $value];
        self::name($name, $a);
        unset($a['readonly'], $a['required']);
        return self::unite('button', $text, extend($a, $attr), $dent);
    }

    // `<input>`
    public static function input($name = null, $type = 'text', $value = null, $placeholder = null, $attr = [], $dent = 0) {
        $a = [
            'placeholder' => self::v($placeholder),
            'type' => $type
        ];
        self::name($name, $a);
        $a['value'] = HTTP::restore(self::key($name), $value);
        return self::unite('input', false, extend($a, $attr), $dent);
    }

    // `<select>`
    public static function select($name = null, $option = [], $select = null, $attr = [], $dent = 0) {
        $o = "";
        $a = [];
        self::name($name, $a);
        unset($a['required']);
        $select = (string) HTTP::restore(self::key($name), $select);
        $a = extend($a, $attr);
        foreach ($option as $key => $value) {
            $tag = new static;
            // option list group
            if (is_array($value)) {
                $s = [];
                $key = (string) $key;
                self::name($key, $s);
                $s['label'] = trim(strip_tags($key));
                $o .= N . $tag->begin('optgroup', $s, $dent + 1);
                foreach ($value as $k => $v) {
                    $s = [];
                    $k = (string) $k;
                    self::name($k, $s);
                    unset($s['readonly'], $s['required']);
                    if ($select === $k) {
                        $s['selected'] = true;
                    }
                    $s['value'] = $k;
                    $o .= N . self::unite('option', trim(strip_tags($v)), $s, $dent + 2);
                }
                $o .= N . $tag->end();
            // option list
            } else {
                $s = [];
                $key = (string) $key;
                self::name($key, $s);
                unset($s['readonly'], $s['required']);
                if ($select === $key) {
                    $s['selected'] = true;
                }
                $s['value'] = $key;
                $o .= N . self::unite('option', trim(strip_tags($value)), $s, $dent + 1);
            }
        }
        return self::unite('select', $o . N . self::dent($dent), $a, $dent);
    }

    // `<textarea>`
    public static function textarea($name = null, $value = "", $placeholder = null, $attr = [], $dent = 0) {
        $a = [];
        self::name($name, $a);
        // <https://www.w3.org/TR/html5/forms.html#the-placeholder-attribute>
        // The `placeholder` attribute represents a short hint (a word or short phrase) intended
        // to aid the user with data entry when the control has no value. A hint could be a sample
        // value or a brief description of the expected format. The attribute, if specified, must
        // have a value that contains no “LF” (U+000A) or “CR” (U+000D) character(s).
        if (isset($placeholder)) {
            $placeholder = substr(self::v(explode("\n", n($placeholder), 2)[0]), 0, 300); // TODO
        }
        $a['placeholder'] = $placeholder;
        $value = HTTP::restore(self::key($name), $value);
        return self::unite('textarea', self::v(htmlentities($value)), extend($a, $attr), $dent);
    }

    public static function key($key) {
        // Replace `foo[bar][baz]` to `foo.bar.baz`
        return str_replace(['.', '[', ']', X], [X, '.', "", "\\."], $key);
    }

    private static function name(&$s, &$a) {
        if ($s && strpos('.!*', $s[0]) !== false) {
            $a[alt($s[0], [
                '.' => 'disabled',
                '!' => 'readonly',
                '*' => 'required'
            ])] = true;
            $s = substr($s, 1);
        } else if (strlen($s) >= 2 && $s[0] === '\\' && strpos('.!*', $s[1]) !== false) { // escaped
            $s = substr($s, 1);
        }
        $a['name'] = $s;
    }

    private static function v($value) {
        // Only un-escape Unicode hex representation
        if ($value && strpos($value . "", '&#x') !== false) {
            return preg_replace_callback('#&\#x[a-f\d]+;#i', function($m) {
                return html_entity_decode($m[0]);
            }, $value);
        }
        return $value;
    }

}