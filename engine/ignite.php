<?php

namespace fn\is {
    // Check for valid data collection (array or object)
    function anemon($x = null, $t = null) {
        if (is_string($t)) {
            return anemon_a($x);
        } else if (is_int($t)) {
            return anemon_0($x);
        }
        return is_array($x) || is_object($x);
    }
    // `[1,2,3]`
    function anemon_0($x = null) {
        $a = (array) $x;
        $count = count($a);
        return $count && array_keys($a) === range(0, $count - 1);
    }
    // `{"a":1,"b":2,"c":3}`
    function anemon_a($x = null) {
        $a = (array) $x;
        $count = count($a);
        return $count && array_keys($a) !== range(0, $count - 1);
    }
    // Check for any valid class instance
    function instance($x = null) {
        if (!is_object($x)) return false;
        return ($s = get_class($x)) && $s !== 'stdClass' ? $x : false;
    }
    // Check for valid JSON string format
    function json($x = null) {
        if (!is_string($x) || trim($x) === "") return false;
        return (
            // Maybe an empty string, array or object
            $x === '""' ||
            $x === '[]' ||
            $x === '{}' ||
            // Maybe an encoded JSON string
            $x[0] === '"' && substr($x, -1) === '"' ||
            // Maybe a numeric array
            $x[0] === '[' && substr($x, -1) === ']' ||
            // Maybe an associative array
            $x[0] === '{' && substr($x, -1) === '}'
        ) && json_decode($x) !== null;
    }
    // Check for valid serialized string format
    function serial($x = null) {
        if (!is_string($x) || trim($x) === "") {
            return false;
        } else if ($x === 'N;') {
            return true;
        } else if (strpos($x, ':') === false) {
            return false;
        } else if ($x === 'b:1;' || $x === 'b:0;' || $x === 'a:0:{}' || $x === 'O:8:"stdClass":0:{}') {
            return true;
        }
        return strpos($x, 'a:') === 0 || strpos($x, 'O:') === 0 || strpos($x, 'd:') === 0 || strpos($x, 'i:') === 0 || strpos($x, 's:') === 0;
    }
}

