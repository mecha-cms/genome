<?php

Hook::set('shield.output', function($content) {
    $c = include __DIR__ . DS . 'lot' . DS . 'state' . DS . 'config.php';
    return Minify::html($content, $c[0]);
});