<?php

Hook::set('content', function($data, $meta) {
    $parser = new ParsedownExtraPlugin;
    if (!isset($meta['content_type']) || $meta['content_type'] === 'Markdown') {
        return $parser->text($data);
    }
    return $data;
});