<?php

Config::plug('fire', function() {
    $config = State::config();
    if (!$lang = File::exist(LANGUAGE . DS . $config->language . DS . 'speak.txt')) {
        $lang = File::exist(LANGUAGE . DS . 'en-us' . DS . 'speak.txt');
    }
    Config::set('__speak', From::yaml(File::open($lang)->read("")));
    return Config::get();
});

Config::plug('speak', function($key = null, $lot = []) {
    if ($key === null) return Config::get('__speak', []);
});