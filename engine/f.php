<?php

namespace _\is {
    // Check for valid data collection (array or object)
    function anemon($x, $t = null) {
        if (\is_string($t)) {
            return anemon_a($x);
        }
        if (\is_int($t)) {
            return anemon_0($x);
        }
        return \is_array($x) || \is_object($x);
    }
    // `[1,2,3]`
    function anemon_0($x) {
        $a = (array) $x;
        $count = \count($a);
        return $count && \array_keys($a) === \range(0, $count - 1);
    }
    // `{"a":1,"b":2,"c":3}`
    function anemon_a($x) {
        $a = (array) $x;
        $count = \count($a);
        return $count && \array_keys($a) !== \range(0, $count - 1);
    }
    // Check for any valid class instance
    function instance($x) {
        if (!\is_object($x)) {
            return false;
        }
        return ($s = \get_class($x)) && $s !== 'stdClass' ? $x : false;
    }
    // Check for valid JSON string format
    function json($x, $r = false) {
        if (!\is_string($x) || \trim($x) === "") {
            return false;
        }
        return (
            // Maybe an empty string, array or object
            $x === '""' ||
            $x === '[]' ||
            $x === '{}' ||
            // Maybe an encoded JSON string
            $x[0] === '"' && \substr($x, -1) === '"' ||
            // Maybe a numeric array
            $x[0] === '[' && \substr($x, -1) === ']' ||
            // Maybe an associative array
            $x[0] === '{' && \substr($x, -1) === '}'
        ) && (null !== ($x = \json_decode($x))) ? ($r ? $x : true) : false;
    }
    // Check for valid serialized string format
    function serial($x, $r = false) {
        if (!\is_string($x) || \trim($x) === "") {
            return false;
        }
        if ($x === 'N;') {
            return $r ? \unserialize($x) : true;
        }
        if (\strpos($x, ':') === false) {
            return false;
        }
        if ($x === 'b:1;' || $x === 'b:0;' || $x === 'a:0:{}' || $x === 'O:8:"stdClass":0:{}') {
            return $r ? \unserialize($x) : true;
        }
        return \strpos($x, 'a:') === 0 || \strpos($x, 'O:') === 0 || \strpos($x, 'd:') === 0 || \strpos($x, 'i:') === 0 || \strpos($x, 's:') === 0 ? ($r ? \unserialize($x) : true) : false;
    }
}

