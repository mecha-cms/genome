<?php

function fn_markdown_replace($in, $mode = 'text') {
    $x = new ParsedownExtraPlugin;
    foreach (Plugin::state('markdown') as $k => $v) {
        $x->{$k} = $v;
    }
    return $x->{$mode}($in);
}

function fn_markdown($in = "", $lot = [], $that) {
    if ($that->get('type') !== 'Markdown') {
        return $in;
    }
    return fn_markdown_replace($in);
}

function fn_markdown_span($in, $lot = [], $that) {
    if ($that->get('type') !== 'Markdown') {
        return $in;
    }
    return w(fn_markdown_replace($in), HTML_WISE_I);
    // return fn_markdown_replace($in, 'line'); // TODO
}

From::_('Markdown', function($in, $span = false) {
    return fn_markdown_replace($in, $span ? 'span' : 'text');
});

To::_('Markdown', function($in) {
    return $in; // TODO
});

// Alias(es)
From::_('markdown', From::_('Markdown'));
To::_('markdown', To::_('Markdown'));

Hook::set('*.title', 'fn_markdown_span', 2);
Hook::set(['*.description', '*.content'], 'fn_markdown', 2);

// Add `markdown` to the allowed file extension(s)
File::$config['extension'] = array_merge(File::$config['extension'], [
    'markdown',
    'md',
    'mkd'
]);