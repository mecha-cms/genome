<?php

// To case: `to case`
To::plug('case', 'w');

// To lower case: `to lower case`
To::plug('case_l', 'l');

// To upper case: `TO UPPER CASE`
To::plug('case_u', 'u');

// To title case: `To Title Case`
To::plug('case_tt', function($input) {
    if (function_exists('mb_strtoupper')) {
        return preg_replace_callback('#(^|[^a-z\d])(\p{Ll})#u', function($m) {
            return $m[1] . mb_strtoupper($m[2]);
        }, w($input));
    }
    return ucwords(w($input));
});

// To pascal case: `ToPascalCase`
To::plug('case_pl', 'p');

// To camel case: `toCamelCase`
To::plug('case_cl', 'c');

// To slug case: `to-slug-case`
To::plug('case_sg', 'h');

// To snake case: `to_snake_case`
To::plug('case_sk', function($input) {
    return h($input, '_');
});

// To HTML
To::plug('html', function($input) {
    return $input; // do nothing ...
});

// To encoded HTML
To::plug('html_x', function($input) {
    return htmlspecialchars($input);
});

// To decoded HTML
To::plug('html_v', function($input) {
    return htmlspecialchars_decode($input);
});

// To HTML dec string
To::plug('html_dec', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = ord($input[$i]);
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#' . $s . ';';
    }
    return $output;
});

// To HTML hex string
To::plug('html_hex', function($input, $z = false) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '&#x' . $s . ';';
    }
    return $output;
});

// To JS hex string
To::plug('js_hex', function($input, $z = true) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $s = dechex(ord($input[$i]));
        if ($z) $s = str_pad($s, 4, '0', STR_PAD_LEFT);
        $output .= '\\u' . $s;
    }
    return $output;
});

// To encoded URL
To::plug('url_x', function($input) {
    return urlencode($input);
});

// To decoded URL
To::plug('url_v', function($input) {
    return urldecode($input);
});

// To encoded Base64
To::plug('base64_x', function($input) {
    return base64_encode($input);
});

// To decoded Base64
To::plug('base64_v', function($input) {
    return base64_decode($input);
});

// Array/object to JSON
To::plug('json', function($input) {
    return json_encode($input);
});

// JSON to array
To::plug('anemon', function($input) {
    if (__such_anemon__($input)) {
        return a($input);
    }
    return (array) json_decode($input, true);
});

// Alias for `To::json`
To::plug('json_x', 'To::json');

// JSON to object
To::plug('json_v', function($input) {
    if (__such_anemon__($input)) {
        return o($input);
    }
    return (object) json_decode($input, false);
});


/**
 * Sanitizer ...
 * -------------
 */

// To safe file name
To::safe('file.name', function($input) {
    return f($input, '-', true, '\w.');
});

// To safe folder name
To::safe('folder.name', function($input) {
    return f($input, '-', true, '\w');
});

// To safe path name
To::safe('path.name', function($input) {
    $x = '-' . DS;
    $s = str_replace(['\\', '/', '\\\\', '//'], [DS, DS, $x, $x], $input);
    return f($s, '-', true, '\w.\\\/');
});

// To safe array key
To::safe('key', function($input, $low = true) {
    $s = f($input, '_', $low);
    return is_numeric($s[0]) ? preg_replace('#^\d+#', '_', $s) : $s;
});