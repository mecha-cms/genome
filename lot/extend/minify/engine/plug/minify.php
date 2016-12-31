<?php

// 0: remove comment(s)
// 1: keep comment(s)
// 2: remove comment(s) except special comment(s)

Minify::plug('css', function($input, $comments = 2) {});

Minify::plug('html', function($input, $comments = 2) {
    $pattern = '#(' . Minify::COM_HTML_REGEX . '|' . Minify::HTML_X_REGEX . '|' . Minify::HTML_REGEX . ')#';
    $parts = preg_split($pattern, $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach ($parts as $part) {
        if ($part !== ' ' && !trim($part) || $comments !== 1 && strpos($part, '<!--') === 0) {
            if ($comments === 2 && substr($part, -12) === '<![endif]-->') {
                $output .= $part;
            }
            continue;
        }
        $output .= $part[0] === '<' && substr($part, -1) === '>' ? $part : preg_replace('#\s+#', ' ', $part);
    }
    return $output;
});

Minify::plug('js', function($input, $comments = 2) {});