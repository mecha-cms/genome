<?php

// _is_anemon_: check for valid data collection (array or object)
// _is_json_: check for valid JSON string format
// _is_serialize_: check for valid serialized string format

function _is_anemon_($x) {
    return is_array($x) || is_object($x);
}

function _is_anemon_a_($x) {
    $a = (array) $x;
    $count = count($a);
    return $count && array_keys($a) !== range(0, $count - 1);
}

function _is_json_($x) {
    if (!is_string($x) || !trim($x)) return false;
    return (
        // Maybe an empty string, array or object
        $x === '""' ||
        $x === '[]' ||
        $x === '{}' ||
        // Maybe an encoded JSON string
        $x[0] === '"' ||
        // Maybe a flat array
        $x[0] === '[' ||
        // Maybe an associative array
        $x[0] === '{'
    ) && json_decode($x) !== null;
}

function _is_serialize_($x) {
    if (!is_string($x) || !trim($x)) {
        return false;
    } elseif ($x === 'N;') {
        return true;
    } elseif (strpos($x, ':') === false) {
        return false;
    } elseif ($x === 'b:1;' || $x === 'b:0;' || $x === 'a:0:{}' || $x === 'O:8:"stdClass":0:{}') {
        return true;
    }
    return strpos($x, 'a:') === 0 || strpos($x, 'O:') === 0 || strpos($x, 'd:') === 0 || strpos($x, 'i:') === 0 || strpos($x, 's:') === 0;
}

function _dump_(...$a) {
    foreach ($a as $b) {
        $s = var_export($b, true);
        $s = str_ireplace(['array (', 'TRUE', 'FALSE', 'NULL'], ['array(', 'true', 'false', 'null'], $s);
        $s = preg_replace('#[,;](\s*[\)\}])#', '$1', $s);
        echo '<pre style="word-wrap:break-word;white-space:pre-wrap;">';
        highlight_string("<?php\n\n" . $s . "\n\n?>");
        echo '</pre>';
    }
}

function _c2f_($x) {
    return str_replace('\\-', '.', h($x, '-', '\\'));
}

function _url_($key = null, $fail = false) {
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
    $protocol = $scheme . '://';
    $host = $_SERVER['HTTP_HOST'];
    $sub = str_replace(DS, '/', dirname($_SERVER['SCRIPT_NAME']));
    $sub = $sub === '.' ? "" : trim($sub, '/');
    $url = rtrim($protocol . $host  . '/' . $sub, '/');
    $s = preg_replace('#[<>"]|[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/')); // Remove HTML tag(s) and query string(s) from URL
    $path = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $sub . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $s;
    if ($path !== "") {
        array_shift($_GET);
    }
    $query = http_build_query($_GET);
    $current = rtrim($url . '/' . $path, '/');
    $a = [
        'scheme' => $scheme,
        'protocol' => $protocol,
        'host' => $host,
        'port' => (int) $_SERVER['SERVER_PORT'],
        'user' => null,
        'pass' => null,
        'sub' => $sub,
        'url' => $url,
        'path' => $path,
        'query' => $query ? '?' . $query : "",
        'previous' => $_SESSION['url']['previous'] ?? null,
        'current' => $current,
        'next' => null,
        'hash' => null
    ];
    return $key !== null ? ($a[$key] ?? $fail) : $a;
}

// a: convert object to array
// b:
// c: convert text to camel case
// d: declare class(es)
// e: evaluate string to their proper data type
// f: filter/sanitize string
// g:
// h: convert text to snake case with `-` (hyphen) as separator by default
// i: include file(s)
// j:
// k:
// l: convert text to lower case
// m: trim string from character(s)
// n: normalize white-space in string
// o: convert array to object
// p: convert text to pascal case
// q: quantity (length of string, number or anemon)
// r: require file(s)
// s: convert data type to their string format
// t: convert N to a tab
// u: convert text to upper case
// v: un-escape
// w: convert any data to plain word(s)
// x: escape
// y: output/yield an echo-based function as normal return value
// z:

