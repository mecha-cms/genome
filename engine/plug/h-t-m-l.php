<?php

HTML::plug('br', function($i = 1, $attr = [], $dent = 0) {
    return HTML::dent($dent) . str_repeat(HTML::unite('br', false, $attr), $i);
});

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