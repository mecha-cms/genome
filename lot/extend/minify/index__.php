<?php

$c = include __DIR__ . DS . 'lot' . DS . 'state' . DS . 'config.php';

Hook::set('shield.output', function($content) {
    return Minify::html($content, $c['html']);
});