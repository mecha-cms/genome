<?php

$state = Plugin::state(__DIR__);

Lot::set([
    'state' => $state,
    'x' => new ParsedownExtraPlugin
], __DIR__);

function fn_markdown($input, $lot) {
    if (!isset($lot['type']) || $lot['type'] !== 'Markdown') {
        return $input;
    }
    extract(Lot::get(null, [], __DIR__));
    foreach ($state as $k => $v) {
        $x->{$k} = $v;
    }
    return $x->text($input);
}

function fn_markdown_span($input, $lot = []) {
    if (!isset($lot)) return $input;
    return t(fn_markdown($input, $lot), '<p>', '</p>');
}

From::plug('markdown', function($input) {
    return fn_markdown($input, ['type' => 'Markdown']);
});

To::plug('markdown', function($input) {
    return $input; // TODO
});

Hook::set('page.title', 'fn_markdown_span', 1);
Hook::set(['page.description', 'page.content'], 'fn_markdown', 1);