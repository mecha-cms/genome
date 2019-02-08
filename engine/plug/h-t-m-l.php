<?php

// Class methods naming follows how the HTML tag naming standards that are not
// case-sensitive. “camelCase” and “ABBR” (abreviation) specifications do not apply here.

foreach([
    'a' => function(string $text = "", string $href = null, $target = null, array $attr = [], $dent = 0) {
        $a = [
            'target' => $target === true ? '_blank' : ($target === false ? null : $target)
        ];
        $attr = extend($a, $attr);
        $attr['href'] = URL::long(str_replace('&amp;', '&', $href));
        return static::unite('a', $text, $attr, $dent);
    },
    'img' => function(string $src = null, string $alt = "", array $attr = [], $dent = 0) {
        if (strpos($src, ROOT) === 0) {
            $path = $src;
            $src = To::URL($src);
        } else {
            $path = To::path($src);
            // $src = To::URL($src);
        }
        if (file_exists($path)) {
            $z = getimagesize($path);
        } else {
            $z = [null, null];
        }
        $a = [
            'alt' => !isset($alt) ? "" : $alt,
            'width' => $z[0],
            'height' => $z[1]
        ];
        $attr = extend($a, $attr);
        $attr['src'] = URL::long(str_replace('&amp;', '&', $src));
        return static::unite('img', false, $attr, $dent);
    }
] as $k => $v) {
    HTML::_($k, $v);
}

foreach (['br', 'hr'] as $kin) {
    HTML::_($kin, function(int $i = 1, array $attr = [], $dent = 0) use($kin) {
        return static::dent($dent) . str_repeat(static::unite($kin, false, $attr), $i);
    });
}

foreach (['ol', 'ul'] as $kin) {
    HTML::_($kin, function(array $list = [], array $attr = [], $dent = 0) use($kin) {
        $tag = new static;
        $html = $tag->begin($kin, $attr, $dent) . N;
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $html .= $tag->begin('li', [], $dent + 1) . $k . N;
                $html .= call_user_func('static::' . $kin, $v, $attr, $dent + 2) . N;
                $html .= $tag->end('li', $dent + 1) . N;
            } else {
                $html .= static::unite('li', $v, [], $dent + 1) . N;
            }
        }
        return $html . $tag->end($kin, $dent);
    });
}