<?php

class Minify extends Genome {

    const STR_REGEX = '"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'';
    const COM_CSS_REGEX = '/\*[\s\S]*?\*/';
    const COM_HTML_REGEX = '<!\-{2}[\s\S]*?\-{2}>';
    const COM_JS_REGEX = '//[^\n]*';
    const HTML_REGEX = '<[!/]?[a-zA-Z\d:.-]+[\s\S]*?>';
    const HTML_X_REGEX = '<pre(?:\s.*?)?>[\s\S]*?</pre>|<code(?:\s.*?)?>[\s\S]*?</code>|<script(?:\s.*?)?>[\s\S]*?</script>|<style(?:\s.*?)?>[\s\S]*?</style>|<textarea(?:\s.*?)?>[\s\S]*?</textarea>';

}