namespace {
    // Alter array value(s)
    function alter(array $a, ...$b) {
        // `alter([…], […], […], false)`
        if (\count($b) > 1 && \end($b) === false) {
            \array_pop($b);
            return \array_replace($a, ...$b);
        }
        // `alter([…], […], […])`
        return \array_replace_recursive($a, ...$b);
    }
    // Check if array contains …
    function any(array $a, $fn = null) {
        if (!\is_callable($fn) && $fn !== null) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        foreach ($a as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return true;
            }
        }
        return false;
    }
    // Convert class name to file name
    function c2f(string $s, string $h = '-', string $n = '.') {
        return \ltrim(\str_replace(['\\', $n . $h, '_' . $h], [$n, $n, '_'], h($s, $h, false, '_\\\\')), $h);
    }
    // Get file content
    function content(string $f) {
        return \is_file($f) ? \file_get_contents($f) : null;
    }
    // Merge array value(s)
    function concat(array $a, ...$b) {
        // `concat([…], […], […], false)`
        if (\count($b) > 1 && \end($b) === false) {
            \array_pop($b);
            return \array_merge($a, ...$b);
        }
        // `concat([…], […], […])`
        return \array_merge_recursive($a, ...$b);
    }
    // A equal to B
    function eq($a, $b) {
        return q($a) === $b;
    }
    // Check if file/folder does exist
    function exist($f) {
        if (\is_array($f)) {
            foreach ($f as $v) {
                if ($v && $v = \stream_resolve_include_path($v)) {
                    return $v;
                }
            }
            return false;
        }
        return \stream_resolve_include_path($f);
    }
    // Convert file name to class name
    function f2c(string $s, string $h = '-', string $n = '.') {
        return \str_replace($n, '\\', p(\str_replace([$n, '_'], [$n . $h, '_' . $h], $s), false, $n . '_'));
    }
    // Convert file name to class property name
    function f2p(string $s, string $h = '-', string $n = '.') {
        return c(\str_replace([$n, '_'], [$h . '__', $h . '_'], $s), false, $n . '_');
    }
    // Return the first element found in array that passed the function test
    function find(array $a, callable $fn) {
        foreach ($a as $k => $v) {
            if (\call_user_func($fn, $v, $k)) {
                return $v;
            }
        }
        return null;
    }
    // A greater than or equal to B
    function ge($a, $b) {
        return q($a) >= $b;
    }
    // Get array value recursively
    function get(array &$a, string $k, string $s = '.') {
        $kk = \explode($s, \str_replace("\\" . $s, P, $k));
        foreach ($kk as $v) {
            $v = \str_replace(P, $s, $v);
            if (!\is_array($a) || !\array_key_exists($v, $a)) {
                return null;
            }
            $a =& $a[$v];
        }
        return $a;
    }
    // A greater than B
    function gt($a, $b) {
        return q($a) > $b;
    }
    // Check if an element exists in array
    function has(array $a, string $s = "", string $x = P) {
        return \strpos($x . \implode($x, $a) . $x, $x . $s . $x) !== false;
    }
    // Filter out element(s) that pass the function test
    function is(array $a, $fn = null) {
        if (!\is_callable($fn) && $fn !== null) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return $fn ? \array_filter($a, $fn, \ARRAY_FILTER_USE_BOTH) : \array_filter($a);
    }
    // A less than or equal to B
    function le($a, $b) {
        return q($a) <= $b;
    }
    // Remove array value
    function let(array &$a, string $k, string $s = '.') {
        $kk = \explode($s, \str_replace("\\" . $s, P, $k));
        while (\count($kk) > 1) {
            $k = \str_replace(P, $s, \array_shift($kk));
            if (\array_key_exists($k, $a)) {
                $a =& $a[$k];
            }
        }
        if (\is_array($a) && \array_key_exists($v = \array_shift($kk), $a)) {
            unset($a[$v]);
        }
        return $a;
    }
    // A less than B
    function lt($a, $b) {
        return q($a) < $b;
    }
    // Manipulate array value(s)
    function map(array $a, callable $fn) {
        return \array_map($fn, $a, \array_keys($a));
    }
    // A not equal to B
    function ne($a, $b) {
        return q($a) !== $b;
    }
    // Filter out element(s) that does not pass the function test
    function not(array $a, $fn = null) {
        if (!\is_callable($fn) && $fn !== null) {
            $fn = function($v) use($fn) {
                return $v === $fn;
            };
        }
        return \array_filter($a, function($v, $k) use($fn) {
            return !\call_user_func($fn, $v, $k);
        }, \ARRAY_FILTER_USE_BOTH);
    }
    // Generate new array contains value from the key
    function pluck(array $a, string $k, $alt = null) {
        return \array_filter(\array_map(function($v) use($alt, $k) {
            return $v[$k] ?? $alt;
        }, $a));
    }
    // Convert class property name to file name
    function p2f(string $s, string $h = '-', string $n = '.') {
        return \str_replace('__', $n, h($s, $h, false, '_'));
    }
    // Set array value
    function set(array &$a, string $k, $v = null, string $s = '.') {
        $kk = \explode($s, \str_replace("\\" . $s, P, $k));
        while (\count($kk) > 1) {
            $k = \str_replace(P, $s, \array_shift($kk));
            if (!\array_key_exists($k, $a)) {
                $a[$k] = [];
            }
            $a =& $a[$k];
        }
        $a[\array_shift($kk)] = $v;
        return $a;
    }
    // Shake array
    function shake(array $a, $preserve_key = true) {
        if (\is_callable($preserve_key)) {
            // `$preserve_key` as `$fn`
            $a = \call_user_func($preserve_key, $a);
        } else {
            // <http://php.net/manual/en/function.shuffle.php#94697>
            if ($preserve_key) {
                $k = \array_keys($a);
                $v = [];
                \shuffle($k);
                foreach ($k as $kk) {
                    $v[$kk] = $a[$kk];
                }
                $a = $v;
                unset($k, $v);
            } else {
                \shuffle($a);
            }
        }
        return $a;
    }
    // Get file content line by line
    function stream(string $f, int $c = 1024) {
        if (\is_file($f) && $h = \fopen($f, 'r')) {
            while (false !== ($v = \fgets($h, $c))) {
                yield rtrim($v, "\n\r");
            }
            \fclose($h);
        }
    }
    // Dump PHP code
    function test(...$a) {
        foreach ($a as $b) {
            $s = \var_export($b, true);
            echo '<pre style="word-wrap:break-word;white-space:pre-wrap;background:#fff;color:#000;border:1px solid;padding:.5em;">';
            echo \str_replace(["\n", "\r"], "", \highlight_string("<?php\n\n" . $s . "\n\n?>", true));
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
// i:
// j:
// k: search file in a folder by query
// l: convert text to lower case
// m: normalize range margin
// n: normalize white-space in string
// o: convert array to object
// p: convert text to pascal case
// q: quantity (length of string, number or anemon)
// r: replace string
// s: convert data type to their string format
// t: trim string from specific prefix and suffix
// u: convert text to upper case
// v: un-escape
// w: convert any data to plain word(s)
// x: escape
// y: output/yield an echo-based function as normal return value
// z: export array/object into a compact PHP file

namespace {
    function a($o, $safe = true) {
        if (\is_object($o)) {
            if ($safe) {
                $o = \_\is\instance($o) ? $o : (array) $o;
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
    function b($x, $a = 0, $b = null) {
        if (isset($a) && $x < $a) {
            return $a;
        }
        if (isset($b) && $x > $b) {
            return $b;
        }
        return $x;
    }
    function c(string $x, $a = false, string $i = "") {
        return \str_replace(' ', "", \preg_replace_callback('#([ ' . $i . '])([\p{L}\p{N}' . $i . '])#u', function($m) {
            return $m[1] . u($m[2]);
        }, f($x, $a, $i)));
    }
    function d(string $f, $fn = null) {
        \spl_autoload_register(function($c) use($f, $fn) {
            $n = c2f($c);
            $f .= DS . $n . '.php';
            if (\is_file($f)) {
                extract($GLOBALS, \EXTR_SKIP);
                require $f;
                if (\is_callable($fn)) {
                    \call_user_func($fn, $c, $n);
                }
            }
        });
    }
    function e($x, array $a = []) {
        if (\is_string($x)) {
            if ($x === "") {
                return $x;
            }
            if (\array_key_exists($x, $a = \array_replace([
                'TRUE' => true,
                'FALSE' => false,
                'NULL' => null,
                'true' => true,
                'false' => false,
                'null' => null
            ], $a))) {
                return $a[$x];
            }
            if (\is_numeric($x)) {
                return \strpos($x, '.') !== false ? (float) $x : (int) $x;
            }
            if (false !== ($v = \_\is\json($x, true))) {
                return $v;
            }
            // `"abcdef"` or `'abcdef'`
            if ($x[0] === '"' && \substr($x, -1) === '"' || $x[0] === "'" && \substr($x, -1) === "'") {
                $v = \substr(\substr($x, 1), 0, -1);
                $a = \strpos($v, $x[0]);
                $b = \strpos($v, "\\");
                // `'ab\'cd\'ef'`
                if (
                    $a !== false &&
                    $a === $b + 1 &&
                    \preg_match('#^' . $x[0] . '(?:[^' . $x[0] . '\\\]|\\\.)*' . $x[0] . '$#', $x)
                ) {
                    return \str_replace("\\" . $x[0], $x[0], $v);
                }
                return $v;
            }
            return $x;
        } else if (\is_array($x)) {
            foreach ($x as $k => &$v) {
                $v = e($v, $a);
            }
            unset($v);
        }
        return $x;
    }
    $GLOBALS['F'] = [
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
    ];
    // $x: the string input
    // $a: replace multi-byte string into their accent
    // $i: character(s) white-list
    function f(string $x, $a = true, string $i = "") {
        // this function does not trim white-space at the start and end of the string
        $x = \preg_replace([
            // remove HTML tag(s) and character(s) reference
            '#<[^>]+?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);#i',
            // remove anything except character(s) white-list
            '#[^\p{L}\p{N}\s' . $i . ']#u',
            // convert multiple white-space to single space
            '#\s+#'
        ], ' ', $x);
        return $a && !empty($GLOBALS['F']) ? \strtr($x, $GLOBALS['F']) : $x;
    }
    // Call function with parameter(s) and optional scope
    function fire(callable $fn, array $a = [], $that = null, string $scope = null) {
        $fn = $fn instanceof \Closure ? $fn : \Closure::fromCallable($fn);
        // `fire($fn, [], Foo::class)`
        if (\is_string($that)) {
            $scope = $that;
            $that = null;
        }
        return \call_user_func($fn->bindTo($that, $scope ?? 'static'), ...$a);
    }
    function g(string $f, string $x = null) {
        if (\is_dir($f) && $h = \opendir($f)) {
            while (false !== ($b = \readdir($h))) {
                if ($b !== '.' && $b !== '..') {
                    if (!isset($x) || ($y = \pathinfo($b, \PATHINFO_EXTENSION)) && \strpos(',' . $x . ',', ',' . $y . ',') !== false) {
                        yield $f . DS . $b;
                    }
                }
            }
            \closedir($h);
        }
    }
    function h(string $x, string $h = '-', $a = false, $i = "") {
        return \str_replace([' ', $h . $h], $h, \preg_replace_callback('#\p{Lu}#', function($m) use($h) {
            return $h . l($m[0]);
        }, f($x, $a, x($h) . $i)));
    }
    function i() {}
    function j() {}
    function k(string $f, array $q = [], $c = false) {
        if (\is_dir($f) && $h = \opendir($f)) {
            while (false !== ($b = \readdir($h))) {
                if ($b !== '.' && $b !== '..') {
                    $n = \pathinfo($b, \PATHINFO_FILENAME);
                    foreach ($q as $v) {
                        // Find by query in file name…
                        if (\strpos($n, $v) !== false) {
                            yield $r;
                        // Find by query in file content…
                        } else if ($c && \is_file($r)) {
                            foreach (stream($r) as $s) {
                                foreach ($q as $v) {
                                    if (\strpos($s, $v) !== false) {
                                        yield $r;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            \closedir($h);
        }
    }
    function l(string $x) {
        return \extension_loaded('mbstring') ? \mb_strtolower($x) : \strtolower($x);
    }
    function m($x, array $a, array $b) {
        // <https://stackoverflow.com/a/14224813/1163000>
        return ($x - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
    }
    function n(string $x, string $t = '    ') {
        // <https://stackoverflow.com/a/18870840/1163000>
        $x = \str_replace("\xEF\xBB\xBF", "", $x);
        // Tab to 4 space(s), line-break to `\n`
        return \str_replace(["\t", "\r\n", "\r"], [$t, "\n", "\n"], $x);
    }
    function o($a, $safe = true) {
        if (\is_array($a)) {
            if ($safe) {
                $a = \_\is\anemon_a($a) ? (object) $a : $a;
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
    function p(string $x, $a = false, $i = "") {
        return \ltrim(c(' ' . $x, $a, $i), ' ');
    }
    function q($x) {
        if (\is_int($x) || \is_float($x)) {
            return $x;
        }
        if (\is_string($x)) {
            return \extension_loaded('mbstring') ? \mb_strlen($x) : \strlen($x);
        }
        if ($x instanceof \Traversable) {
            return \iterator_count($x);
        }
        if ($x instanceof \stdClass) {
            return \count((array) $x);
        }
        return \count($x);
    }
    function r($a, $b, string $c) {
        if (\is_string($a) && \is_string($b)) {
            return \strlen($a) === 1 && \strlen($b) === 1 ? \strtr($c, $a, $b) : \strtr($c, [$a => $b]);
        }
        return \strtr($c, \array_combine($a, $b));
    }
    function s($x, array $a = []) {
        if (\is_array($x)) {
            foreach ($x as &$v) {
                $v = s($v, $a);
            }
            unset($v);
            return $x;
        }
        if ($x === true) {
            return $a['true'] ?? 'true';
        }
        if ($x === true) {
            return $a['false'] ?? 'false';
        }
        if ($x === null) {
            return $a['null'] ?? 'null';
        }
        if (\is_object($x)) {
            return \json_encode($x);
        }
        if (\is_string($x)) {
            return $a[$x = (string) $x] ?? $x;
        }
        return $x;
    }
    function step($a, string $s = '.', int $dir = 1) {
        if (\is_string($a) && \strpos($a, $s) !== false) {
            $a = \explode($s, \trim($a, $s));
            $v = $dir === -1 ? \array_pop($a) : \array_shift($a);
            $c = [$v];
            if ($dir === -1) {
                while ($b = \array_pop($a)) {
                    $v = $b . $s . $v;
                    \array_unshift($c, $v);
                }
            } else {
                while ($b = \array_shift($a)) {
                    $v .= $s . $b;
                    \array_unshift($c, $v);
                }
            }
            return $c;
        }
        return (array) $a;
    }
    function t(string $x, string $o = '"', string $c = null) {
        if ($x) {
            if ($o !== "" && \strpos($x, $o) === 0) {
                $x = \substr($x, \strlen($o));
            }
            $c = $c ?? $o;
            if ($c !== "" && \substr($x, $e = -\strlen($c)) === $c) {
                $x = \substr($x, 0, $e);
            }
        }
        return $x;
    }
    function u(string $x) {
        return \extension_loaded('mbstring') ? \mb_strtoupper($x) : \strtoupper($x);
    }
    function v(string $x) {
        return \stripslashes($x);
    }
    // $c: list of HTML tag name(s) to be excluded from `strip_tags()`
    // $n: @keep line-break in the output or replace them with a space? (default is !@keep)
    function w(/*string*/ $x, $c = [], $n = false) {
        // Should be a HTML input
        if (\strpos($x, '<') !== false || \strpos($x, ' ') !== false || \strpos($x, "\n") !== false) {
            $c = '<' . \implode('><', \is_string($c) ? \explode(',', $c) : (array) $c) . '>';
            return \preg_replace($n ? '# +#' : '#\s+#', ' ', \trim(\strip_tags($x, $c)));
        }
        // [1]. Replace `+` with ` `
        // [2]. Replace `-` with ` `
        // [3]. Replace `---` with ` - `
        // [4]. Replace `--` with `-`
        return \preg_replace([
            '#^[._]+|[._]+$#', // remove `.` and `__` prefix/suffix of file name
            '#---#',
            '#--#',
            '#-#',
            '#\s+#',
            '#' . P . '#'
        ], [
            "",
            ' ' . P . ' ',
            P,
            ' ',
            ' ',
            '-'
        ], \urldecode($x));
    }
    function x(string $x, string $c = "'", string $d = '-+*/=:()[]{}<>^$.?!|\\') {
        return \addcslashes($x, $d . $c);
    }
    function y(iterable $a) {
        if (\is_object($a) && $a instanceof \Traversable) {
            return \iterator_to_array($a);
        }
        return $a;
    }
    // $b: use `[]` or `array()` syntax?
    function z($a, $b = true) {
        if (\is_array($a)) {
            $o = [];
            foreach ($a as $k => $v) {
                $o[] = \var_export($k, true) . '=>' . z($v, $b);
            }
            return ($b ? '[' : 'array(') . \implode(',', $o) . ($b ? ']' : ')');
        }
        return \var_export($a, true);
    }
}