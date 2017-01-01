<?php

$slot = new ParsedownExtraPlugin;

fn::plug('markdown_parse', function($input) use($slot) {
    $c = include __DIR__ . DS . 'lot' . DS . 'state' . DS . 'config.php';
    foreach ($c as $cc => $ccc) {
        $slot->{$cc} = $ccc;
    }
    return $slot->text($input);
});

fn::plug('markdown_parse_i', function($input) {
    $input = fn::markdown_parse($input);
    return t($input, '<p>', '</p>');
});

fn::plug('markdown', function($data) {
    if (isset($data['type']) && $data['type'] === 'Markdown') {
        $data['title'] = fn::markdown_parse_i($data['title']);
        $data['description'] = fn::markdown_parse($data['description']);
        $data['content'] = fn::markdown_parse($data['content']);
    }
    return $data;
});

From::plug('markdown', function($input) {
    fn::markdown_parse($input);
    return $input;
});

To::plug('markdown', function($input) {
    return $input; // TODO
});

Hook::set('page.output', 'fn::markdown', 1);