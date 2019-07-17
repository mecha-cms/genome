<?php

To::_('YAML', $fn = function(array $in, string $dent = '  ', $docs = false) {
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
            if ($k !== "" && (is_numeric($k) || (ctype_alnum($k) && !is_numeric($k[0])) || preg_match('/^[a-z][a-z\d]*(?:[_-]+[a-z\d]+)*$/i', $k))) {
            } else {
                $k = "'" . str_replace("'", "\\\'", $k) . "'";
            }
            return $k . $m . s($v, ['null' => '~']);
        };
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                if (_\anemon_0($v)) {
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
});

// Alias
To::_('yaml', $fn);