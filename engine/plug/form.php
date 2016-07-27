<?php

// `<input type="hidden">`
Form::add('hidden', function($n = null, $v = null, $a = [], $d = 0) {
    return Form::input('hidden', $n, $v, null, $a, $d);
});

// `<input type="file">`
Form::add('file', function($n = null, $a = [], $d = 0) {
    return Form::input('file', $n, null, null, $a, $d);
});

// `<input type="checkbox">`
Form::add('checkbox', function($n = null, $v = null, $c = false, $t = "", $a = [], $d = 0) {
    $aa = ['checked' => $c ? true : null];
    $d = Cell::dent($d);
    if($v === true) $v = 'true';
    Anemon::extend($aa, $a);
    $t = $t ? '&nbsp;<span>' . t($t, WISE_CELL_I) . '</span>' : "";
    return $d . '<label>' . Form::input('checkbox', $n, $v, null, $aa) . $t . '</label>';
});

// `<input type="radio">`
Form::add('radio', function($n = null, $o = [], $s = null, $a = [], $d = 0) {
    $output = [];
    $d = Cell::dent($d);
    $s = (string) $s;
    foreach ($o as $k => $v) {
        $aa = ['disabled' => null];
        if(strpos($k, '.') === 0) {
            $aa['disabled'] = true;
            $k = substr($k, 1);
        }
        $k = (string) $k;
        $aa['checked'] = $s === $k || $s === '.' . $k ? true : null;
        Anemon::extend($aa, $a);
        $v = $v ? '&nbsp;<span>' . t($v, WISE_CELL_I) . '</span>' : "";
        $output[] = $d . '<label>' . Form::input('radio', $n, $k, null, $aa) . $v . '</label>';
    }
    return implode(' ', $output);
});

// `<input type="(color|date|email|number|password|range|search|tel|text|url)">`
foreach(['color', 'date', 'email', 'number', 'password', 'range', 'search', 'tel', 'text', 'url'] as $unit) {
    Form::add($unit, function(...$lot) use($unit) {
        return Form::input($unit, ...$lot);
    });
}