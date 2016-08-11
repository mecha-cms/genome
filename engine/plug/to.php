<?php

To::plug('url', function($input) {/*
    $url = URL::extract();
    $input = str_replace([ROOT, DS, '\\'], [$url->url, '/', '/'], $input);
    // Fix broken external URL `http://://example.com`, `http:////example.com`
    $input = str_replace(['://://', ':////'], '://', $input);
    // @ditto `http:example.com`
    if (strpos($input, $url->scheme . ':') === 0 && strpos($input, $url->protocol) !== 0) {
        $input = str_replace(X . $url->scheme . ':', $url->protocol, X . $input);
    }*/
    return $input;
});

To::plug('path', function($input) {/*
    $url = Config::get('url');
    return str_replace([X . $url, '\\', '/', X], [ROOT, DS, DS, ""], X . $input);*/
});

function __to_yaml__($input, $c = [], $in = '  ', $dent = 0) {
    $cc = array_slice(Sheet::$v, 1);
    Anemon::extend($cc, $c);
    if (__is_anemon__($input)) {
        $t = "";
        foreach ($input as $k => $v) {
            if (!__is_anemon__($v)) {
                $v = s($v);
                $v = $v !== $cc[1] && strpos($v, $cc[2]) !== false ? json_encode($v) : $v;
                $T = str_repeat($in, $dent);
                // Line
                if($v === $cc[1]) {
                    $t .= $cc[1];
                // Comment
                } elseif (strpos($v, '#') === 0) {
                    $t .= $T . trim($v) . $cc[1];
                // ...
                } else {
                    $t .= $T . trim($k) . $cc[0] . $v . $n;
                }
            } else {
                $s = __to_yaml__($v, $cc, $in, $dent + 1);
                $t .= $T . $k . $cc[0] . $cc[1] . $s . $cc[1];
            }
        }
        return str_replace($cc[0] . $cc[1], $cc[0] . '""' . $cc[1], rtrim($t));
    }
    return $input !== $cc[1] && strpos($input, $cc[1]) !== false ? json_encode($input) : $input;
}

To::plug('yaml', '__to_yaml__');

To::plug('case', 'w');

To::plug('case_lower', 'l');

To::plug('case_upper', 'u');

To::plug('case_title', function($input) {
    if (function_exists('mb_strtoupper')) {
        return preg_replace_callback('#(^|[^a-z\d])(\p{Ll})#u', function($m) {
            return $m[1] . mb_strtoupper($m[2]);
        }, w($input));
    }
    return ucwords(w($input));
});

To::plug('case_pascal', 'p');

To::plug('case_camel', 'c');

To::plug('case_slug', 'h');

To::plug('case_snake', function($input) {
    return h($input, '_');
});

To::plug('title', 'To::case_title');
To::plug('slug', 'To::case_slug');

To::plug('html', function($input) {
    return $input; // do nothing ...
});

To::plug('html_x', function($input) {
    return htmlspecialchars($input);
});

To::plug('html_v', function($input) {
    return htmlspecialchars_decode($input);
});

To::plug('dec', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = ord($input[$i]);
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#' . $s . ';';
    }
    return $output;
});

To::plug('hex', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#x' . $s . ';';
    }
    return $output;
});

To::plug('url_x', function($input) {
    return urlencode($input);
});

To::plug('url_v', function($input) {
    return urldecode($input);
});

To::plug('base64', function($input) {
    return base64_encode($input);
});

To::plug('json', function($input) {
    return json_encode($input);
});

To::plug('anemon', function($input) {
    if (__is_anemon__($input)) {
        return a($input);
    }
    return (array) json_decode($input, true);
});

To::plug('yaml', function($input) {
    if (__is_anemon__($input)) return a($input);
    return $input;
});

To::safe('file.name', function($input) {
    return f($input, '-', true, '\w.');
});

To::safe('folder.name', function($input) {
    return f($input, '-', true, '\w');
});

To::safe('path.name', function($input) {
    $x = '-' . DS;
    $s = str_replace(['\\', '/', '\\\\', '//'], [DS, DS, $x, $x], $input);
    return f($s, '-', true, '\w.\\\/');
});

To::safe('key', function($input, $low = true) {
    $s = f($input, '_', $low);
    return is_numeric($s[0]) ? preg_replace('#^\d+#', '_', $s) : $s;
});