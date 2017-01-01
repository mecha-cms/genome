<?php

class Minify extends Genome {

    const STRING = '"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'';

    const CSS_COMMENT = '/\*[\s\S]*?\*/';
    const HTML_COMMENT = '<!\-{2}[\s\S]*?\-{2}>';
    const JS_COMMENT = '//[^\n]*';

    const JS_PATTERN = "";

    const HTML = '<[!/]?[a-zA-Z\d:.-]+[\s\S]*?>';
    const HTML_KEEP = '<pre(?:\s.*?)?>[\s\S]*?</pre>|<code(?:\s.*?)?>[\s\S]*?</code>|<script(?:\s.*?)?>[\s\S]*?</script>|<style(?:\s.*?)?>[\s\S]*?</style>|<textarea(?:\s.*?)?>[\s\S]*?</textarea>';

}