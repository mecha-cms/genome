<?php namespace fn\to;

function query($array, $key) {
    $out = [];
    $s = $key ? ']' : "";
    foreach ($array as $k => $v) {
        $k = urlencode($k);
        if (is_array($v)) {
            $out = concat($out, query($v, $key . $k . $s . '['));
        } else {
            $out[$key . $k . $s] = $v;
        }
    }
    return $out;
}

function yaml($in, $d = '  ', $safe = false, $dent = 0) {
    if (\fn\is\anemon($in)) {
        $out = "";
        $li = \fn\is\anemon_0($in) && !$safe; // is numeric array?
        $t = str_repeat($d, $dent);
        foreach ($in as $k => $v) {
            if (strpos($k, ':') !== false) {
                $k = '"' . $k . '"';
            }
            if (!\fn\is\anemon($v) || empty($v)) {
                if (is_array($v)) {
                    $v = '[]';
                } else if (is_object($v)) {
                    $v = '{}';
                } else if ($v === "") {
                    $v = '""';
                } else {
                    $v = \s($v);
                }
                $v = strpos($v, "\n") !== false ? "|\n" . $t . $d . str_replace("\n", "\n" . $t . $d, $v) : $v;
                // Comment…
                if (strpos($v, '#') === 0) {
                    $out .= $t . trim($v) . "\n";
                // …
                } else {
                    $out .= $t . ($li ? '- ' : trim($k) . ': ') . $v . "\n";
                }
            } else {
                $out .= $t . $k . ":\n" . yaml($v, $d, $safe, $dent + 1) . "\n";
            }
        }
        return rtrim($out, "\n");
    }
    return strpos($in, ': ') === false ? json_encode($in) : $in;
}

