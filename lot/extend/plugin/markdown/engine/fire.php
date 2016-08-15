<?php

function do_markdown_i(...$a) {
    return t(call_user_func_array('do_markdown_b', $a), '<p>', '</p>');
}

function do_markdown_b($data, $meta) {
    $from = new ParsedownExtraPlugin;
    if (!isset($meta['content_type']) || $meta['content_type'] === 'Markdown') {
        return $from->text($data);
    }
    return $data;
}

Hook::set('title', 'do_markdown_i', 1);
Hook::set('content', 'do_markdown_b', 1);