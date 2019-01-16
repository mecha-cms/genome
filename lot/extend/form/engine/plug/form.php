<?php

foreach (['reset', 'submit'] as $kin) {
    Form::_($kin, function(string $name = null, $value = null, string $text = null, array $attr = [], $dent = 0) use($kin) {
        $attr['type'] = $kin;
        return static::button($name, $value, $text, $attr, $dent);
    });
}

foreach ([
    'hidden' => function(string $name = null, $value = null, array $attr = [], $dent = 0) {
        // Do not cache any request data of hidden form element(s)
        Session::reset(static::session . '.' . static::key($name));
        return static::input($name, 'hidden', $value, null, $attr, $dent);
    },
    'file' => function(string $name = null, array $attr = [], $dent = 0) {
        return static::input($name, 'file', null, null, $attr, $dent);
    },
    'checkbox' => function(string $name = null, $value = null, $check = false, string $text = null, array $attr = [], $dent = 0) {
        $a = ['checked' => $check ? true : null];
        if ($value === true) {
            $value = 'true';
        }
        $text = $text ? '&#x0020;' . HTML::span($text) : "";
        return HTML::dent($dent) . HTML::label(Form::input($name, 'checkbox', $value, null, extend($a, $attr)) . $text);
    },
    'color' => function(string $name = null, $value = null, array $attr = [], $dent = 0) {
        // TODO: color converter for `$value`
        return static::input($name, 'color', $value, null, $attr, $dent);
    },
    'date' => function(string $name = null, $value = null, string $placeholder = null, array $attr = [], $dent = 0) {
        // <https://www.w3.org/TR/2011/WD-html-markup-20110405/input.date.html>
        $value = (new Date($value))->format('Y-m-d');
        return static::input($name, 'date', $value, $placeholder, $attr, $dent);
    },
    'radio' => function(string $name = null, array $options = [], $select = null, array $attr = [], $dent = 0) {
        $out = [];
        $select = s($select);
        $id = $attr['id'] ?? null;
        unset($attr['id']);
        foreach ($options as $k => $v) {
            $a = ['disabled' => null];
            if (strpos($k, '.') === 0) {
                $a['disabled'] = true;
                $k = substr($k, 1);
            }
            $k = (string) $k;
            $a['checked'] = $select === $k || $select === '.' . $k ? true : null;
            $v = $v ? '&#x0020;' . HTML::span($v) : "";
            if ($id) {
                $a['id'] = $id . ':' . dechex(crc32($k));
            }
            $out[] = HTML::dent($dent) . HTML::label(Form::input($name, 'radio', $k, null, extend($a, $attr)) . $v);
        }
        return implode(HTML::unite('br', false), $out);
    },
    'range' => function(string $name = null, $range = [], array $attr = [], $dent = 0) {
        if (is_array($range)) {
            $range = extend([0, 0, 1], $range);
            if (!array_key_exists('min', $attr)) {
                $attr['min'] = $range[0];
            }
            if (!array_key_exists('max', $attr)) {
                $attr['max'] = $range[2];
            }
        }
        return static::input($name, 'range', is_array($range) ? $range[1] : $range, null, $attr, $dent);
    }
] as $k => $v) {
    Form::_($k, $v);
}

foreach (['email', 'number', 'password', 'search', 'tel', 'text', 'url'] as $kin) {
    Form::_($kin, function(string $name = null, $value = null, string $placeholder = null, array $attr = [], $dent = 0) use($kin) {
        return static::input($name, $kin, $value, $placeholder, $attr, $dent);
    });
}

// Alias(es)
foreach ([
    'blob' => 'file',
    'check' => 'checkbox',
    'pass' => 'password',
    'toggle' => 'checkbox'
] as $k => $v) {
    Form::_($k, Form::_($v));
}