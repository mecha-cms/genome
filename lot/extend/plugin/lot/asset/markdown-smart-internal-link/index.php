<?php

function do_markdown_smart_internal_link($content) {
    if (strpos($content, '[link:') === false) return $content;
    global $url;
    return preg_replace_callback('#(?:\[(.*?)\])?\[link:([a-z\d/-]+?)([?&\#].*?)?\]#', function($m) use($url) {
        if (strpos($m[2], '/') === 0) {
            $m_2 = PAGE . $m[2];
        } else {
            $m_2 = Path::D($url->path) . '/' . $m[2];
        }
        $tt = Page::open(To::path($m_2))->get('title', To::title(Path::B($m_2)));
        return '<a class="auto-link" href="' . Path::D($url->current) . '/' . To::url($m[2]) . '" title="' . $tt . '">' . ($m[1] ?: $tt) . '</a>';
    }, $content);
}

Hook::set('page.output', function($data) {
    if (!isset($data['content']) || !isset($data['type']) || $data['type'] !== 'Markdown') {
        return $data;
    }
    $content = $data['content'];
    if (strpos($content, '[link:') === false) return $data;
    $parts = preg_split('#(<pre(?:\s.*?)?>[\s\S]*?</pre>|<code(?:\s.*?)?>[\s\S]*?</code>|<script(?:\s.*?)?>[\s\S]*?</script>|<style(?:\s.*?)?>[\s\S]*?</style>|<textarea(?:\s.*?)?>[\s\S]*?</textarea>|<[!/]?[a-zA-Z\d:.-]+[\s\S]*?>)#', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $output = "";
    foreach ($parts as $part) {
        if (!$part) continue;
        if ($part[0] === '<' && substr($part, -1) === '>') {
            $output .= $part;
        } else {
            $output .= do_markdown_smart_internal_link($part);
        }
    }
    $data['content'] = $output;
    return $data;
});