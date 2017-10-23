<?php

To::plug('anemon', function($input) {
    if (__is_anemon__($input)) {
        return a($input);
    }
    return json_decode($input, true);
});

To::plug('base64', 'base64_encode');
To::plug('camel', 'c');

To::plug('dec', function($input, $z = false, $f = ['&#', ';']) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = ord($input[$i]);
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= $f[0] . $s . $f[1];
    }
    return $output;
});

To::plug('file', function($input) {
    $input = explode(DS, str_replace('/', DS, $input));
    $n = explode('.', array_pop($input));
    $x = array_pop($n);
    $s = "";
    foreach ($input as $v) {
        $s .= f($v, '-', true, '\w') . DS;
    }
    return $s . f(implode('.', $n), '-', true, '\w.') . '.' . f($x, '-', true);
});

To::plug('folder', function($input) {
    $input = explode(DS, str_replace('/', DS, $input));
    $n = array_pop($input);
    $s = "";
    foreach ($input as $v) {
        $s .= f($v, '-', true, '\w') . DS;
    }
    return $s . f($n, '-', true, '\w');
});

To::plug('hex', function($input, $z = false, $f = ['&#x', ';']) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= $f[0] . $s . $f[1];
    }
    return $output;
});

To::plug('html', 'htmlspecialchars_decode');

To::plug('json', 'json_encode');

To::plug('key', function($input, $low = true) {
    $s = f($input, '_', $low);
    return is_numeric($s[0]) ? '_' . $s : $s;
});

To::plug('pascal', 'p');

To::plug('path', function($input) {
    $u = __url__();
    $s = str_replace('/', DS, $u['url']);
    return str_replace([$u['url'], '\\', '/', $s], [ROOT, DS, DS, ROOT], $input);
});

To::plug('sentence', function($input, $tail = '.') {
    $input = trim($input);
    if (extension_loaded('mbstring')) {
        return mb_strtoupper(mb_substr($input, 0, 1)) . mb_strtolower(mb_substr($input, 1)) . $tail;
    }
    return ucfirst(strtolower($input)) . $tail;
});

To::plug('slug', 'h');

To::plug('snake', function($input) {
    return h($input, '_');
});

To::plug('snippet', function($input, $html = true, $x = [200, '&#x2026;']) {
    $s = w($input, $html ? HTML_WISE_I : []);
    $t = extension_loaded('mbstring') ? mb_strlen($s) : strlen($s);
    if (is_int($x)) {
        $x = [$x, '&#x2026;'];
    }
    $s = extension_loaded('mbstring') ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
    $s = str_replace('<br>', ' ', $s);
    // Remove the unclosed HTML tag(s)…
    if ($html && strpos($s, '<') !== false) {
        $s = preg_replace('#<\/[^>]*$#', "", $s); // `foo bar </a`
        $ss = '#<[^\/>]+?>([^<]*?)$#';
        while (preg_match($ss, $s)) {
            $s = preg_replace($ss, '$1', $s); // `foo bar <a href="">baz`
        }
        $s = preg_replace('#<[^>]*$#', "", $s); // `foo bar <a href=`
    }
    return trim($s) . ($t > $x[0] ? $x[1] : "");
});

To::plug('text', 'w');

To::plug('title', function($input) {
    $input = w($input);
    if (extension_loaded('mbstring')) {
        return mb_convert_case($input, MB_CASE_TITLE);
    }
    return ucwords($input);
});

To::plug('url', function($input, $raw = false) {
    $u = __url__();
    $s = str_replace(DS, '/', ROOT);
    $input = str_replace([ROOT, DS, '\\', $s], [$u['url'], '/', '/', $u['url']], $input);
    // Fix broken external URL `http://://example.com`, `http:////example.com`
    $input = str_replace(['://://', ':////'], '://', $input);
    // --ditto `http:example.com`
    if (strpos($input, $u['scheme'] . ':') === 0 && strpos($input, $u['protocol']) !== 0) {
        $input = str_replace(X . $u['scheme'] . ':', $u['protocol'], X . $input);
    }
    return $raw ? rawurldecode($input) : urldecode($input);
});

function __to_yaml__($input, $in = '  ', $safe = false, $dent = 0) {
    if (__is_anemon__($input)) {
        $t = "";
        $line = __is_anemon_0__($input) && !$safe;
        $T = str_repeat($in, $dent);
        foreach ($input as $k => $v) {
            if (strpos($k, ':') !== false) {
                $k = '"' . $k . '"';
            }
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
                $v = strpos($v, "\n") !== false ? "|\n" . $T . $in . str_replace("\n", "\n" . $T . $in, $v) : $v;
                // Comment…
                if (strpos($v, '#') === 0) {
                    $t .= $T . trim($v) . "\n";
                // …
                } else {
                    $t .= $T . ($line ? '- ' : trim($k) . ': ') . $v . "\n";
                }
            } else {
                $t .= $T . $k . ":\n" . __to_yaml__($v, $in, $safe, $dent + 1) . "\n";
            }
        }
        return rtrim($t, "\n");
    }
    return strpos($input, ': ') === false ? json_encode($input) : $input;
}

To::plug('yaml', function(...$lot) {
    if (!__is_anemon__($lot[0])) {
        return s($lot[0]);
    }
    if (is_string($lot[0]) && Is::path($lot[0], true)) {
        $lot[0] = include $lot[0];
    }
    return call_user_func_array('__to_yaml__', $lot);
});

// Alias(es)…
To::plug('h_t_m_l', 'htmlspecialchars_decode');
To::plug('u_r_l', 'To::url');
To::plug('y_a_m_l', 'To::yaml');