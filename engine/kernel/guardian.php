<?php

class Guardian extends Genome {

    const config = [
        'session' => [
            'token' => 'mecha.guardian.token'
        ]
    ];

    public static $config = self::config;

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function check($token, $id = 0, $fail = false) {
        $s = Session::get(self::$config['session']['token'] . '.' . $id);
        return $s && $token && $s === $token ? $token : $fail;
    }

    public static function token($id = 0) {
        $key = self::$config['session']['token'] . '.' . $id;
        $token = Session::get($key, self::hash($id));
        Session::set($key, $token);
        return $token;
    }

    public static function kick($path = null) {
        $current = $GLOBALS['URL']['current'];
        if (!isset($path)) {
            $path = $current;
        }
        Session::set('url.previous', $current);
        $long = URL::long($path);
        $long = Hook::fire(c2f(static::class, '_', '/') . '.' . __FUNCTION__, [$long, $path], new stdClass);
        header('Location: ' . str_replace('&amp;', '&', $long));
        exit;
    }

    public static function abort(string $message, $exit = true) {
        echo error($message);
        if ($exit) exit;
    }

}