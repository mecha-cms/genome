<?php

// Break into structure(s)
$yaml_select = function(string $in) {
    $out = [];
    $s = $n = null;
    foreach (explode("\n", $in) as $v) {
        if (substr($vv = trim($v), 0, 1) === '#') {
            continue; // Remove comment(s)
        }
        if ($v && $v[0] !== ' ' && strpos($v, '- ') !== 0 && $vv !== '-') {
            if ($s !== null) {
                $out[] = rtrim($s);
            }
            $s = $v;
        } else {
            $s .= $n ? ' ' . ltrim($v) : "\n" . $v;
        }
        $n = $vv === '-';
    }
    $out[] = rtrim($s);
    return $out;
};

// Dedent from `$dent`
$yaml_pull = function(string $in, string $dent) {
    if (strpos($in, $dent) === 0) {
        return str_replace("\n" . $dent, "\n", substr($in, strlen($dent)));
    }
    return $in;
};

// Folded-style string
$yaml_block = function(string $in) {
    $out = "";
    $e = false; // Previous is empty
    $x = false; // Has back-slash at the end of string
    foreach (explode("\n", $in) as $k => $v) {
        $t = trim($v);
        if ($t === "") {
            $out .= "\n";
        } else if (!$e && !$x) {
            $out .= ' ';
        }
        if ($t !== "" && substr($t, -1) === "\\") {
            $out .= ltrim(substr($v, 0, -1));
        } else if ($t !== "") {
            $out .= $t;
        }
        if ($t === "") {
            $e = true;
            $x = false;
        } else if (substr($t, -1) === "\\") {
            $e = false;
            $x = true;
        } else {
            $e = $x = false;
        }
    }
    return trim($out);
};

$yaml_list = function(string $in, string $dent, $e) use(&$yaml, &$yaml_break, &$yaml_pull, &$yaml_value) {
    $out = [];
    $in = $yaml_pull($in, '  ' /* hard-coded */);
    foreach (explode("\n- ", substr($in, 2)) as $v) {
        $v = str_replace("\n  ", "\n", $v);
        list($k, $m) = $yaml_break($v);
        if ($m === false) {
            $v = $yaml_value($v);
            $out[] = $e ? e($v, ['~' => null]) : $v;
        } else {
            $out[] = $yaml($v, $dent, $e);
        }
    }
    return $out;
};

