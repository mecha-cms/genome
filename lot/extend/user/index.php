<?php

function fn_user($data) {
    global $config;
    $s = isset($data['author']) ? $data['author'] : $config->page->author;
    if (isset($s) && is_string($s) && strpos($s, User::ID) === 0) {
        $user = new User(substr($s, 1));
        $data['author'] = $user;
        Config::set('page.author', $user);
    }
    return $data;
}

Hook::set(['fire', 'page.input'], 'fn_user', 1);