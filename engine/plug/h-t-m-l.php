<?php

HTML::plug('a', function($content = "", $href = null, $target = null, $attr = [], $dent = 0) {
    $attr_o = [
        'href' => URL::long(str_replace('&amp;', '&', $href)),
        'target' => $target === true ? '_blank' : ($target === false ? null : $target)
    ];
    return HTML::unite('a', $content, Anemon::extend($attr_o, $attr), $dent);
});

HTML::plug('img', function($src = null, $alt = null, $attr = [], $dent = 0) {
    $attr_o = [
        'src' => URL::long(str_replace('&amp;', '&', $src)),
        'alt' => !isset($alt) ? "" : $alt
    ];
    return HTML::unite('img', false, Anemon::extend($attr_o, $attr), $dent);
});

foreach (['br', 'hr'] as $unit) {
    HTML::plug($unit, function($i = 1, $attr = [], $dent = 0) use($unit) {
        return HTML::dent($dent) . str_repeat(HTML::unite($unit, false, $attr), $i);
    });
}

foreach (['ol', 'ul'] as $unit) {
    HTML::plug($unit, function($list = [], $attr = [], $dent = 0) use($unit) {
        $html = HTML::begin($unit, $attr, $dent) . N;
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $html .= HTML::begin('li', [], $dent + 1) . $k . N;
                $html .= call_user_func('HTML::' . $unit, $v, $attr, $dent + 2) . N;
                $html .= HTML::end('li', $dent + 1) . N;
            } else {
                $html .= HTML::unit('li', $v, [], $dent + 1) . N;
            }
        }
        return $html . HTML::end($unit, $dent);
    });
}