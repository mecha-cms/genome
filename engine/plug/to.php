<?php

To::plug('url', function($input) {
    $url = _url_();
    $input = str_replace([ROOT, DS, '\\'], [$url['url'], '/', '/'], $input);
    // Fix broken external URL `http://://example.com`, `http:////example.com`
    $input = str_replace(['://://', ':////'], '://', $input);
    // @ditto `http:example.com`
    if (strpos($input, $url['scheme'] . ':') === 0 && strpos($input, $url['protocol']) !== 0) {
        $input = str_replace(X . $url['scheme'] . ':', $url['protocol'], X . $input);
    }
    return $input;
});

To::plug('path', function($input) {
    return str_replace([X . _url_('url'), '\\', '/', X], [ROOT, DS, DS, ""], X . $input);
});

function _to_yaml_($input, $c = [], $in = '  ', $safe = true, $dent = 0) {
    $s = Genome\Sheet::$v;
    Anemon::extend($s, $c);
    if (_is_anemon_($input)) {
        $t = "";
        $T = str_repeat($in, $dent);
        foreach ($input as $k => $v) {
            if (!_is_anemon_($v) || empty($v)) {
                if (is_array($v)) {
                    $v = '[]';
                } elseif (is_object($v)) {
                    $v = '{}';
                } elseif ($v === "") {
                    $v = '""';
                } else {
                    $v = s($v);
                }
                $v = $v !== $s[4] && strpos($v, $s[2]) !== false ? json_encode($v) : $v;
                // Line
                if ($v === $s[4]) {
                    $t .= $s[4];
                // Comment
                } elseif (strpos($v, '#') === 0) {
                    $t .= $T . trim($v) . $s[4];
                // ...
                } else {
                    $t .= $T . trim($k) . $s[2] . $v . $s[4];
                }
            } else {
                $o = _to_yaml_($v, $s, $in, $safe, $dent + 1);
                $t .= $T . $k . $s[2] . $s[4] . $o . $s[4];
            }
        }
        return rtrim($t);
    }
    return $input !== $s[4] && strpos($input, $s[2]) !== false ? json_encode($input) : $input;
}

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

To::plug('html_encode', function($input) {
    return htmlspecialchars($input);
});

To::plug('html_decode', function($input) {
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

To::plug('url_encode', function($input) {
    return urlencode($input);
});

To::plug('url_decode', function($input) {
    return urldecode($input);
});

To::plug('base64', function($input) {
    return base64_encode($input);
});

To::plug('json', function($input) {
    return json_encode($input);
});

To::plug('anemon', function($input) {
    if (_is_anemon_($input)) {
        return a($input);
    }
    return (array) json_decode($input, true);
});

To::plug('yaml', function($input, $c = [], $in = '  ', $safe = true) {
    if (!_is_anemon_($input)) return s($input);
    return _to_yaml_($input, $c, $in, $safe, 0);
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