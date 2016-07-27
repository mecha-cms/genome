<?php

// `<meta>`
Cell::add('meta', function($a = [], $d = 0) {
    return Cell::unit('meta', false, $a, $d);
});

// `<link>`
Cell::add('link', function($href = null, $rel = null, $type = null, $a = [], $d = 0) {
    $a['href'] = URL::full($href);
    $a['rel'] = $rel;
    $a['type'] = $type;
    return Cell::unit('link', false, $a, $d);
});

// `<script>`
Cell::add('script', function($a = [], $c = "", $d = 0) {
    return Cell::unit('script', $c, is_string($a) ? ['src' => URL::full($a)] : $a, $d);
});

// `<a>`
Cell::add('a', function($href = null, $c = "", $target = null, $a = [], $d = 0) {
    $a['href'] = URL::full($href);
    $a['target'] = $target === true ? '_blank' : $target;
    return Cell::unit('a', $c, $a, $d);
});

// `<img>`
Cell::add('img', function($src = null, $alt = null, $a = [], $d = 0) {
    $a['src'] = URL::full($src);
    $a['alt'] = Cell::x($alt);
    return Cell::unit('img', false, $a, $d);
});

// `<(ol|ul)>`
foreach(['ol', 'ul'] as $unit) {
    Cell::add($unit, function($l = [], $a = [], $d = 0) use($unit) {
        $html = Cell::begin($unit, $a, $d) . N;
        foreach ($l as $k => $v) {
            if (is_array($v)) {
                $html .= Cell::begin('li', [], $d + 1) . $k . N;
                $html .= call_user_func('Cell::' . $unit, $v, $a, $d + 2) . N;
                $html .= Cell::end('li', $d + 1) . N;
            } else {
                $html .= Cell::unit('li', $v, [], $d + 1) . N;
            }
        }
        return $html . Cell::end($unit, $d);
    });
}

// `<(hr|br)>`
foreach (['hr', 'br'] as $unit) {
    Cell::add($unit, function($r = 1, $a = [], $d = 0) use($unit) {
        $d = Cell::dent($d);
        return $d . str_repeat(Cell::unit($unit, false, $a), $r);
    });
}