namespace {
    // Check if array contains …
    function any(array $a = [], callable $fn) {
        foreach ($a as $k => $v) {
            if (call_user_func($fn, $v, $k)) {
                return true;
            }
        }
        return false;
    }
    // Filter out element(s) that does not pass the function test
    function but(array $a = [], callable $fn) {
        return array_filter($a, function($v, $k) use($fn) {
            return !call_user_func($fn, $v, $k);
        }, ARRAY_FILTER_USE_BOTH);
    }
    // Replace pattern to its value
    function candy(string $s = "", $a = [], $x = "\n", $r = true) {
        if (!$s || strpos($s, '%') === false) return $s;
        $a = (array) $a;
        foreach ($a as $k => $v) {
            if (is_array($v) || is_object($v)) {
                // `%{$.a.b.c}%`
                if (strpos($s, '%{' . $k . '.') !== false) {
                    $s = preg_replace_callback('#\%\{' . x($k) . '(\.[a-z\d_]+)*\}\%#i', function($m) use($v) {
                        $a = explode('.', $m[1] ?? "");
                        $b = array_pop($a);
                        if (isset($m[2])) {
                            $fn = substr($m[2], 1);
                            $fn = is_callable($fn) ? $fn : false;
                        } else {
                            $fn = false;
                        }
                        if ($b) {
                            if (is_object($v)) {
                                if (!method_exists($v, '__get') && !isset($v->{$b})) {
                                    return $m[0];
                                }
                                $v = $v->{$b};
                            } else if (is_array($v)) {
                                if (!isset($v[$b])) {
                                    return $m[0];
                                }
                                $v = $v[$b];
                            }
                            if ($a) {
                                if (!is_array($v) && !is_object($v)) {
                                    return $v;
                                }
                                while ($b = array_pop($a)) {
                                    if (!is_array($v) && !is_object($v)) {
                                        return $v;
                                    }
                                    if (is_object($v)) {
                                        if (!method_exists($v, '__get') && !isset($v->{$b})) {
                                            return $m[0];
                                        }
                                        $v = $v->{$b};
                                    } else if (is_array($v)) {
                                        $v = $v[$b] ?? $m[0];
                                    }
                                }
                                return $fn ? call_user_func($fn, $v) : $v;
                            }
                        }
                        return $fn ? call_user_func($fn, $v) : $v;
                    }, $s);
                }
                // `%{$}%`
                if (is_object($v) && method_exists($v, '__toString')) {
                    $s = str_replace('%{' . $k . '}%', $v . "", $s);
                }
            // `%{a}%`
            } else if (strpos($s, '%{' . $k . '}%') !== false) {
                $s = str_replace('%{' . $k . '}%', s($v), $s);
                continue;
            }
            // TODO: replace pattern(s) as in `format` function
        }
        return $s;
    }
    // Convert class name to file name
    function c2f(string $s = "", string $h = '-', string $n = '.') {
        return ltrim(str_replace(['\\', $n . $h], $n, h($s, $h, false, '\\\\')), $h);
    }
    // Merge array value(s)
    function concat(array $a = [], ...$b) {
        return array_merge_recursive($a, ...$b);
    }
    // Extend array value(s)
    function extend(array $a = [], ...$b) {
        return array_replace_recursive($a, ...$b);
    }
    // Convert file name to class name
    function f2c(string $s = "", string $h = '-', string $n = '.') {
        return str_replace($n, '\\', p($s, false, $n));
    }
    // Return the first element found in array that passed the function test
    function find(array $a = [], callable $fn) {
        foreach ($a as $k => $v) {
            if (call_user_func($fn, $v, $k)) {
                return $v;
            }
        }
        return false;
    }
    // Trigger function with scope and parameter(s)
    function fn(callable $fn, $t = null, array $a = []) {
        $fn = \Closure::fromCallable($fn);
        return call_user_func($t ? $fn->bindTo($t) : $fn, ...$a);
    }
    // Replace pattern to regular expression
    function format(string $s = "", string $x = "\n", string $d = '#', $r = true) {
        if (!$s || strpos($s, '%') === false) return $s;
        $r = $r ? "" : '?';
        // group: `%[foo,bar,baz]%`
        if (($i = strpos($s, '%[')) !== false && strpos($s, ']%') > $i) {
            $s = preg_replace_callback('#%\[([\s\S]+?)' . $r . '\]%#', function($m) {
                $m[1] = str_replace(['\,', ','], [X, '|'], $m[1]);
                return '(' . $m[1] . ')';
            }, $s);
        }
        return str_replace([
            '%s%', // any string excludes `$x`
            '%i%', // any string number(s)
            '%f%', // any string number(s) includes float(s)
            '%b%', // any string boolean(s)
           '%\*%', // any string includes `$x`
             X
        ], [
            '([^' . $x . ']+)' . $r,
            '(\-?\d+)' . $r,
            '(\-?(?:(?:\d+)?\.)?\d+)' . $r,
            '(\b(?:TRUE|FALSE|YES|NO|Y|N|ON|OFF|true|false|yes|no|y|n|on|off|1|0|\+|\-)\b)' . $r,
            '([\s\S]+)' . $r,
            ','
        ], x($s, $d)); // return a regular expression string without the delimiter(s)
    }
    // Check if an element exists in array
    function has(array $a = [], $s = "") {
        return strpos(X . implode(X, $a) . X, X . $s . X) !== false;
    }
    // Filter out element(s) that pass the function test
    function is(array $a = [], $fn = null) {
        return $fn ? array_filter($a, $fn, ARRAY_FILTER_USE_BOTH) : array_filter($a);
    }
    // Manipulate array value(s)
    function map(array $a = [], callable $fn) {
        return array_map($fn, $a);
    }
    // Dump PHP code
    function test(...$a) {
        foreach ($a as $b) {
            $s = var_export($b, true);
            echo '<pre style="word-wrap:break-word;white-space:pre-wrap;background:#fff;color:#000;border:1px solid;padding:.5em;">';
            echo str_replace(["\n", "\r"], "", highlight_string("<?php\n\n" . $s . "\n\n?>", true));
            echo '</pre>';
        }
    }
}

