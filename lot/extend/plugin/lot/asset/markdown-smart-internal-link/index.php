<?php

function fn_markdown_smart_internal_link($data) {
    if (!isset($data['content']) || !isset($data['type']) || $data['type'] !== 'Markdown') {
        return $data;
    }
    $content = $data['content'];
    if (strpos($content, '[link:') === false) return $data;
    global $url;
    $links = "";
    $content = preg_replace_callback('#(?:\[(.*?)\])?\[link:([a-z\d/-]+?)([?&\#].*?)?\]#', function($m) use(&$links, $url) {
        $pp = Path::D($url->path);
        $pp = $pp ? '/' . $pp : "";
        if (strpos($m[2], '/') === 0) {
            $p = PAGE . $m[2];
        } else {
            $p = PAGE . $pp . '/' . $m[2];
        }
        $m[3] = isset($m[3]) ? $m[3] : "";
        $tt = Page::open(To::path($p) . '.page')->get('title', To::title(Path::B($m[2])));
        $links .= "\n" . '[link:' . $m[2] . ']: ' . $url . $pp . '/' . To::url($m[2]) . $m[3] . ' "' . To::text($tt) . '"';
        if (empty($m[1])) {
            return '[' . $tt . '][link:' . $m[2] . $m[3] . ']';
        }
        return $m[0];
    }, $content) . "\n" . $links;
    $data['content'] = $content;
    return $data;
}

Hook::set('page.input', 'fn_markdown_smart_internal_link');