foreach([
    'anemon' => function($in) {
        return (array) (is_array($in) || is_object($in) ? a($in) : json_decode($in, true));
    },
    'base64' => 'base64_encode',
    'camel' => '\c',
    'dec' => function(string $in, $z = false, $f = ['&#', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = ord($in[$i]);
            if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'file' => function(string $in) {
        $in = array_map('trim', explode(DS, strtr($in, '/', DS)));
        $n = array_map('trim', explode('.', array_pop($in)));
        $x = array_pop($n);
        $s = "";
        foreach ($in as $v) {
            if ($v === "") continue;
            $s .= \h($v, '-', true, '_') . DS;
        }
        $out = $s . \h(implode('.', $n), '-', true, '_.') . '.' . \h($x, '-', true);
        return $out === '.' ? "" : $out;
    },
    'folder' => function(string $in) {
        $in = array_map('trim', explode(DS, strtr($in, '/', DS)));
        $n = array_pop($in);
        $s = "";
        foreach ($in as $v) {
            if ($v === "") continue;
            $s .= \h($v, '-', true, '_') . DS;
        }
        return $s . \h($n, '-', true, '_');
    },
    'hex' => function(string $in, $z = false, $f = ['&#x', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = dechex(ord($in[$i]));
            if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'HTML' => ['htmlspecialchars_decode', [null, ENT_QUOTES | ENT_HTML5]],
    'JSON' => function($in, $tidy = false) {
        if ($tidy) {
            $i = JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $i = JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($in, $i);
    },
    'kebab' => function(string $in, $a = true) {
        return trim(\h($in, '-', $a), '-');
    },
    'key' => function(string $in, $a = true) {
        $s = trim(\h($in, '_', $a), '_');
        return $s && is_numeric($s[0]) ? '_' . $s : $s;
    },
    'lower' => '\l',
    'pascal' => '\p',
    'path' => function(string $in) {
        $u = $GLOBALS['URL'];
        $s = strtr($u['$'], '/', DS);
        $in = str_replace([$u['$'], '\\', '/', $s], [ROOT, DS, DS, ROOT], $in);
        return file_exists($in) ? realpath($in) : $in;
    },
    'query' => function(array $in, $c = []) {
        $c = \extend(['?', '&', '=', ""], $c, false);
        foreach (query($in, "") as $k => $v) {
            if (!isset($v) || $v === false) continue; // `['a' => 'false', 'b' => false, 'c' => 'null', 'd' => null]` → `a=false&c=null`
            $v = $v !== true ? $c[2] . urlencode(\s($v)) : ""; // `['a' => 'true', 'b' => true]` → `a=true&b`
            $out[] = urlencode($k) . $v;
        }
        return !empty($out) ? $c[0] . implode($c[1], $out) . $c[3] : "";
    },
    'sentence' => function(string $in, $tail = '.') {
        $in = trim($in);
        if (extension_loaded('mbstring')) {
            return mb_strtoupper(mb_substr($in, 0, 1)) . mb_strtolower(mb_substr($in, 1)) . $tail;
        }
        return ucfirst(strtolower($in)) . $tail;
    },
    'serial' => 'serialize',
    'slug' => function(string $in, $s = '-', $a = true) {
        return trim(\h($in, $s, $a), $s);
    },
    'snake' => function(string $in, $a = true) {
        return trim(\h($in, '_', $a), '_');
    },
    'snippet' => function(string $in, $html = true, $x = 200) {
        $s = \w($in, $html ? HTML_WISE_I : []);
        $utf8 = extension_loaded('mbstring');
        if (is_int($x)) {
            $x = [$x, '&#x2026;'];
        }
        $utf8 = extension_loaded('mbstring');
        // <https://stackoverflow.com/a/1193598/1163000>
        if ($html && (strpos($s, '<') !== false || strpos($s, '&') !== false)) {
            $out = "";
            $done = $i = 0;
            $tags = [];
            while ($done < $x[0] && preg_match('#</?([a-z\d:.-]+)(?:\s[^<>]*?)?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);|[\x80-\xFF][\x80-\xBF]*#i', $s, $m, PREG_OFFSET_CAPTURE, $i)) {
                list($tag, $pos) = $m[0];
                $str = substr($s, $i, $pos - $i);
                if ($done + strlen($str) > $x[0]) {
                    $out .= substr($str, 0, $x[0] - $done);
                    $done = $x[0];
                    break;
                }
                $out .= $str;
                $done += strlen($str);
                if ($done >= $x[0]) {
                    break;
                }
                if ($tag[0] === '&' || ord($tag) >= 0x80) {
                    $out .= $tag;
                    ++$done;
                } else {
                    // `tag`
                    $n = $m[1][0];
                    // `</tag>`
                    if ($tag[1] === '/') {
                        $open = array_pop($tags);
                        assert($open === $n); // Check that tag(s) are properly nested!
                        $out .= $tag;
                    // `<tag/>`
                    } else if (substr($tag, -2) === '/>' || preg_match('#<(?:br|hr|img|input|link|meta|svg)(?:\s[^<>]*?)?>#i', $tag)) {
                        $out .= $tag;
                    // `<tag>`
                    } else {
                        $out .= $tag;
                        $tags[] = $n;
                    }
                }
                // Continue after the tag…
                $i = $pos + strlen($tag);
            }
            // Print rest of the text…
            if ($done < $x[0] && $i < strlen($s)) {
                $out .= substr($s, $i, $x[0] - $done);
            }
            // Close any open tag(s)…
            while ($close = array_pop($tags)) {
                $out .= '</' . $close . '>';
            }
            $out = trim(str_replace('<br>', ' ', $out));
            $s = trim(strip_tags($s));
            $t = $utf8 ? mb_strlen($s) : strlen($s);
            return $out . ($t > $x[0] ? $x[1] : "");
        }
        $out = $utf8 ? mb_substr($s, 0, $x[0]) : substr($s, 0, $x[0]);
        $t = $utf8 ? mb_strlen($s) : strlen($s);
        return trim($out) . ($t > $x[0] ? $x[1] : "");
    },
    'text' => '\w',
    'title' => function(string $in) {
        $in = \w($in);
        $out = extension_loaded('mbstring') ? mb_convert_case($in, MB_CASE_TITLE) : ucwords($in);
        // Convert to abbreviation if case(s) are all in upper
        return u($out) === $out ? str_replace(' ', "", $out) : $out;
    },
    'upper' => '\u',
    'URL' => function(string $in, $raw = false) {
        $u = $GLOBALS['URL'];
        $s = strtr(ROOT, DS, '/');
        $in = file_exists($in) ? realpath($in) : $in;
        $in = str_replace([ROOT, DS, '\\', $s], [$u['$'], '/', '/', $u['$']], $in);
        return $raw ? rawurldecode($in) : urldecode($in);
    },
    'YAML' => function(...$lot) {
        if (!\fn\is\anemon($lot[0])) {
            return \s($lot[0]);
        }
        if (is_string($lot[0]) && \Is::path($lot[0], true)) {
            $lot[0] = include $lot[0];
        }
        return yaml(...$lot);
    }
] as $k => $v) {
    \To::_($k, $v);
}

// Alias(es)…
foreach ([
    'files' => 'folder',
    'html' => 'HTML',
    'url' => 'URL',
    'yaml' => 'YAML'
] as $k => $v) {
    \To::_($k, \To::_($v));
}