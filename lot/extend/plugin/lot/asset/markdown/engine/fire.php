<?php

function do_markdown_parse(&$input) {
    $slot = new ParsedownExtraPlugin;
    $slot->links = [];
    $input = $slot->text($input);
}

From::plug('markdown', function($input) {
    do_markdown_parse($input);
    return $input;
});

To::plug('markdown', function($input) {
    return $input; // TODO
});

function do_markdown_parse_i(&$input) {
    do_markdown_parse($input);
    $input = t($input, '<p>', '</p>');
}

function do_markdown($data) {
    if ($data['type'] === 'Markdown') {
        do_markdown_parse_i($data['title']);
        do_markdown_parse($data['description']);
        do_markdown_parse($data['content']);
    }
    return $data;
}

Hook::set('page.output', 'do_markdown', 1);