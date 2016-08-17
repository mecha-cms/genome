<?php

function do_markdown_proto() {
    $slot = new ParsedownExtraPlugin;
    $slot->links = [];
    return $slot;
}

From::plug('markdown', function($input) {
    return do_markdown_proto()->text($input);
});

To::plug('markdown', function($input) {
    return $input; // TODO
});

function do_markdown_i(...$a) {
    return t(call_user_func_array('do_markdown_b', $a), '<p>', '</p>');
}

function do_markdown_b($data, $meta) {
    if (!isset($meta['content_type']) || $meta['content_type'] === 'Markdown') {
        return do_markdown_proto()->text($data);
    }
    return $data;
}

Hook::set('title', 'do_markdown_i', 1);
Hook::set('content', 'do_markdown_b', 1);