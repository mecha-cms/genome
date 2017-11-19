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

From::plug('query', function($input, $c = []) {
    $c = array_replace(['?', '&', '=', ""], $c);
    if (!is_string($input)) {
        return [];
    }
    $output = [];
    foreach (explode($c[1], t($input, $c[0], $c[3])) as $v) {
        $q = explode($c[2], $v, 2);
        $q[0] = urldecode($q[0]);
        if (isset($q[1])) {
            $q[1] = urldecode($q[1]);
            $q[1] = e(Anemon::alter($q[1], [
                'TRUE' => '"true"', // `a=TRUE&b` → `['a' => 'true', 'b' => true]`
                'true' => '"true"'  // `a=true&b` → `['a' => 'true', 'b' => true]`
            ]));
        } else {
            $q[1] = true;
        }
        Anemon::set($output, str_replace(['[', ']'], ['.', ""], $q[0]), $q[1]);
    }
    return $output;
});

From::plug('url', function($input, $raw = false) {
    return $raw ? rawurlencode($input) : urlencode($input);
});

$str_1 = "'(?:[^'\\\]|\\\.)*'";
$str_2 = '"(?:[^"\\\]|\\\.)*"';
$str_any = '(' . $str_1 . '|' . $str_2 . ')';

// Get key and value pair…
function __from_yaml_k__($s) {
    global $str_any;
    if ((strpos($s, "'") === 0 || strpos($s, '"') === 0) && preg_match('#' . $str_any . ' *: +([^\n]*)#', $s, $m)) {
        $a = [t($m[1], '"'), $m[2]];
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
    global $str_1, $str_2;
    if (strpos($s, '[') === 0 && substr($s, -1) === ']' || strpos($s, '{') === 0 && substr($s, -1) === '}') {
        $a = preg_split('#(\s*(?:' . $str_1 . '|' . $str_2 . '|[\[\]\{\}:,])\s*)#', $s, null, PREG_SPLIT_DELIM_CAPTURE);
        $s = "";
        foreach ($a as $v) {
            if (($v = trim($v)) === "") {
                continue;
            }
            if (strpos('[]{}:,', $v) !== false || is_numeric($v) || $v === 'true' || $v === 'false' || $v === 'null') {
                // Do nothing!
            } else if (strpos($v, '"') === 0 && substr($v, -1) === '"') {
                if (json_decode($v) === null) {
                    $v = '"' . str_replace('"', '\\"', $v) . '"';
                }
            } else if (strpos($v, "'") === 0 && substr($v, -1) === "'") {
                $v = '"' . t($v, "'") . '"';
            } else {
                $v = '"' . $v . '"';
            }
            $s .= $v;
        }
        return json_decode($s, true);
    }
    return $s;
}

function __from_yaml__($input, $in = '  ', $ref = [], $e = true) {
    // Normalize white-space(s)…
    $input = trim(n($input), "\n");
    if ($input === "") {
        return [];
    }
    $key = [];
    $len = strlen($in);
    $i = [];
    // Save `\:` as `\x1A`
    $input = str_replace('\\:', X, $input);
    $x = x($in);
    if (strpos($input, '&') !== false) {
        if (preg_match_all('#((?:' . $x . ')*)([^\n]+): +&(\S+)(\s*\n((?:(?:\1' . $x . '[^\n]*)?\n)+|$)| *[^\n]*)#', $input, $m)) {
            foreach ($m[3] as $k => $v) {
                $ref[$v] = __from_yaml__($m[2][$k] . ':' . $m[4][$k], $in, $ref);
            }
        }
    }
    if (strpos($input, ': ') !== false && (strpos($input, '|') !== false || strpos($input, '>') !== false)) {
        $input = preg_replace_callback('#((?:' . $x . ')*)([^\n]+): +([|>])\s*\n((?:(?:\1' . $x . '[^\n]*)?\n)+|$)#', function($m) use($in) {
            $s = trim(str_replace("\n" . $m[1] . $in, "\n", "\n" . $m[4]), "\n");
            if ($m[3] === '>') {
                // TODO
                $s = preg_replace('#(\S)\n(\S)#', '$1 $2', $s);
            }
            return $m[1] . $m[2] . ': ' . json_encode($s) . "\n";
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
            // Copy…
            } else if (strpos($a[1], '&') === 0) {
                $a[1] = strpos($a[1], ' ') !== false ? explode(' ', $a[1], 2)[1] : [];
            // Paste…
            } else if (strpos($a[1], '*') === 0 && isset($ref[substr($a[1], 1)])) {
                $a[1] = array_pop($ref[substr($a[1], 1)]);
            } else {
                $s = strpos($a[1], "'") === 0 || strpos($a[1], '"') === 0 ? $a[1] : explode('#', $a[1])[0];
                $s = trim($s);
                $s = $s === '~' ? 'null' : $s;
                $a[1] = __from_yaml_a__($e ? e($s) : $s);
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
    if (strpos($lot[0] = n($lot[0]), "---\n") === 0) {
        $output = [];
        $lot[0] = str_replace([X . "---\n", "\n..." . X, X], "", X . $lot[0] . X);
        foreach (explode("\n---\n", $lot[0]) as $v) {
            $output[] = call_user_func_array('__from_yaml__', $lot);
        }
        return $output;
    }
    return call_user_func_array('__from_yaml__', $lot);
});

// Alias(es)…
From::plug('h_t_m_l', 'htmlspecialchars');
From::plug('j_s_o_n', 'From::json');
From::plug('u_r_l', 'From::url');
From::plug('y_a_m_l', 'From::yaml');