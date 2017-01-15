<?php

Panel::set('page.types.Markdown', 'Markdown');

Hook::set('shield.before', function() {
    $page = Lot::get('page');
    if ($page[0] && $page[0]->type === 'Markdown') {
        Asset::set(__DIR__ . DS . 'lot' . DS . '__asset' . DS . 'js' . DS . 'panel.code-mirror.min.js', 20);
    }
});