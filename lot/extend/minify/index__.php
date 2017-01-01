<?php

$c = include __DIR__ . DS . 'lot' . DS . 'state' . DS . 'config.php';

Hook::set('shield.output', function($content) use($c) {
    // Minify embedded CSS
    if (strpos($content, '</style>') !== false) {
        $content = preg_replace_callback('#<style(\s.*?)?([\s\S]*?)<\/style>#i', function($m) use($c) {
            array_unshift($c['css'], $m[2]);
            return '<style' . $m[1] . call_user_func_array('Minify::css', $c['css']) . '</style>';
        }, $content);
    }
    // Minify HTML
    array_unshift($c['html'], $content);
    $content = call_user_func_array('Minify::html', $c['html']);
    return $content;
});