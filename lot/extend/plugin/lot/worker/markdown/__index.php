<?php

Panel::set('page.types.Markdown', 'Markdown');

Hook::set('shield.before', function() {
    if ($__page = Lot::get('__page')) {
        if ($__page[0] && $__page[0]->type === 'Markdown') {
            Asset::set(__DIR__ . DS . 'lot' . DS . '__asset' . DS . 'js' . DS . 'panel.code-mirror.min.js', 20);
        }
    }
});