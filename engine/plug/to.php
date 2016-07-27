<?php

// To case: `to case`
To::plug('case', function($input, $cell = "", $no_break = true) {
    // Should be a HTML input
    if(strpos($input, '<') !== false || strpos($input, ' ') !== false) {
        return preg_replace($no_break ? '#\s+#' : '# +#', ' ', trim(strip_tags($input, $cell)));
    }
    // 1. Replace `+` to ` `
    // 2. Replace `-` to ` `
    // 3. Replace `-----` to ` - `
    // 3. Replace `---` to `-`
    return preg_replace(
        [
            '#^(\.|_{2})#', // remove `.` and `__` prefix from a file name
            '#-{5}#',
            '#-{3}#',
            '#-#',
            '#\s+#',
            '#' . X . '#'
        ],
        [
            "",
            ' ' . X . ' ',
            X,
            ' ',
            ' ',
            '-'
        ],
    urldecode($input));
});

// To lower case: `to lower case`
To::plug('case_l', function($input) {
    return function_exists('mb_strtolower') ? mb_strtolower($input) : strtolower($input);
});

// To upper case: `TO UPPER CASE`
To::plug('case_u', function($input) {
    return function_exists('mb_strtoupper') ? mb_strtoupper($input) : strtoupper($input);
});

// To title case: `To Title Case`
To::plug('case_tt', function($input) {
    if (function_exists('mb_strtoupper')) {
        return preg_replace_callback('#(^|[-\s])(\p{Ll})#u', function($m) {
            return $m[1] . mb_strtoupper($m[2]);
        }, To::case($input));
    }
    return ucwords(To::case($input));
});

// To pascal case: `ToPascalCase`
To::plug('case_pl', function($input) {
    return preg_replace_callback('#(^|[^\p{L}])(\p{Ll})#u', function($m) {
        return To::case_u($m[2]);
    }, $input);
});

// To camel case: `toCamelCase`
To::plug('case_cl', function($input) {
    return preg_replace_callback('#([^\p{L}])(\p{Ll})#u', function($m) {
        return To::case_u($m[2]);
    }, $input);
});

// To slug case: `to-slug-case`
To::plug('case_sg', function($input, $s = '-') {
    return __sanitize__(preg_replace_callback('#(.)(\p{Lu})#u', function($m) use($s) {
        return $m[1] . $s . To::case_l($m[2]);
    }, $input), $s, true);
});

// To snake case: `to_snake_case`
To::plug('case_sk', function($input) {
    return To::case_sg($input, '_');
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
To::plug('html_dec', function($input) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $output .= '&#' . ord($input[$i]) . ';';
    }
    return $output;
});

// To HTML hex string
To::plug('html_hex', function($input) {
    $output = "";
    for($i = 0, $count = strlen($input); $i < $count; ++$i) {
        $output .= '&#x' . dechex(ord($input[$i])) . ';';
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

// Array/object to JSON
To::plug('json', function($input) {
    return json_encode($input);
});

// JSON to array
To::plug('anemon', function($input) {
    return (array) json_decode($input, true);
});

// Alias for `To::json`
To::plug('json_x', 'To::json');

// JSON to object
To::plug('json_v', , function($input) {
    return (object) json_decode($input, false);
});


/**
 * Sanitizer ...
 * -------------
 */

// To safe file name
To::safe('name.file', function($input) {
    return __sanitize__($input, '-', true, '\w.');
});

// To safe folder name
To::safe('name.folder', function($input) {
    return __sanitize__($input, '-', true, '\w');
});

// To safe path name
To::safe('name.path', function($input) {
    $x = '-' . DS;
    $s = str_replace(['\\', '/', '\\\\', '//'], [DS, DS, $x, $x], $input);
    return __sanitize__($s, '-', true, '\w.\\\/');
});

// To safe array key
To::safe('key', function($input, $low = true) {
    $s = __sanitize__($input, '_', $low);
    return is_numeric($s[0]) ? preg_replace('#^\d+#', '_', $s) : $s;
});