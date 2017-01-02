<?php

$state = Plugin::state(__DIR__);

Lot::set([
    'state' => $state,
    'parser' => new ParsedownExtraPlugin
], __DIR__);

function fn_markdown_parse(&$input, $parser) {
    extract(Lot::get(null, [], __DIR__));
    foreach ($state as $k => $v) {
        $parser->{$k} = $v;
    }
    $input = $parser->text($input);
}

function fn_markdown($data) {
    extract(Lot::get(null, [], __DIR__));
    if (isset($data['type']) && $data['type'] === 'Markdown') {
        fn_markdown_parse($data['title'], $parser);
        fn_markdown_parse($data['description'], $parser);
        fn_markdown_parse($data['content'], $parser);
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