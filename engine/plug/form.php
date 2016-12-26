<?php

// `<input type="hidden">`
Form::plug('hidden', function($name = null, $value = null, $attr = [], $dent = 0) {
    return Form::input('hidden', $name, $value, null, $attr, $dent);
});

// `<input type="file">`
Form::plug('file', function($name = null, $attr = [], $dent = 0) {
    return Form::input('file', $name, null, null, $attr, $dent);
});

// `<input type="checkbox">`
Form::plug('checkbox', function($name = null, $value = null, $check = false, $text = "", $attr = [], $dent = 0) {
    $attr_o = ['checked' => $check ? true : null];
    if ($value === true) {
        $value = 'true';
    }
    $text = $text ? '&nbsp;<span>' . To::text($text, explode(',', HTML_WISE_I)) . '</span>' : "";
    return Form::dent($dent) . '<label>' . Form::input($name, 'checkbox', $value, null, Anemon::extend($attr_o, $attr)) . $text . '</label>';
});

// `<input type="radio">`
Form::plug('radio', function($name = null, $options = [], $select = null, $attr = [], $dent = 0) {
    $output = [];
    $select = (string) $select;
    foreach ($options as $k => $v) {
        $attr_o = ['disabled' => null];
        if (strpos($k, '.') === 0) {
            $attr_o['disabled'] = true;
            $k = substr($k, 1);
        }
        $k = (string) $k;
        $attr_o['checked'] = $select === $key || $select === '.' . $key ? true : null;
        $v = $v ? '&nbsp;<span>' . To::text($value, explode(',', HTML_WISE_I)) . '</span>' : "";
        $output[] = Form::dent($dent) . '<label>' . Form::input($name, 'radio', $k, null, Anemon::extend($attr_o, $attr)) . $v . '</label>';
    }
    return implode(HTML::br(), $output);
});

// `<input type="(color|date|email|number|password|range|search|tel|text|url)">`
foreach (['color', 'date', 'email', 'number', 'password', 'range', 'search', 'tel', 'text', 'url'] as $unit) {
    Form::plug($unit, function($name = null, $value = null, $placeholder = null, $attr = [], $dent = 0) use($unit) {
        return Form::input($name, $unit, $value, $placeholder, $attr, $dent);
    });
}