function a($o) {
    if (_is_anemon_($o)) {
        $o = (array) $o;
        foreach ($o as &$oo) {
            $oo = a($oo);
        }
        unset($oo);
    }
    return $o;
}

function b() {}

function c($x) {
    return preg_replace_callback('#([^\p{L}])(\p{Ll})#u', function($m) {
        return u($m[2]);
    }, $x);
}

function d($f, $fn = null) {
    spl_autoload_register(function($w) use($f, $fn) {
        $n = _c2f_($w);
        $f = $f . DS . $n . '.php';
        if (file_exists($f)) {
            require $f;
            if (is_callable($fn)) {
                call_user_func($fn, $w, $n);
            }
        }
    });
}

function e($x) {
    if (is_string($x)) {
        if ($x === "") return $x;
        if (is_numeric($x)) {
            return strpos($x, '.') !== false ? (float) $x : (int) $x;
        } elseif (_is_json_($x) && $v = json_decode($x, true)) {
            return $v;
        } elseif ($x[0] === '"' && substr($x, -1) === '"' || $x[0] === "'" && substr($x, -1) === "'") {
            return m($x, $x[0]);
        }
        $xx = [
            'TRUE' => true,
            'FALSE' => false,
            'NULL' => null,
            'true' => true,
            'false' => false,
            'null' => null,
            'yes' => true,
            'no' => false,
            'on' => true,
            'off' => false
        ];
        return array_key_exists($x, $xx) ? $xx[$x] : $x;
    } elseif (_is_anemon_($x)) {
        foreach ($x as &$v) {
            $v = e($v);
        }
        unset($v);
    }
    return $x;
}

function f($x, $s = '-', $l = false, $X = 'a-zA-Z\d', $f = 1) {
    $sx = x($s, '#');
    $X .= $sx;
    $x = preg_replace([
        '#<.*?>|&(?:[a-z\d]+|\#\d+|\#x[a-f\d]+);#i',
        '#[^' . $X . ']#',
        '#(' . $sx . ')+#',
        '#^' . $sx . '|' . $sx . '$#'
    ], [
        $s,
        $s,
        $s,
        ""
    ], $f === 1 ? strtr($x, [
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
        'Ъ' => '',
        'Ь' => '',
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
        'ъ' => '',
        'ь' => '',
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
    ]) : $x);
    return $x ? ($l ? l($x) : $x) : $s . $s;
}

function g() {}

function h($x, $s = '-', $X = "") {
    return f(preg_replace_callback('#(?<=[\p{Ll}\D])(\p{Lu})#u', function($m) use($s) {
        return $s . l($m[1]);
    }, $x), $s, true, 'a-zA-Z\d' . $X);
}

function i($a, $b = [], $fn = null) {
    if (_is_anemon_($b)) {
        foreach ($b as $v) {
            $v = $a . DS . $v;
            include $v;
            if (is_callable($fn)) {
                call_user_func($fn, $v);
            }
        }
    } else {
        $f = strpos($b, '{') !== false ? GLOB_NOSORT | GLOB_BRACE : GLOB_NOSORT;
        foreach (glob($a . DS . $b, $f) as $v) {
            include $v;
            if (is_callable($fn)) {
                call_user_func($fn, $v);
            }
        }
    }
}

function j() {}
function k() {}

function l($x) {
    return function_exists('mb_strtolower') ? mb_strtolower($x) : strtolower($x);
}

function m($x, $o = '"', $c = null) {
    $c = $c ?? $o;
    if ($x && strpos($x, $o) === 0 && substr($x, -strlen($c)) === $c) {
        return substr(substr($x, strlen($o)), 0, -strlen($c));
    }
    return $x;
}

function n($x, $t = I) {
    // Tab to 2 space(s), line-break to `\n`
    return str_replace(["\t", "\r\n", "\r"], [$t, N, N], $x);
}

function o($a, $safe = true) {
    if (_is_anemon_($a)) {
        $a = $safe && _is_anemon_a_($a) ? (object) $a : $a;
        foreach ($a as &$aa) {
            $aa = o($aa, $safe);
        }
        unset($aa);
    }
    return $a;
}