// Parse flow-style collection(s)
$yaml_span = function(string $in, $e) {
    $out = "";
    // Validate to JSON
    foreach (preg_split('#\s*("(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[\[\]\{\}:,])\s*#', $in, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $v) {
        $out .= ctype_punct($v) ? $v : json_encode($v);
    }
    $out = json_decode($out, true) ?? $in;
    return $e ? e($out, ['~' => null]) : $out;
};

// Remove comment(s)
$yaml_value = function(string $in) {
    $in = trim($in);
    if (strpos($in, '"') === 0 || strpos($in, "'") === 0) {
        $q = $in[0];
        if (preg_match('#^(' . $q . '(?:[^' . $q . '\\\]|\\\.)*' . $q . ')(\s*\#.*)?$#', $in, $m)) {
            return $m[1];
        }
    }
    return trim(explode('#', $in, 2)[0]);
};

// Get key and value pair(s)
$yaml_break = function(string $in) {
    $in = trim($in, "\n");
    if (strpos($in, '"') === 0 || strpos($in, "'") === 0) {
        $q = $in[0];
        if (preg_match('#^(' . $q . '(?:[^' . $q . '\\\]|\\\.)*' . $q . ')\s*(:[ \n])([\s\S]*)$#', $in, $m)) {
            array_shift($m);
            $m[0] = e($m[0]);
            return $m;
        }
    } else if (
        strpos($in, ':') !== false &&
        strpos($in, '- ') !== 0 &&
        strpos('[{', $in[0]) === false
    ) {
        $m = explode(':', $in, 2);
        $m[0] = trim($m[0]);
        if (strpos($m[1], '#') !== false) {
            $m[1] = preg_replace('#^\s*\#.*$#m', "", $m[1]);
        }
        $m[2] = ltrim(rtrim($m[1] ?? ""), "\n");
        $m[1] = ':' . ($m[1][0] ?? "");
        return $m;
    }
    return [false, false, trim(explode('#', $in, 2)[0])];
};

$yaml_set = function(&$out, string $in, string $dent, $e) use(&$yaml, &$yaml_block, &$yaml_break, &$yaml_list, &$yaml_pull, &$yaml_span, &$yaml_value) {
    list($k, $m, $v) = $yaml_break($in);
    if ($k === false && $m === false && $v !== "") {
        if (
            $v[0] === '[' && substr($v, -1) === ']' ||
            $v[0] === '{' && substr($v, -1) === '}'
        ) {
            $out = $yaml_span($v, $e);
            return;
        }
    }
    $vv = $yaml_pull($v, $dent);
    // Get first token
    $t = substr(trim($vv), 0, 1);
    // A literal-style or folded-style scalar value
    if ($t === '|' || $t === '>') {
        $vv = $yaml_pull(ltrim(substr(ltrim($vv), 1), "\n"), $dent);
        $out[$k] = $t === '>' ? $yaml_block($vv) : $vv;
    // Maybe a YAML collection(s)
    } else if ($m === ":\n") {
        // Sequence
        if (strpos($vv, '- ') === 0) {
            // Indented sequence
            if (strpos($v, $dent . '-') === 0) {
                $v = $vv;
            }
            $out[$k] = $yaml_list($v, $dent, $e);
        // Else
        } else {
            $out[$k] = $vv !== "" ? $yaml($vv, $dent, $e) : [];
        }
    } else {
        $vv = $yaml_value($vv);
        if (strpos($vv, '- ') === 0) {
            $out = $yaml_list($vv, $dent, $e);
            return;
        }
        if ($vv === "" || $vv === '[]' || $vv === '{}') {
            $vv = []; // Empty array
        } else if (
            $vv && (
                $vv[0] === '[' && substr($vv, -1) === ']' ||
                $vv[0] === '{' && substr($vv, -1) === '}'
            )
        ) {
            // Use native JSON parser where possible
            $vv = json_decode($vv, true) ?? $yaml_span($vv, $e);
        } else if ($e) {
            $vv = e($vv, ['~' => null]);
        }
        $out[$k] = $vv;
    }
};

$yaml = function(string $in, string $dent = '  ', $e = true) use(&$yaml_select, &$yaml_set) {
    $out = [];
    // Normalize line-break
    $in = trim(n($in));
    if ($in === "") {
        return $out; // Empty array
    }
    foreach ($yaml_select($in) as $v) {
        $v !== "" && $yaml_set($out, $v, $dent, $e);
    }
    return $out;
};

$yaml_docs = function(string $in, string $dent = '  ', $e = true, $content = "\t") use(&$yaml) {
    $docs = [];
    // Normalize line-break
    $in = trim(n($in));
    // Remove the first separator
    $in = strpos($in, '---') === 0 && substr($in, 3, 1) !== '-' ? preg_replace('#^-{3}\s*#', "", $in) : $in;
    // Skip any string after `...`
    $parts = explode("\n...\n", trim($in) . "\n", 2);
    foreach (explode("\n---", $parts[0]) as $v) {
        $docs[] = $yaml(trim($v), $dent, $e);
    }
    // Take the rest of the YAML stream just in case you need it!
    if (isset($parts[1])) {
        // We use tab character as array key placeholder because based
        // on the specification, this character should not be written in
        // a YAML document, so it will be impossible that, there will be
        // a YAML key denoted by a human using a tab character.
        // <https://yaml.org/spec/1.2/spec.html#id2777534>
        $docs[$content] = trim($parts[1], "\n");
    }
    return $docs;
};

foreach ([
    'anemon' => function($in) {
        if ($in instanceof \Traversable) {
            return iterator_to_array($in);
        }
        return (array) $in;
    },
    'base64' => "\\base64_decode",
    'dec' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'hex' => ["\\html_entity_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'HTML' => ["\\htmlspecialchars", [null, ENT_QUOTES | ENT_HTML5]],
    'JSON' => function($in) {
        if (fn\is\anemon($in)) {
            return (object) \o($in);
        }
        return json_decode($in);
    },
    'query' => function(string $in) {
        $out = [];
        $q = function(array &$out, $k, $v) {
            $k = explode('[', str_replace(']', "", $k));
            while (count($k) > 1) {
                $kk = array_shift($k);
                if (!array_key_exists($kk, $out)) {
                    $out[$kk] = [];
                }
                $out =& $out[$kk];
            }
            $out[array_shift($k)] = $v;
        };
        if (isset($in[0]) && $in[0] === '?') {
            $in = substr($in, 1);
        }
        foreach (explode('&', $in) as $v) {
            $v = explode('=', $v, 2);
            $q($out, urldecode($v[0]), isset($v[1]) ? e(urldecode($v[1])) : true);
        }
        return $out;
    },
    'serial' => "\\unserialize",
    'URL' => function($in, $raw = false) {
        return $raw ? rawurlencode($in) : urlencode($in);
    },
    'YAML' => function(string $in, string $dent = '  ', $docs = false, $e = true) use(&$yaml, &$yaml_docs) {
        return $docs ? $yaml_docs($in, $dent, $e, $docs === true ? "\t" : $docs) : $yaml($in, $dent, $e);
    }
] as $k => $v) {
    From::_($k, $v);
}

// Alias(es)…
foreach ([
    'html' => 'HTML',
    'json' => 'JSON',
    'url' => 'URL',
    'yaml' => 'YAML'
] as $k => $v) {
    From::_($k, From::_($v));
}