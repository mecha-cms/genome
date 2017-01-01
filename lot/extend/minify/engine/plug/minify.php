<?php


/**
 * 0: remove comment(s)
 * 1: keep comment(s)
 * 2: remove comment(s) except special comment(s)
 */

function fn_minify_html_union($input) {
    if (
        strpos($input, ' ') === false &&
        strpos($input, "\n") === false &&
        strpos($input, "\t") === false
    ) return $input;
    return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
        if (isset($m[2])) {
            // Minify inline CSS declaration(s)
            if (stripos($m[2], ' style=') !== false) {
                $m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i', function($m) {
                    return $m[1] . $m[2] . fn_minify_css($m[3]) . $m[2];
                }, $m[2]);
            }
            $attr = 'a(sync|uto(focus|play))|c(hecked|ontrols)|d(efer|isabled)|hidden|ismap|loop|multiple|open|re(adonly|quired)|s((cop|elect)ed|pellcheck)';
            return '<' . $m[1] . preg_replace([
                // From `a="a"`, `a='a'`, `a="true"`, `a='true'`, `a=""` and `a=''` to `a` [^1]
                '#\s(' . $attr . ')(?:=([\'"]?)(?:true|\1)?\2)#i',
                // Remove extra white–space(s) between HTML attribute(s) [^2]
                '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
                // From `<img />` to `<img/>` [^3]
                '#\s+\/$#'
            ], [
                // [^1]
                ' $1',
                // [^2]
                ' $1$2',
                // [^3]
                '/'
            ], str_replace("\n", ' ', $m[2])) . '>';
        }
        return '<' . $m[1] . '>';
    }, $input);
}

function fn_minify_css_union($input) {
    if (stripos($input, 'calc(') !== false) {
        // Keep important white–space(s) in `calc()`
        $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function($m) {
            return $m[1] . preg_replace('#\s+#', X, $m[2]) . ')';
        }, $input);
    }
    $input = preg_replace([
        // Fix case for `#foo<space>[bar="baz"]` and `#foo<space>:first-child` [^1]
        '#(?<=[\w])\s+(\[|:[\w-]+)#',
        // Fix case for `[bar="baz"]<space>.foo` and `@media<space>(foo: bar)<space>and<space>(baz: qux)` [^2]
        '#\]\s+(?=[\w\#.])#', '#\b\s+\(#', '#\)\s+\b#',
        // Minify HEX color code … [^3]
        '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
        // Remove white–space(s) around punctuation(s) [^4]
        '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
        // Replace zero unit(s) with `0` [^5]
        '#\b(?:0\.)?0([a-z]+\b|%)#i',
        // Replace `0.6` with `.6` [^6]
        '#\b0+\.(\d+)#',
        // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
        '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
        // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
        '#\b(background(?:-position)?):(0|none)\b#i',
        // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
        '#\b(border(?:-radius)?|outline):none\b#i',
        // Remove empty selector(s) [^10]
        '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
        // Remove the last semi–colon and replace multiple semi–colon(s) with a semi–colon [^11]
        '#;+([;\}])#',
        // Replace multiple white–space(s) with a space [^12]
        '#\s+#'
    ], [
        // [^1]
        X . '$1',
        // [^2]
        ']' . X, X . '(', ')' . X,
        // [^3]
        '#$1$2$3',
        // [^4]
        '$1',
        // [^5]
        '0',
        // [^6]
        '.$1',
        // [^7]
        ':0',
        // [^8]
        '$1:0 0',
        // [^9]
        '$1:0',
        // [^10]
        '$1',
        // [^11]
        '$1',
        // [^12]
        ' '
    ], $input);
    return trim(str_replace(X, ' ', $input));
}

function fn_minify_css($input, $comments = 2) {
    if (!$input = trim($input)) return $input;
    $pattern = '#(' . Minify::CSS_COMMENT . '|' . Minify::STRING . ')#';
    $parts = preg_split($pattern, $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach ($parts as $part) {
        if (!trim($part)) continue;
        if ($comments !== 1 && strpos($part, '/*') === 0 && substr($part, -2) === '*/') {
            if ($comments === 2 && strpos('*!', $part[2]) !== false) {
                $output .= $part;
            }
            continue;
        }
        if ($part[0] === '"' && substr($part, -1) === '"' || $part[0] === "'" && substr($part, -1) === "'") {
            $output .= $part;
        } else {
            $output .= fn_minify_css_union($part);
        }
    }
    // Remove quote(s) where possible …
    $output = preg_replace([
        '#(' . Minify::CSS_COMMENT . ')|(?<!\bcontent\:|[\s\(])([\'"])([a-z_][-\w]*?)\2#i',
        '#(' . Minify::CSS_COMMENT . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
    ], [
        '$1$3',
        '$1$2$4$5'
    ], $output);
    return trim($output);
}

function fn_minify_html($input, $comments = 2) {
    if (!$input = trim($input)) return $input;
    $pattern = '#(' . Minify::HTML_COMMENT . '|' . Minify::HTML_KEEP . '|' . Minify::HTML . ')#';
    $parts = preg_split($pattern, $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach ($parts as $part) {
        if ($part !== ' ' && !trim($part) || $comments !== 1 && strpos($part, '<!--') === 0) {
            if ($comments === 2 && substr($part, -12) === '<![endif]-->') {
                $output .= $part;
            }
            continue;
        }
        $output .= $part[0] === '<' && substr($part, -1) === '>' ? fn_minify_html_union($part) : preg_replace('#\s+#', ' ', $part);
    }
    return $output;
}

function fn_minify_js($input, $comments = 2) {
    if (!$input = trim($input)) return $input;
    $output = $input;
    return $output;
}

Minify::plug('css', 'fn_minify_css');
Minify::plug('html', 'fn_minify_html');
// Minify::plug('js', 'fn_minify_js');