<?php

From::plug('json', function($input) {
    if (__such_anemon__($input)) {
        return a($input);
    }
    return json_decode($input);
});

From::plug('base64', function($input) {
    return base64_decode($input);
});

function __from_yaml__($input, $c = [], $in = '  ') {
    $cc = array_slice(Sheet::$sv, 1);
    Anemon::extend($cc, $c);
    if (!is_string($input)) return a($input);
    if (!trim($input)) return [];
    $i = 0;
    $output = $data = [];
    // Normalize white-space(s)
    $input = n($input);
    // Save `\: ` as `\x1A`
    $input = str_replace('\\' . $cc[0], X, $input);
    $len = strlen($in);
    foreach (explode($cc[1], $input) as $li) {
        $dent = 0;
        $li = rtrim($li);
        // Ignore comment and empty line-break
        if ($li === "" || strpos($li, '#') === 0) continue;
        while (substr($li, 0, $len) === $in) {
            $dent += 1;
            $li = substr($li, $len);
        }
        $li = ltrim($li);
        while ($dent < count($data)) {
            array_pop($data);
        }
        // No `: ` ... fix it!
        if (strpos($li, $cc[0]) === false) {
            $li = $li . $cc[0] . $li;
        // Start with `: `
        } elseif (strpos($li, $li[0]) === 0) {
            $li = $i . $li;
            $i++;
        // else ...
        } else {
            $i = 0;
        }
        $part = explode($s, $li, 2);
        $v = trim($part[1]);
        // Remove inline comment(s) ...
        if($v && strpos($v, '#') !== false) {
            if($v[0] === '"' || $v[0] === "'") {
                $vv = '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\s*\#.*#';
                $v = preg_replace($vv, '$1', $v);
            } else {
                $v = explode('#', $v, 2);
                $v = trim($v[0]);
            }
        }
        // Restore `\x1A` as `: `
        $data[$dent] = str_replace(X, $cc[0], trim($part[0]));
        $parent =& $output;
        foreach($data as $k) {
            if(!isset($parent[$k])) {
                $parent[$k] = e($v ? $v : []);
                break;
            }
            $parent =& $parent[$k];
        }
    }
    return $output;
}

From::plug('yaml', '__from_yaml__');

function __from_entity__($input) {
    return html_entity_decode($input);
}

From::plug('dec', '__from_entity__');
From::plug('hex', '__from_entity__');