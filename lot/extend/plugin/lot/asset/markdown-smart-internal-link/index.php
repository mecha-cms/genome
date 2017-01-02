<?php

function fn_markdown_smart_internal_link($data) {
    if (!isset($data['content']) || !isset($data['type']) || $data['type'] !== 'Markdown') {
        return $data;
    }
    $content = $data['content'];
    if (strpos($content, '[link:') === false) return $data;
    global $language, $url;
    $links = "";
    $content = preg_replace_callback('#(?:\[(.*?)\])?\[link:((?:\.{2}/)*)([a-z\d/-]+?)([?&\#].*?)?\]#', function($m) use(&$links, $language, $url) {
        // Remove the hook immediately to prevent infinity function nesting level
        // Because `Page::get()` normally will also trigger the `page.input` hook(s)
        Hook::reset('page.input', 'fn_markdown_smart_internal_link');
        $pp = Path::D($url->path);
        if (!empty($m[2]) && ($i = substr_count($m[2], '../')) !== 0) {
            $pp = Path::D($pp, $i);
            $m[2] = str_replace('../', "", $m[2]);
        }
        $pp = $pp ? '/' . $pp : "";
        if (empty($m[2]) && strpos($m[3], '/') === 0) {
            $p = PAGE . $m[3];
        } else {
            $p = PAGE . $pp . '/' . $m[3];
        }
        $m[4] = isset($m[4]) ? $m[4] : "";
        $ff = To::path($p) . '.page';
        if (!file_exists($ff)) {
            return HTML::s('&#x26A0; ' . $language->_message_exist($language->link), [
                'title' => $m[0],
                'css' => ['color' => '#f00']
            ]);
        }
        $tt = Page::open($ff)->get('title', To::title(Path::B($m[2])));
        $ii = md5($m[3] . $m[4]) . '-' . str_replace('.', '-', microtime(true)); // Unique ID
        $links .= "\n" . '[link:' . $ii . ']: ' . $url . $pp . '/' . To::url($m[3]) . $m[4] . ' "' . To::text($tt) . '"';
        if (empty($m[1])) $m[1] = $tt;
        return '[' . $m[1] . '][link:' . $ii . ']';
    }, $content) . "\n" . $links;
    $data['content'] = $content;
    return $data;
}

Hook::set('page.input', 'fn_markdown_smart_internal_link');