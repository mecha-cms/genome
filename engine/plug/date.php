<?php

Hook::set('on.ready', function($out = "") {
    $key = str_replace('-', '_', Config::get('language'));
    if (!Date::get($key)) {
        Date::set($key, function($out) {
            return $out['en_us'];
        });
    }
    return $out;
}, 0);