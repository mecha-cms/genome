<?php

foreach([
    'anemon' => function($in) {
        return (array) (is_array($in) || is_object($in) ? a($in) : json_decode($in, true));
    },
    'base64' => "\\base64_encode",
    'camel' => "\\c",
    'dec' => function(string $in, $z = false, $f = ['&#', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = ord($in[$i]);
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'file' => function(string $in) {
        $in = preg_split('#\s*[\\\/]\s*#', $in, null, PREG_SPLIT_NO_EMPTY);
        $n = preg_split('#\s*[.]\s*#', array_pop($in), null, PREG_SPLIT_NO_EMPTY);
        $x = array_pop($n);
        $out = "";
        foreach ($in as $v) {
            $out .= h($v, '-', true, '_') . DS;
        }
        $out .= h(implode('.', $n), '-', true, '_.') . '.' . h($x, '-', true);
        return $out === '.' ? "" : $out;
    },
    'folder' => function(string $in) {
        $in = preg_split('#\s*[\\\/]\s*#', $in, null, PREG_SPLIT_NO_EMPTY);
        $n = array_pop($in);
        $out = "";
        foreach ($in as $v) {
            $out .= h($v, '-', true, '_') . DS;
        }
        return $out . h($n, '-', true, '_');
    },
    'hex' => function(string $in, $z = false, $f = ['&#x', ';']) {
        $out = "";
        for($i = 0, $count = strlen($in); $i < $count; ++$i) {
            $s = dechex(ord($in[$i]));
            if (!$z) {
                $s = str_pad($s, 4, '0', STR_PAD_LEFT);
            }
            $out .= $f[0] . $s . $f[1];
        }
        return $out;
    },
    'HTML' => ["\\htmlspecialchars_decode", [null, ENT_QUOTES | ENT_HTML5]],
    'JSON' => function($in, $tidy = false) {
        if ($tidy) {
            $i = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        } else {
            $i = JSON_UNESCAPED_UNICODE;
        }
        return json_encode($in, $i);
    },
    'kebab' => function(string $in, $a = true) {
        return trim(h($in, '-', $a), '-');
    },
    'key' => function(string $in, $a = true) {
        $out = trim(h($in, '_', $a), '_');
        return $out && is_numeric($out[0]) ? '_' . $out : $out;
    },
    'lower' => "\\l",
    'pascal' => "\\p",
    'path' => function(string $in) {
        $u = $GLOBALS['URL'];
        $x = strtr($u['$'], '/', DS);
        $in = str_replace([$u['$'], '/', $x], [ROOT, DS, ROOT], $in);
        return realpath($in) ?: $in;
    },
    'query' => function(array $in) {
        $out = [];
        $q = function(array $in, $enter) use(&$q) {
            $a = [];
            $exit = $enter ? ']' : "";
            foreach ($in as $k => $v) {
                $k = urlencode($k);
                if (is_array($v)) {
                    $a = array_merge($a, $q($v, $enter . $k . $exit . '['));
                } else {
                    $a[$enter . $k . $exit] = $v;
                }
            }
            return $a;
        };
        foreach ($q($in, "") as $k => $v) {
            // `['a' => false, 'b' => 'false', 'c' => null, 'd' => 'null']` → `b=false&d=null`
            if (!isset($v) || $v === false) {
                continue;
            }
            // `['a' => true, 'b' => 'true', 'c' => ""]` → `a&b=true&c=`
            $v = $v !== true ? '=' . urlencode(s($v)) : "";
            if ("" !== ($v = $k . $v)) {
                $out[] = $v;
            }
        }
        return $out ? '?' . implode('&', $out) : "";
    },
    'sentence' => function(string $in, $tail = '.') {
        $in = trim($in);
        if (extension_loaded('mbstring')) {
            return mb_strtoupper(mb_substr($in, 0, 1)) . mb_strtolower(mb_substr($in, 1)) . $tail;
        }
        return ucfirst(strtolower($in)) . $tail;
    },
    'serial' => "\\serialize",
    'slug' => function(string $in, $s = '-', $a = true) {
        return trim(h($in, $s, $a), $s);
    },
    'snake' => function(string $in, $a = true) {
        return trim(h($in, '_', $a), '_');
    },
    'snippet' => function(string $in, $html = true, $x = 200) {
        $s = w($in, $html ? HTML_WISE_I : []);
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
            while ($done < $x[0] && preg_match('#</?([a-z\d:.-]+)(?:\s[^>]*)?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);|[\x80-\xFF][\x80-\xBF]*#i', $s, $m, PREG_OFFSET_CAPTURE, $i)) {
                $tag = $m[0][0];
                $pos = $m[0][1];
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
                    } else if (substr($tag, -2) === '/>' || preg_match('#<(?:area|base|br|col|command|embed|hr|img|input|link|meta|param|source)(?:\s[^>]*)?>#i', $tag)) {
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
    'text' => "\\w",
    'title' => function(string $in) {
        $in = w($in);
        $out = extension_loaded('mbstring') ? mb_convert_case($in, MB_CASE_TITLE) : ucwords($in);
        // Convert to abbreviation if all case(s) are in upper
        return u($out) === $out ? str_replace(' ', "", $out) : $out;
    },
    'upper' => "\\u",
    'URL' => function(string $in, $raw = false) {
        $u = $GLOBALS['URL'];
        $x = strtr(ROOT, DS, '/');
        $in = realpath($in) ?: $in;
        $in = str_replace([ROOT, DS, $x], [$u['$'], '/', $u['$']], $in);
        return $raw ? rawurldecode($in) : urldecode($in);
    },
    'YAML' => function($in, string $dent = '  ', $docs = false) {
        $yaml = function(array $data, string $dent = '  ') use(&$yaml) {
            $out = [];
            $yaml_list = function(array $data) use(&$yaml) {
                $out = [];
                foreach ($data as $v) {
                    if (is_array($v)) {
                        $out[] = '- ' . str_replace("\n", "\n  ", $yaml($v, $dent));
                    } else {
                        $out[] = '- ' . s($v, ['null' => '~']);
                    }
                }
                return implode("\n", $out);
            };
            $yaml_set = function(string $k, string $m, $v) {
                // Check for safe key pattern, otherwise, wrap it with quote
                if ($k !== "" && (is_numeric($k) || (ctype_alnum($k) && !is_numeric($k[0])) || preg_match('#^[a-z][a-z\d]*(?:[_-]+[a-z\d]+)*$#i', $k))) {
                } else {
                    $k = "'" . str_replace("'", "\\\'", $k) . "'";
                }
                return $k . $m . s($v, ['null' => '~']);
            };
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    if (fn\is\anemon_0($v)) {
                        $out[] = $yaml_set($k, ":\n", $yaml_list($v));
                    } else {
                        $out[] = $yaml_set($k, ":\n", $dent . str_replace("\n", "\n" . $dent, $yaml($v, $dent)));
                    }
                } else {
                    if (is_string($v)) {
                        if (strpos($v, "\n") !== false) {
                            $v = "|\n" . $dent . str_replace(["\n", "\n" . $dent . "\n"], ["\n" . $dent, "\n\n"], $v);
                        } else if (strlen($v) > 80) {
                            $v = ">\n" . $dent . wordwrap($v, 80, "\n" . $dent);
                        } else if (strtr($v, "!#%&*,-:<=>?@[\\]{|}", '-------------------') !== $v) {
                            $v = "'" . $v . "'";
                        } else if (is_numeric($v)) {
                            $v = "'" . $v . "'";
                        }
                    }
                    $out[] = $yaml_set($k, ': ', $v, $dent);
                }
            }
            return implode("\n", $out);
        };
        $yaml_docs = function(array $data, string $dent = '  ', $content = "\t") use(&$yaml) {
            $out = $s = "";
            if (isset($data[$content])) {
                $s = $data[$content];
                unset($data[$content]);
            }
            for ($i = 0, $count = count($data); $i < $count; ++$i) {
                $out .= "---\n" . $yaml($data[$i], $dent) . "\n";
            }
            return $out . "...\n\n" . trim($s, "\n");
        };
        return $docs ? $yaml_docs($in, $dent, $docs === true ? "\t" : $docs) : $yaml($in, $dent);
    }
] as $k => $v) {
    To::_($k, $v);
}

// Alias(es)…
foreach ([
    'files' => 'folder',
    'html' => 'HTML',
    'url' => 'URL',
    'yaml' => 'YAML'
] as $k => $v) {
    To::_($k, To::_($v));
}