// a: convert object to array
// b: keep value between `a` and `b`
// c: convert text to camel case
// d: declare class(es) with callback
// e: evaluate string to their appropriate data type
// f: filter/sanitize string
// g: advance PHP `glob` function
// h: convert text to snake case with `-` (hyphen) as the default separator
// i: include file(s) with callback
// j:
// k:
// l: convert text to lower case
// m:
// n: normalize white-space in string
// o: convert array to object
// p: convert text to pascal case
// q: quantity (length of string, number or anemon)
// r: require file(s) with callback
// s: convert data type to their string format
// t: trim string from specific prefix and suffix
// u: convert text to upper case
// v: un-escape
// w: convert any data to plain word(s)
// x: escape
// y: output/yield an echo-based function as normal return value
// z: export array/object into a compact PHP file

namespace {
    function a($o = [], $safe = true) {
        if (fn\is\anemon($o)) {
            if ($safe) {
                $o = fn\is\instance($o) ? $o : (array) $o;
            } else {
                $o = (array) $o;
            }
            foreach ($o as &$oo) {
                $oo = a($oo, $safe);
            }
            unset($oo);
        }
        return $o;
    }
    function b($x = 0, $a = 0, $b = null) {
        if (isset($a) && $x < $a) return $a;
        if (isset($b) && $x > $b) return $b;
        return $x;
    }
    function c(string $x = "", $a = false, string $i = "") {
        return preg_replace_callback('# ([\p{L}\p{N}' . $i . '])#u', function($m) {
            return u($m[1]);
        }, f($x, $a, $i));
    }
    function d(string $f = "", $fn = null, array $s__ = []) {
        spl_autoload_register(function($w) use($f, $fn, $s__) {
            $n = c2f($w);
            $f = $f . DS . $n . '.php';
            if (file_exists($f)) {
                if ($s__) extract($s__);
                require $f;
                if (is_callable($fn)) {
                    call_user_func($fn, $w, $n, $s__);
                }
            }
        });
    }
    function e($s = "", array $x = []) {
        if (is_string($s)) {
            if ($s === "") return $s;
            if (is_numeric($s)) {
                return strpos($s, '.') !== false ? (float) $s : (int) $s;
            } else if (fn\is\json($s) && $v = json_decode($s)) {
                return $v;
            } else if ($s[0] === '"' || $s[0] === "'") {
                return t($s, $s[0]);
            }
            $a = [
                'TRUE' => true,
                'FALSE' => false,
                'NULL' => null,
                'YES' => true,
                'NO' => false,
                'ON' => true,
                'OFF' => false,
                'true' => true,
                'false' => false,
                'null' => null,
                'yes' => true,
                'no' => false,
                'on' => true,
                'off' => false
            ];
            return array_key_exists($s, $a) ? $a[$s] : $s;
        } else if (fn\is\anemon($s)) {
            $x = X . implode(X, (array) $x) . X;
            foreach ($s as $k => &$v) {
                if (strpos($x, X . $k . X) === false) {
                    $v = e($v, (array) $x);
                }
            }
            unset($v);
        }
        return $s;
    }
    // $x: the string input
    // $a: replace multi-byte string into their accent
    // $i: character(s) white-list
    function f(string $x = "", $a = true, string $i = "") {
        // this function does not trim white-space at the start and end of the string
        $x = preg_replace([
            // remove HTML tag(s) and character(s) reference
            '#<[^<>]*?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);#i',
            // remove anything except character(s) white-list
            '#[^\p{L}\p{N}\s' . $i . ']#u',
            // convert multiple white-space to single space
            '#\s+#'
        ], ' ', $x);
        return $a ? strtr($x, [
            '¹' => '1',
            '²' => '2',
            '³' => '3',
            '°' => '0',
            'æ' => 'ae',
            'ǽ' => 'ae',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ǻ' => 'A',
            'Ă' => 'A',
            'Ǎ' => 'A',
            'Æ' => 'AE',
            'Ǽ' => 'AE',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ǻ' => 'a',
            'ă' => 'a',
            'ǎ' => 'a',
            'ª' => 'a',
            '@' => 'at',
            'Ĉ' => 'C',
            'Ċ' => 'C',
            'ĉ' => 'c',
            'ċ' => 'c',
            '©' => 'c',
            'Ð' => 'Dj',
            'Đ' => 'D',
            'ð' => 'dj',
            'đ' => 'd',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ĕ' => 'E',
            'Ė' => 'E',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ĕ' => 'e',
            'ė' => 'e',
            'ƒ' => 'f',
            'Ĝ' => 'G',
            'Ġ' => 'G',
            'ĝ' => 'g',
            'ġ' => 'g',
            'Ĥ' => 'H',
            'Ħ' => 'H',
            'ĥ' => 'h',
            'ħ' => 'h',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ĩ' => 'I',
            'Ĭ' => 'I',
            'Ǐ' => 'I',
            'Į' => 'I',
            'Ĳ' => 'IJ',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ĩ' => 'i',
            'ĭ' => 'i',
            'ǐ' => 'i',
            'į' => 'i',
            'ĳ' => 'ij',
            'Ĵ' => 'J',
            'ĵ' => 'j',
            'Ĺ' => 'L',
            'Ľ' => 'L',
            'Ŀ' => 'L',
            'ĺ' => 'l',
            'ľ' => 'l',
            'ŀ' => 'l',
            'Ñ' => 'N',
            'ñ' => 'n',
            'ŉ' => 'n',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ō' => 'O',
            'Ŏ' => 'O',
            'Ǒ' => 'O',
            'Ő' => 'O',
            'Ơ' => 'O',
            'Ø' => 'O',
            'Ǿ' => 'O',
            'Œ' => 'OE',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ō' => 'o',
            'ŏ' => 'o',
            'ǒ' => 'o',
            'ő' => 'o',
            'ơ' => 'o',
            'ø' => 'o',
            'ǿ' => 'o',
            'º' => 'o',
            'œ' => 'oe',
            'Ŕ' => 'R',
            'Ŗ' => 'R',
            'ŕ' => 'r',
            'ŗ' => 'r',
            'Ŝ' => 'S',
            'Ș' => 'S',
            'ŝ' => 's',
            'ș' => 's',
            'ſ' => 's',
            'Ţ' => 'T',
            'Ț' => 'T',
            'Ŧ' => 'T',
            'Þ' => 'TH',
            'ţ' => 't',
            'ț' => 't',
            'ŧ' => 't',
            'þ' => 'th',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ũ' => 'U',
            'Ŭ' => 'U',
            'Ű' => 'U',
            'Ų' => 'U',
            'Ư' => 'U',
            'Ǔ' => 'U',
            'Ǖ' => 'U',
            'Ǘ' => 'U',
            'Ǚ' => 'U',
            'Ǜ' => 'U',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ũ' => 'u',
            'ŭ' => 'u',
            'ű' => 'u',
            'ų' => 'u',
            'ư' => 'u',
            'ǔ' => 'u',
            'ǖ' => 'u',
            'ǘ' => 'u',
            'ǚ' => 'u',
            'ǜ' => 'u',
            'Ŵ' => 'W',
            'ŵ' => 'w',
            'Ý' => 'Y',
            'Ÿ' => 'Y',
            'Ŷ' => 'Y',
            'ý' => 'y',
            'ÿ' => 'y',
            'ŷ' => 'y',
            'Ъ' => "",
            'Ь' => "",
            'А' => 'A',
            'Б' => 'B',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Э' => 'E',
            'Ф' => 'F',
            'Г' => 'G',
            'Х' => 'H',
            'И' => 'I',
            'Й' => 'J',
            'Я' => 'Ja',
            'Ю' => 'Ju',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Ш' => 'Sh',
            'Щ' => 'Shch',
            'Т' => 'T',
            'У' => 'U',
            'В' => 'V',
            'Ы' => 'Y',
            'З' => 'Z',
            'Ж' => 'Zh',
            'ъ' => "",
            'ь' => "",
            'а' => 'a',
            'б' => 'b',
            'ц' => 'c',
            'ч' => 'ch',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'э' => 'e',
            'ф' => 'f',
            'г' => 'g',
            'х' => 'h',
            'и' => 'i',
            'й' => 'j',
            'я' => 'ja',
            'ю' => 'ju',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'ш' => 'sh',
            'щ' => 'shch',
            'т' => 't',
            'у' => 'u',
            'в' => 'v',
            'ы' => 'y',
            'з' => 'z',
            'ж' => 'zh',
            'Ä' => 'AE',
            'Ö' => 'OE',
            'Ü' => 'UE',
            'ß' => 'ss',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ç' => 'C',
            'Ğ' => 'G',
            'İ' => 'I',
            'Ş' => 'S',
            'ç' => 'c',
            'ğ' => 'g',
            'ı' => 'i',
            'ş' => 's',
            'Ā' => 'A',
            'Ē' => 'E',
            'Ģ' => 'G',
            'Ī' => 'I',
            'Ķ' => 'K',
            'Ļ' => 'L',
            'Ņ' => 'N',
            'Ū' => 'U',
            'ā' => 'a',
            'ē' => 'e',
            'ģ' => 'g',
            'ī' => 'i',
            'ķ' => 'k',
            'ļ' => 'l',
            'ņ' => 'n',
            'ū' => 'u',
            'Ґ' => 'G',
            'І' => 'I',
            'Ї' => 'Ji',
            'Є' => 'Ye',
            'ґ' => 'g',
            'і' => 'i',
            'ї' => 'ji',
            'є' => 'ye',
            'Č' => 'C',
            'Ď' => 'D',
            'Ě' => 'E',
            'Ň' => 'N',
            'Ř' => 'R',
            'Š' => 'S',
            'Ť' => 'T',
            'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c',
            'ď' => 'd',
            'ě' => 'e',
            'ň' => 'n',
            'ř' => 'r',
            'š' => 's',
            'ť' => 't',
            'ů' => 'u',
            'ž' => 'z',
            'Ą' => 'A',
            'Ć' => 'C',
            'Ę' => 'E',
            'Ł' => 'L',
            'Ń' => 'N',
            'Ó' => 'O',
            'Ś' => 'S',
            'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
            'Α' => 'A',
            'Β' => 'B',
            'Γ' => 'G',
            'Δ' => 'D',
            'Ε' => 'E',
            'Ζ' => 'Z',
            'Η' => 'E',
            'Θ' => 'Th',
            'Ι' => 'I',
            'Κ' => 'K',
            'Λ' => 'L',
            'Μ' => 'M',
            'Ν' => 'N',
            'Ξ' => 'X',
            'Ο' => 'O',
            'Π' => 'P',
            'Ρ' => 'R',
            'Σ' => 'S',
            'Τ' => 'T',
            'Υ' => 'Y',
            'Φ' => 'Ph',
            'Χ' => 'Ch',
            'Ψ' => 'Ps',
            'Ω' => 'O',
            'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'ά' => 'a',
            'έ' => 'e',
            'ή' => 'e',
            'ί' => 'i',
            'ΰ' => 'Y',
            'α' => 'a',
            'β' => 'b',
            'γ' => 'g',
            'δ' => 'd',
            'ε' => 'e',
            'ζ' => 'z',
            'η' => 'e',
            'θ' => 'th',
            'ι' => 'i',
            'κ' => 'k',
            'λ' => 'l',
            'μ' => 'm',
            'ν' => 'n',
            'ξ' => 'x',
            'ο' => 'o',
            'π' => 'p',
            'ρ' => 'r',
            'ς' => 's',
            'σ' => 's',
            'τ' => 't',
            'υ' => 'y',
            'φ' => 'ph',
            'χ' => 'ch',
            'ψ' => 'ps',
            'ω' => 'o',
            'ϊ' => 'i',
            'ϋ' => 'y',
            'ό' => 'o',
            'ύ' => 'y',
            'ώ' => 'o',
            'ϐ' => 'b',
            'ϑ' => 'th',
            'ϒ' => 'Y',
            'أ' => 'a',
            'ب' => 'b',
            'ت' => 't',
            'ث' => 'th',
            'ج' => 'g',
            'ح' => 'h',
            'خ' => 'kh',
            'د' => 'd',
            'ذ' => 'th',
            'ر' => 'r',
            'ز' => 'z',
            'س' => 's',
            'ش' => 'sh',
            'ص' => 's',
            'ض' => 'd',
            'ط' => 't',
            'ظ' => 'th',
            'ع' => 'aa',
            'غ' => 'gh',
            'ف' => 'f',
            'ق' => 'k',
            'ك' => 'k',
            'ل' => 'l',
            'م' => 'm',
            'ن' => 'n',
            'ه' => 'h',
            'و' => 'o',
            'ي' => 'y',
            'ạ' => 'a',
            'ả' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ậ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ặ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'ẹ' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ệ' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ị' => 'i',
            'ỉ' => 'i',
            'ọ' => 'o',
            'ỏ' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ộ' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ợ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ụ' => 'u',
            'ủ' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ự' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ỳ' => 'y',
            'ỵ' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'Ạ' => 'A',
            'Ả' => 'A',
            'Ầ' => 'A',
            'Ấ' => 'A',
            'Ậ' => 'A',
            'Ẩ' => 'A',
            'Ẫ' => 'A',
            'Ằ' => 'A',
            'Ắ' => 'A',
            'Ặ' => 'A',
            'Ẳ' => 'A',
            'Ẵ' => 'A',
            'Ẹ' => 'E',
            'Ẻ' => 'E',
            'Ẽ' => 'E',
            'Ề' => 'E',
            'Ế' => 'E',
            'Ệ' => 'E',
            'Ể' => 'E',
            'Ễ' => 'E',
            'Ị' => 'I',
            'Ỉ' => 'I',
            'Ọ' => 'O',
            'Ỏ' => 'O',
            'Ồ' => 'O',
            'Ố' => 'O',
            'Ộ' => 'O',
            'Ổ' => 'O',
            'Ỗ' => 'O',
            'Ờ' => 'O',
            'Ớ' => 'O',
            'Ợ' => 'O',
            'Ở' => 'O',
            'Ỡ' => 'O',
            'Ụ' => 'U',
            'Ủ' => 'U',
            'Ừ' => 'U',
            'Ứ' => 'U',
            'Ự' => 'U',
            'Ử' => 'U',
            'Ữ' => 'U',
            'Ỳ' => 'Y',
            'Ỵ' => 'Y',
            'Ỷ' => 'Y',
            'Ỹ' => 'Y'
        ]) : $x;
    }
    // $s: directory path
    // $x: file extension or name pattern… `txt`, `log,txt`
    // $q: filter by query
    // $o: order?
    // $h: include hidden file(s)?
    function g(string $s = ROOT, string $x = '*', $q = "", $o = false, $h = true) {
        $s = rtrim($s, DS) . DS;
        $x = str_replace(' ', "", $x);
        $f = GLOB_BRACE | GLOB_NOSORT;
        if (strpos($s . $x, '{') !== false) {
            // ...
        } else if (strpos($x, ',') !== false) {
            $x = strpos($x, '.') === false ? '*.{' . $x . '}' : '{' . $x . '}';
        } else if (strpos($x, '.') === false) {
            $x = '*.' . $x;
        }
        $g = glob($s . $x, $f);
        if ($h) {
            $g = concat($g, glob($s . '.' . $x, $f));
        }
        if (!$q) {
            if ($o) {
                natsort($g);
            }
            return $g;
        }
        $r = [];
        if (is_callable($q)) {
            foreach ($g as $k => $v) {
                if (call_user_func($q, $v, $k)) {
                    $r[] = $v;
                }
            }
        } else {
            foreach ($g as $k => $v) {
                $s = pathinfo($v, PATHINFO_FILENAME);
                if (strpos($s, $q) !== false) {
                    $r[] = $v;
                }
            }
        }
        if ($o) {
            natsort($r);
        }
        unset($g);
        return $r;
    }
    function h(string $x = "", string $h = '-', $a = false, $i = "") {
        return str_replace([' ', $h . $h], $h, preg_replace_callback('#\p{Lu}#', function($m) use($h) {
            return $h . l($m[0]);
        }, f($x, $a, x($h) . $i)));
    }
    // If `$fn` is a function, the function will be executed after file include.
    // If `$fn` is array of function, the first function will be executed before
    // file include, and the rest will be executed after file include.
    function i(string $a = "", $b = [], $fn = [null, null], array $s__ = []) {
        if (!is_array($fn)) {
            $fn = [null, $fn];
        } else if (!isset($fn[1])) {
            $fn[1] = null;
        }
        if (fn\is\anemon($b)) {
            foreach ($b as $v) {
                if ($s__) extract($s__);
                $v = $a . DS . $v;
                if (is_callable($fn[0])) { // before
                    call_user_func($fn[0], $v, $s__);
                }
                include $v;
                if (is_callable($fn[1])) { // after
                    call_user_func($fn[1], $v, $s__);
                }
            }
        } else {
            foreach (g($a, $b) as $v) {
                if ($s__) extract($s__);
                if (is_callable($fn[0])) { // before
                    call_user_func($fn[0], $v, $s__);
                }
                include $v;
                if (is_callable($fn[1])) { // after
                    call_user_func($fn[1], $v, $s__);
                }
            }
        }
    }
    function j() {}
    function k() {}
    function l(string $x = "") {
        return extension_loaded('mbstring') ? mb_strtolower($x) : strtolower($x);
    }
    function m() {}
    function n(string $x = "", string $t = '    ') {
        // Tab to 4 space(s), line-break to `\n`
        return str_replace(["\t", "\r\n", "\r"], [$t, "\n", "\n"], $x);
    }
    function o($a = [], $safe = true) {
        if (fn\is\anemon($a)) {
            if ($safe) {
                $a = fn\is\anemon_a($a) ? (object) $a : $a;
            } else {
                $a = (object) $a;
            }
            foreach ($a as &$aa) {
                $aa = o($aa, $safe);
            }
            unset($aa);
        }
        return $a;
    }
    function p(string $x = "", $a = false, $i = "") {
        return ltrim(c(' ' . $x, $a, $i), ' ');
    }
    function q($x = null, $deep = false) {
        if (is_int($x) || is_float($x)) {
            return $x;
        } else if (is_string($x)) {
            return extension_loaded('mbstring') ? mb_strlen($x) : strlen($x);
        } else if (is_object($x)) {
            $x = a($x, false);
        }
        return count($x, $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }
    // If `$fn` is a function, the function will be executed after file require.
    // If `$fn` is array of function, the first function will be executed before
    // file require, and the rest will be executed after file require.
    function r(string $a = "", $b = [], $fn = [null, null], array $s__ = []) {
        if (!is_array($fn)) {
            $fn = [null, $fn];
        } else if (!isset($fn[1])) {
            $fn[1] = null;
        }
        if (fn\is\anemon($b)) {
            foreach ($b as $v) {
                if ($s__) extract($s__);
                $v = $a . DS . $v;
                if (is_callable($fn[0])) { // before
                    call_user_func($fn[0], $v, $s__);
                }
                require $v;
                if (is_callable($fn[1])) { // after
                    call_user_func($fn[1], $v, $s__);
                }
            }
        } else {
            foreach (g($a, $b) as $v) {
                if ($s__) extract($s__);
                if (is_callable($fn[0])) { // before
                    call_user_func($fn[0], $v, $s__);
                }
                require $v;
                if (is_callable($fn[1])) { // after
                    call_user_func($fn[1], $v, $s__);
                }
            }
        }
    }
    function s($x = "") {
        if ($x === true) {
            return 'true';
        } else if ($x === false) {
            return 'false';
        } else if ($x === null) {
            return 'null';
        } else if (fn\is\anemon($x)) {
            foreach ($x as &$v) {
                $v = s($v);
            }
            unset($v);
            return $x;
        }
        return (string) $x;
    }
    function t(string $x = "", string $o = '"', $c = null) {
        if ($x) {
            if ($o !== "" && strpos($x, $o) === 0) {
                $x = substr($x, strlen($o));
            }
            $c = $c ?? $o;
            if ($c !== "" && substr($x, $e = -strlen($c)) === $c) {
                $x = substr($x, 0, $e);
            }
        }
        return $x;
    }
    function u(string $x = "") {
        return extension_loaded('mbstring') ? mb_strtoupper($x) : strtoupper($x);
    }
    function v(string $x = "") {
        return stripslashes($x);
    }
    // $c: list of HTML tag name(s) to be excluded from `strip_tags()`
    // $n: @keep line-break in the output or replace them with a space? (default is !@keep)
    function w(string $x = "", $c = [], $n = false) {
        // Should be a HTML input
        if (strpos($x, '<') !== false || strpos($x, ' ') !== false || strpos($x, "\n") !== false) {
            $c = '<' . implode('><', is_string($c) ? explode(',', $c) : (array) $c) . '>';
            return preg_replace($n ? '# +#' : '#\s+#', ' ', trim(strip_tags($x, $c)));
        }
        // [1]. Replace `+` with ` `
        // [2]. Replace `-` with ` `
        // [3]. Replace `---` with ` - `
        // [4]. Replace `--` with `-`
        return preg_replace([
            '#^[._]+|[._]+$#', // remove `.` and `__` prefix/suffix of file name
            '#---#',
            '#--#',
            '#-#',
            '#\s+#',
            '#' . X . '#'
        ], [
            "",
            ' ' . X . ' ',
            X,
            ' ',
            ' ',
            '-'
        ], urldecode($x));
    }
    function x(string $x = "", string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        return addcslashes($x, $d . $c);
    }
    function y($x = null, array $a = []) {
        // By path
        if (is_string($x) && strlen($x) <= 260 && realpath($x) && is_file($x)) {
            ob_start();
            extract($a);
            require $x;
            return ob_get_clean();
        // By function
        } else if (is_callable($x)) {
            ob_start();
            call_user_func($x, ...$a);
            return ob_get_clean();
        }
        return false;
    }
    // $b: use `[]` or `array()` syntax?
    function z(array $a = [], $b = true, $safe = true) {
        $a = json_encode($a);
        $a = preg_split('#("(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\')#', $a, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $s = "";
        foreach ($a as $v) {
            if ($v[0] === '"' && substr($v, -1) === '"') {
                $v = json_decode($v);
                if ($safe) {
                    $v = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $v);
                }
                $s .= "'" . str_replace("'", '\\\'', $v) . "'";
            } else {
                $s .= str_replace(['[', ']', '{', '}', ':', 'true', 'false'], ['{', '}', $b ? '[' : 'array(', $b ? ']' : ')', '=>', '!0', '!1'], $v);
            }
        }
        return $s;
    }
}