function p($x) {
    return preg_replace_callback('#(^|[^\p{L}])(\p{Ll})#u', function($m) {
        return u($m[2]);
    }, $x);
}

function q($x, $deep = false) {
    if (is_int($x) || is_float($x)) {
        return $x;
    } elseif (is_string($x)) {
        return function_exists('mb_strlen') ? mb_strlen($x) : strlen($x);
    } elseif (_is_anemon_($x)) {
        return count(a($x), $deep ? COUNT_RECURSIVE : COUNT_NORMAL);
    }
    return count($x);
}

function r($a, $b = [], $fn = null) {
    if (_is_anemon_($b)) {
        foreach ($b as $v) {
            $v = $a . DS . $v;
            require $v;
            if (is_callable($fn)) {
                call_user_func($fn, $v);
            }
        }
    } else {
        $f = strpos($b, '{') !== false ? GLOB_NOSORT | GLOB_BRACE : GLOB_NOSORT;
        foreach (glob($a . DS . $b, $f) as $v) {
            require $v;
            if (is_callable($fn)) {
                call_user_func($fn, $v);
            }
        }
    }
}

function s($x) {
    if ($x === true) {
        return 'true';
    } elseif ($x === false) {
        return 'false';
    } elseif ($x === null) {
        return 'null';
    } elseif (_is_anemon_($x)) {
        foreach ($x as &$v) {
            $v = s($v);
        }
        unset($v);
        return $x;
    }
    return (string) $x;
}

function t($x, $t = I) {
    return str_replace($t, T, $x);
}

function u($x) {
    return function_exists('mb_strtoupper') ? mb_strtoupper($x) : strtoupper($x);
}

function v($x) {
    return stripslashes($x);
}

// w.c: list of HTML tag name(s) to be excluded from `strip_tags()`
// w.n: @keep line-break in the output or replace them with a space? (default is !@keep)
function w($x, $c = [], $n = true) {
    // Should be a HTML input
    if(strpos($x, '<') !== false || strpos($x, ' ') !== false) {
        $c = '<' . implode('><', $c) . '>';
        return preg_replace($n ? '#\s+#' : '# +#', ' ', trim(strip_tags($x, $c)));
    }
    // 1. Replace `+` to ` `
    // 2. Replace `-` to ` `
    // 3. Replace `-----` to ` - `
    // 4. Replace `---` to `-`
    return preg_replace([
        '#^(\.|_{2})|(\.|_{2})$#', // remove `.` and `__` prefix/suffix in a file name
        '#-{5}#',
        '#-{3}#',
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

function x($x, $c = "'", $d = '-+*/=:()[]{}<>^$.?!|\\') {
    return addcslashes($x, $d . $c);
}

function y($x, $a = []) {
    if (is_callable($x)) {
        ob_start();
        call_user_func_array($x, $a);
        return ob_get_clean();
    }
    return $x;
}

function z() {}


// ...


d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['file_extension_allow'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Session::start();
Config::start();

$seed = [
    'config' => new Genome\Config,
    'language' => new Genome\Language,
    'url' => new Genome\URL
];

// plant and extract ...
extract(Seed::set($seed)->get(null, []));

r(EXTEND . DS . '*', '{index.php,index__.php,__index.php}', function($f) use($config) {
    $i18n = Path::D($f) . DS . 'lot' . DS . 'language';
    if (!$l = File::exist($i18n . DS . $config->language . '.txt')) {
        $l = $i18n . DS . 'en-us.txt';
    }
    Language::set(From::yaml(File::open($l)->read("")));
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract(Seed::get(null, []));
            require $f;
        }
    });
});

r(SHIELD . DS . $config->shield, '{index.php,index__.php,__index.php}', function($f) {
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract(Seed::get(null, []));
            require $f;
        }
    });
});

function do_hook_start() {
    Route::fire();
    Shield::abort();
}

Hook::set('start', 'do_hook_start')->fire('start');