<?php

Hook::set('fire', function() use($config) {
    $s = $config->page->author;
    if (is_string($s) && strpos($s, User::ID) === 0) {
        Config::set('page.author', new User(substr($s, 1)));
    }
}, 1);

Hook::set('page.input', function($data) {
    if (isset($data['author']) && is_string($data['author']) && strpos($data['author'], User::ID) === 0) {
        $data['author'] = new User(substr($data['author'], 1));
    }
    return $data;
}, 1);