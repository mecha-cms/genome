<?php

class Guardian extends Genome {

    const config = [
        'session' => [
            'token' => 'b9645ac7'
        ]
    ];

    public static $config = self::config;

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function check(string $token, $id = 0, $fail = false) {
        $previous = Session::get(self::$config['session']['token'] . '.' . $id);
        return $previous && $token && $previous === $token ? $token : $fail;
    }

    public static function token($id = 0) {
        $token = self::hash($id);
        Session::set(self::$config['session']['token'] . '.' . $id, $token);
        return $token;
    }

    public static function kick(string $path = null) {
        $c = static::class;
        $current = $GLOBALS['URL']['current'];
        $path = $path ?? $current;
        Session::set('url.previous', $current);
        $long = URL::long($path);
        $long = Hook::fire(c2f($c, '_', '/') . '.' . __FUNCTION__, [$long, $path], null, $c);
        header('Location: ' . str_replace('&amp;', '&', $long));
        exit;
    }

    public static function abort(string $message, $exit = true) {
        echo fail($message);
        if ($exit) exit;
    }

}