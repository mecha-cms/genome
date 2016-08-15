<?php

From::plug('json', function($input) {
    if (__is_anemon__($input)) {
        return o($input);
    }
    return o(json_decode($input, true));
});

From::plug('base64', function($input) {
    return base64_decode($input);
});

function __from_yaml__($input, $c = [], $in = '  ') {
    $s = Genome\Sheet::$v;
    $q = ['"([^"\\\]++|\\\.)*+"', '\'([^\'\\\\]++|\\\.)*+\''];
    Anemon::extend($s, $c);
    if (!is_string($input)) return a($input);
    if (!trim($input)) return [];
    $i = 0;
    $output = $data = [];
    // Normalize white-space(s)
    $input = n($input);
    // Save `\: ` as `\x1A`
    $input = str_replace('\\' . $s[2], X, $input);
    $len = strlen($in);
    foreach (explode($s[4], $input) as $li) {
        $dent = 0;
        $li = rtrim($li);
        // Ignore comment and empty line-break
        if ($li === "" || strpos($li, '#') === 0) continue;
        while (substr($li, 0, $len) === $in) {
            $dent += 1;
            $li = substr($li, $len);
        }
        $li = ltrim($li) . ' ';
        while ($dent < count($data)) {
            array_pop($data);
        }
        // Start with `- `
        if (strpos($li, $s[3]) === 0) {
            $li = $i . $s[2] . substr($li, strlen($s[3]));
            $i++;
        } else {
            $i = 0;
        }
        // No `: ` ... fix it!
        if (strpos($li, $s[2]) === false) {
            $li = $li . $s[2] . $li;
        }
        if ($li[0] === '"' && preg_match('/^' . $q[0] . '\s*' . x($s[2]) . '(.*?)$/', $li, $part)) {
            array_shift($part);
        } elseif ($li[0] === "'" && preg_match('/^' . $q[1] . '\s*' . x($s[2]) . '(.*?)$/', $li, $part)) {
            array_shift($part);
        } else {
            $part = explode($s[2], $li, 2);
        }
        $v = trim($part[1] ?? "");
        // Remove inline comment(s) ...
        if ($v && strpos($v, '#') !== false) {
            if ($v[0] === '"' || $v[0] === "'") {
                $vv = '/(' . implode('|', $q) . ')|\s*#.*/';
                $v = preg_replace($vv, '$1', $v);
            } else {
                $v = explode('#', $v, 2);
                $v = trim($v[0]);
            }
        }
        // Restore `\x1A` as `: `
        $data[$dent] = str_replace(X, $s[2], trim($part[0]));
        $parent =& $output;
        foreach ($data as $k) {
            if (strpos($k, '"') === 0 || strpos($k, "'") === 0) {
                $k = t($k, $k[0]);
            }
            if (!isset($parent[$k])) {
                if (!$v) {
                    $parent[$k] = [];
                } else {
                    $v = e($v);
                    if (is_string($v)) {
                        if (strpos($v, '[') === 0 && substr($v, -1) === ']') {
                            $v = e(preg_split('/\s*,\s*/', trim(t($v, '[', ']'))));
                        } elseif (strpos($v, '{') === 0 && substr($v, -1) === '}') {
                            $v = trim(t($v, '{', '}'));
                            $v = preg_replace('/\s*,\s*/', $s[4], $v);
                            $v = __from_yaml__($v);
                        }
                    }
                    $parent[$k] = $v;
                }
                break;
            }
            $parent =& $parent[$k];
        }
    }
    return $output;
}

From::plug('yaml', function(...$lot) {
    if (__is_anemon__($lot[0])) return a($lot[0]);
    if (Is::path($lot[0])) {
        $lot[0] = file_get_contents($lot[0]);
    }
    $s = Genome\Sheet::$v;
    $lot[0] = str_replace([X . $s[0], $s[1] . X, X], "", X . $lot[0] . X);
    return call_user_func_array('__from_yaml__', $lot);
});

function __from_entity__($input) {
    return html_entity_decode($input);
}

From::plug('dec', '__from_entity__');
From::plug('hex', '__from_entity__');