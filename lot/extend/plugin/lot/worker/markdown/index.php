<?php

$state = Plugin::state(__DIR__);

Lot::set([
    'state' => $state,
    'slot' => new ParsedownExtraPlugin
], __DIR__);

function fn_markdown_parse(&$input) {
    extract(Lot::get(null, [], __DIR__));
    foreach ($state as $k => $v) {
        $slot->{$k} = $v;
    }
    $input = $slot->text($input);
}

function fn_markdown($data) {
    if (isset($data['type']) && $data['type'] === 'Markdown') {
        fn_markdown_parse($data['title']);
        fn_markdown_parse($data['description']);
        fn_markdown_parse($data['content']);
        $data['title'] = t($data['title'], '<p>', '</p>');
    }
    return $data;
}

From::plug('markdown', function($input) {
    return fn_markdown(['content' => $input])['content'];
});

To::plug('markdown', function($input) {
    return $input; // TODO
});

Hook::set('page.output', 'fn_markdown', 1);