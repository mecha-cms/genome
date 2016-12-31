<?php

class Minify extends Genome {

    const STR_REGEX = '"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'';
    const COM_CSS_REGEX = '/\*[\s\S]*?\*/';
    const COM_HTML_REGEX = '<!\-{2}[\s\S]*?\-{2}>';
    const COM_JS_REGEX = '//[^\n]*';
    const HTML_REGEX = '<[!/]?[a-zA-Z\d:.-]+.*?>';
    const HTML_X_REGEX = '<pre(?:\s.*?)?>[\s\S]*?</pre>|<code(?:\s.*?)?>[\s\S]*?</code>|<script(?:\s.*?)?>[\s\S]*?</script>|<style(?:\s.*?)?>[\s\S]*?</style>|<textarea(?:\s.*?)?>[\s\S]*?</textarea>';

    public static function CSS($input, $comments = 2) {}

    // 0: remove comment(s)
    // 1: keep comment(s)
    // 2: remove comment(s) except IE comment(s)
    public static function HTML($input, $comments = 2) {
        $pattern = '#(' . self::COM_HTML_REGEX . '|' . self::HTML_X_REGEX . '|' . self::HTML_REGEX . ')#';
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
    }

    public static function JS($input, $comments = 2) {}

}