<?php namespace fn\from;

// Get key and value pair…
function yaml_pair($in) {
    if ((strpos($in, "'") === 0 || strpos($in, '"') === 0) && preg_match('#(\'(?:[^\'\\\]|\\\.)*\'|"(?:[^"\\\]|\\\.)*") *: +([^\n]*)#', $in, $m)) {
        $out = [\t($m[1], $in[0]), $m[2]];
    } else {
        $out = explode(': ', $in, 2);
    }
    $out[0] = trim($out[0]);
    // If value is an empty string, replace with `[]`
    $out[1] = isset($out[1]) && $out[1] !== "" ? trim($out[1]) : [];
    return $out;
}

// Parse array-like string…
function yaml_array($in) {
    if (!is_string($in)) {
        return $in;
    }
    if (strpos($in, '[') === 0 && substr($in, -1) === ']' || strpos($in, '{') === 0 && substr($in, -1) === '}') {
        $out = "";
        foreach (preg_split('#(\s*(?:\'(?:[^\'\\\]|\\\.)*\'|"(?:[^"\\\]|\\\.)*"|[\[\]\{\}:,])\s*)#', $in, null, PREG_SPLIT_DELIM_CAPTURE) as $v) {
            if (($v = trim($v)) === "") {
                continue;
            }
            if (strpos('[]{}:,', $v) !== false || is_numeric($v) || $v === 'true' || $v === 'false' || $v === 'null') {
                // Do nothing!
            } else if (strpos($v, '"') === 0 && substr($v, -1) === '"') {
                if (json_decode($v) === null) {
                    $v = json_encode(\t($v, '"'));
                }
            } else if (strpos($v, "'") === 0 && substr($v, -1) === "'") {
                $v = json_encode(\t($v, "'"));
            } else {
                $v = json_encode($v);
            }
            $out .= $v;
        }
        return json_decode($out, true);
    }
    return $in;
}

function yaml($in, $d = '  ', $e = true) {
    // Normalize white-space(s)…
    $in = trim(\n($in), "\n");
    if ($in === "") {
        return [];
    }
    $key = $out = $i = [];
    $len = strlen($d);
    $x = \x($d);
    if (strpos($in, ': ') !== false && (strpos($in, '|') !== false || strpos($in, '>') !== false)) {
        $in = preg_replace_callback('#((?:' . $x . ')*)([^\n]+): +([|>])\s*\n((?:(?:(?:\1' . $x . '[^\n]*)?\n?)+|$))#', function($m) use($d) {
            $s = str_replace("\n" . $m[1] . $d, "\n", "\n" . $m[4]);
            if ($m[3] === '>') {
                $s = str_replace([
                    "\n\n",
                    "\n",
                    X
                ], [
                    X,
                    ' ',
                    "\n\n"
                ], trim($s, "\n"));
            } else {
                $s = \t($s, "\n");
            }
            return $m[1] . $m[2] . ': ' . json_encode($s) . "\n";
        }, $in);
    }
    foreach (explode("\n", $in) as $v) {
        $test = trim($v);
        // Ignore empty line-break and comment(s)…
        if ($test === "" || strpos($test, '#') === 0) {
            continue;
        }
        $dent = 0;
        while (substr($v, 0, $len) === $d) {
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
        $v = trim($v);
        $a = yaml_pair(substr($v, -1) === ':' ? $v . ' ' : $v);
        $key[$dent] = $a[0];
        if (is_string($a[1])) {
            // Ignore comment(s)…
            if (strpos($a[1], '#') === 0) {
                $a[1] = [];
            } else {
                $s = strpos($a[1], "'") === 0 || strpos($a[1], '"') === 0 ? $a[1] : explode('#', $a[1])[0];
                $s = trim($s);
                $s = $s === '~' ? null : $s;
                $a[1] = yaml_array($e ? \e($s) : $s);
            }
        }
        $parent =& $out;
        foreach ($key as $kk) {
            if (!isset($parent[$kk])) {
                $parent[$kk] = $a[1];
                break;
            }
            $parent =& $parent[$kk];
        }
    }
    return $out;
}

foreach ([
    'anemon' => function($in) {
        if ($in instanceof \Traversable) {
            return iterator_to_array($in);
        }
        return (array) $in;
    },
    'base64' => 'base64_decode',
    'dec' => ['html_entity_decode', [null, ENT_QUOTES | ENT_HTML5]],
    'hex' => ['html_entity_decode', [null, ENT_QUOTES | ENT_HTML5]],
    'HTML' => ['htmlspecialchars', [null, ENT_QUOTES | ENT_HTML5]],
    'JSON' => function($in) {
        if (\fn\is\anemon($in)) {
            return (object) \o($in);
        }
        return json_decode($in);
    },
    'query' => function($in, $c = []) {
        $c = extend(['?', '&', '=', ""], $c, false);
        if (!is_string($in)) {
            return [];
        }
        $out = [];
        foreach (explode($c[1], \t($in, $c[0], $c[3])) as $v) {
            $q = explode($c[2], $v, 2);
            $q[0] = urldecode($q[0]);
            if (isset($q[1])) {
                $q[1] = urldecode($q[1]);
                // `a=TRUE&b` → `['a' => 'true', 'b' => true]`
                // `a=true&b` → `['a' => 'true', 'b' => true]`
                $q[1] = \e($q[1] === 'TRUE' || $q[1] === 'true' ? '"true"' : $q[1]);
            } else {
                $q[1] = true;
            }
            \Anemon::set($out, str_replace(']', "", $q[0]), $q[1], '[');
        }
        return $out;
    },
    'serial' => 'unserialize',
    'URL' => function($in, $raw = false) {
        return $raw ? rawurlencode($in) : urlencode($in);
    },
    'YAML' => function(...$lot) {
        if (\fn\is\anemon($lot[0])) {
            return \a($lot[0]);
        }
        if (\Is::path($lot[0], true)) {
            $lot[0] = file_get_contents($lot[0]);
        }
        if (strpos($lot[0] = \n($lot[0]), "---\n") === 0) {
            $out = [];
            $lot[0] = str_replace([X . "---\n", "\n..." . X, X], "", X . $lot[0] . X);
            foreach (explode("\n---\n", $lot[0]) as $v) {
                $out[] = yaml(...$lot);
            }
            return $out;
        }
        return yaml(...$lot);
    }
] as $k => $v) {
    \From::_($k, $v);
}

// Alias(es)…
foreach ([
    'html' => 'HTML',
    'json' => 'JSON',
    'url' => 'URL',
    'yaml' => 'YAML'
] as $k => $v) {
    \From::_($k, \From::_($v));
}