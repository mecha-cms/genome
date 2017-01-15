<?php

$url = __url__();

To::plug('url', function($input) use($url) {
    $s = str_replace(DS, '/', ROOT);
    $input = str_replace([ROOT, DS, '\\', $s], [$url['url'], '/', '/', $url['url']], $input);
    // Fix broken external URL `http://://example.com`, `http:////example.com`
    $input = str_replace(['://://', ':////'], '://', $input);
    // @ditto `http:example.com`
    if (strpos($input, $url['scheme'] . ':') === 0 && strpos($input, $url['protocol']) !== 0) {
        $input = str_replace(X . $url['scheme'] . ':', $url['protocol'], X . $input);
    }
    return $input;
});

To::plug('path', function($input) use($url) {
    $s = str_replace('/', DS, $url['url']);
    return str_replace([$url['url'], '\\', '/', $s], [ROOT, DS, DS, ROOT], $input);
});

function __to_yaml__($input, $c = [], $in = '  ', $safe = false, $dent = 0) {
    $s = Page::$v;
    Anemon::extend($s, $c);
    if (__is_anemon__($input)) {
        $t = "";
        $line = !__is_anemon_assoc__($input) && !$safe;
        $T = str_repeat($in, $dent);
        foreach ($input as $k => $v) {
            if (!__is_anemon__($v) || empty($v)) {
                if (is_array($v)) {
                    $v = '[]';
                } else if (is_object($v)) {
                    $v = '{}';
                } else if ($v === "") {
                    $v = '""';
                } else {
                    $v = s($v);
                }
                $v = $v !== $s[4] && strpos($v, $s[2]) !== false ? json_encode($v) : $v;
                // Line
                if ($v === $s[4]) {
                    $t .= $s[4];
                // Comment
                } else if (strpos($v, '#') === 0) {
                    $t .= $T . trim($v) . $s[4];
                // …
                } else {
                    $t .= $T . ($line ? $s[3] : trim($k) . $s[2]) . $v . $s[4];
                }
            } else {
                $o = __to_yaml__($v, $s, $in, $safe, $dent + 1);
                $t .= $T . $k . $s[2] . $s[4] . $o . $s[4];
            }
        }
        return rtrim($t);
    }
    return $input !== $s[4] && strpos($input, $s[2]) !== false ? json_encode($input) : $input;
}

To::plug('text', 'w');

To::plug('title', function($input) {
    $input = w($input);
    if (function_exists('mb_strtoupper')) {
        return preg_replace_callback('#(^|[^a-z\d])(\p{Ll})#u', function($m) {
            return $m[1] . mb_strtoupper($m[2]);
        }, $input);
    }
    return ucwords($input);
});

To::plug('slug', 'h');

To::plug('snake', function($input) {
    return h($input, '_');
});

To::plug('html', function($input) {
    return $input; // do nothing …
});

To::plug('html_encode', 'htmlspecialchars');
To::plug('html_decode', 'htmlspecialchars_decode');

To::plug('html_dec', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = ord($input[$i]);
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#' . $s . ';';
    }
    return $output;
});

To::plug('html_hex', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#x' . $s . ';';
    }
    return $output;
});

To::plug('url_encode', 'urlencode');
To::plug('url_decode', 'urldecode');
To::plug('base64', 'base64_encode');
To::plug('json', 'json_encode');

To::plug('anemon', function($input) {
    if (__is_anemon__($input)) {
        return a($input);
    }
    return (array) json_decode($input, true);
});

To::plug('yaml', function(...$lot) {
    if (!__is_anemon__($lot[0])) {
        return s($lot[0]);
    }
    if (Is::path($lot[0], true)) {
        $lot[0] = include $lot[0];
    }
    return call_user_func_array('__to_yaml__', $lot);
});

To::plug('snippet', function($input, $html = true, $count = 200, $tail = '&#x2026;') {
    $s = w($input, $html ? explode(',', HTML_WISE_I) : []);
    $t = function_exists('mb_strlen') ? mb_strlen($s) : strlen($s);
    return (function_exists('mb_substr') ? mb_substr($s, 0, $count) : substr($s, 0, $count)) . ($t > $count ? $tail : "");
});

To::safe('file.name', function($input) {
    return f($input, '-', true, '\w.');
});

To::safe('folder.name', function($input) {
    return f($input, '-', true, '\w');
});

To::safe('key', function($input, $low = true) {
    $s = f($input, '_', $low);
    return is_numeric($s[0]) ? '_' . $s : $s;
});