<?php

Guardian::plug('token', function() {
    $key = Guardian::$config['session']['token'];
    $token = Session::get($key, Guardian::hash());
    Session::set($key, $token);
    return $token;
});