<?php

From::plug('base64', 'base64_decode');
From::plug('dec', 'html_entity_decode');
From::plug('hex', 'html_entity_decode');
From::plug('html', 'htmlspecialchars');

From::plug('json', function($input) {
    if (__is_anemon__($input)) {
        return (object) o($input);
    }
    return json_decode($input);
});

From::plug('url', function($input, $raw = false) {
    return $raw ? rawurlencode($input) : urlencode($input);
});

$str_1 = "'(?:[^'\\\]|\\\.)*'";
$str_2 = '"(?:[^"\\\]|\\\.)*"';
$str_x = '(' . $str_1 . '|' . $str_2 . ')';

// Get key and value pair…
function __from_yaml_k__($s) {
    global $str_x;
    if ((strpos($s, "'") === 0 || strpos($s, '"') === 0) && preg_match('#' . $str_x . ' *: +([^\n]*)#', $s, $m)) {
        $a = [$m[1], $m[2]];
    } else {
        $a = explode(':', $s, 2);
    }
    $a[0] = trim($a[0]);
    // If value is an empty string, replace with `[]`
    $a[1] = isset($a[1]) && $a[1] !== "" ? trim($a[1]) : [];
    return $a;
}

// Parse array-like string…
function __from_yaml_a__($s) {
    if (!is_string($s)) {
        return $s;
    }
    global $str_x;
    if (strpos($s, '[') === 0 && substr($s, -1) === ']') {
        if ((strpos($s, "'") !== false || strpos($s, '"') !== false) && strpos($s, ',') !== false) {
            $s = preg_replace_callback('#' . $str_x . '#', function($m) {
                return str_replace(',', X, $m[1]);
            }, $s);
        }
        $a = [];
        foreach (preg_split('#(, *?(?:\[.*?\]|\{.*?\}) *,|,)#', t($s, '[', ']'), null, PREG_SPLIT_DELIM_CAPTURE) as $v) {
            if ($v === ',') continue;
            $v = trim(str_replace(X, ',', $v), ', ');
            if (strpos($v, '[') === 0 && substr($v, -1) === ']' || strpos($v, '{') === 0 && substr($v, -1) === '}') {
                $a[] = __from_yaml_a__($v);
            } else {
                $a[] = trim($v);
            }
        }
        return $a;
    } else if (strpos($s, '{') === 0 && substr($s, -1) === '}') {
        $a = [];
        foreach (__from_yaml_a__('[' . t($s, '{', '}') . ']') as $v) {
            if (is_string($v)) {
                $v = __from_yaml_k__($v);
                $a[$v[0]] = __from_yaml_a__($v[1]);
            } else {
                $a[] = $v;
            }
        }
        return $a;
    }
    return $s;
}

function __from_yaml__($input, $in = '  ', $ref = []) {
    $output = $key = [];
    $len = strlen($in);
    $i = [];
    // Normalize white-space(s)
    $input = trim(n($input), "\n");
    // Save `\:` as `\x1A`
    $input = str_replace('\\:', X, $input);
    if (strpos($input, ': |') !== false || strpos($input, ': >') !== false) {
        $input = preg_replace_callback('#((?:' . x($in) . ')*)([^\n]+?): +([|>])\s*\n((?:(?:\1 +[^\n]*?)?\n)+|$)#', function($m) use($in) {
            $s = trim(str_replace("\n" . $in, "\n", "\n" . $m[4]), "\n");
            if ($m[3] === '>') {
                // TODO
                $s = preg_replace('#( *)([^\n]*)\n\1#', '$1$2 $1', $s);
            }
            return $m[1] . $m[2] . ': ' . json_encode($s) . "\n" . $m[1];
        }, $input);
    }
    foreach (explode("\n", $input) as $v) {
        $test = trim($v);
        // Ignore empty line-break and comment(s)…
        if ($test === "" || strpos($test, '#') === 0) {
            continue;
        }
        $dent = 0;
        while (substr($v, 0, $len) === $in) {
            ++$dent;
            $v = substr($v, $len);
        }
        // Start with `- `
        if (strpos($v, '- ') === 0) {
            ++$dent;
            if (isset($i[$dent])) {
                $i[$dent] += 1;
            } else {
                $i[$dent] = 0;
            }
            $v = substr_replace($v, $i[$dent] . ': ', 0, 2);
        // TODO
        } else if ($v === '-') {
            ++$dent;
            if (isset($i[$dent])) {
                $i[$dent] += 1;
            } else {
                $i[$dent] = 0;
            }
            $v = $i[$dent] . ':';
        } else {
            $i = [];
        }
        while ($dent < count($key)) {
            array_pop($key);
        }
        $a = __from_yaml_k__(trim($v));
        // Restore `\x1A` to `:`
        $a[0] = $key[$dent] = str_replace(X, ':', $a[0]);
        if (is_string($a[1])) {
            // Ignore comment(s)…
            if (strpos($a[1], '#') === 0) {
                $a[1] = [];
            // TODO
            } else if (strpos($a[1], '&') === 0) {
                $ref[substr($a[1], 1)] = $a[0];
                $a[1] = [];
            } else {
                $a[1] = __from_yaml_a__(e($a[1]));
            }
        }
        $parent =& $output;
        foreach ($key as $kk) {
            if (!isset($parent[$kk])) {
                $parent[$kk] = $a[1];
                break;
            }
            $parent =& $parent[$kk];
        }
    }
    return $output;
}

From::plug('yaml', function(...$lot) {
    if (__is_anemon__($lot[0])) {
        return a($lot[0]);
    }
    if (Is::path($lot[0], true)) {
        $lot[0] = file_get_contents($lot[0]);
    }
    return call_user_func_array('__from_yaml__', $lot);
});

// Alias(es)…
From::plug('h_t_m_l', 'htmlspecialchars');
From::plug('j_s_o_n', 'From::json');
From::plug('u_r_l', 'From::url');
From::plug('y_a_m_l', 'From::yaml');