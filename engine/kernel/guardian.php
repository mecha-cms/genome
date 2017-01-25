<?php

class Guardian extends Genome {

    public static $config = [
        'session' => ['token' => 'Mecha\Guardian\Token']
    ];

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function token() {
        $key = self::$config['session']['token'];
        $token = Session::get($key, self::hash());
        Session::set($key, $token);
        return $token;
    }

    public static function kick($path = null) {
        global $url;
        $current = $url->current;
        if (!isset($path)) {
            $path = $current;
        }
        $long = URL::long($path, false);
        $G = ['source' => $path, 'url' => $long];
        Session::set('url.previous', $current);
        Hook::fire(__c2f__(static::class) . '.kick.before', [null, $G, $G]);
        header('Location: ' . $long);
        exit;
    }

    public static function abort($message, $exit = true) {
        echo '<p>' . $message . '</p>';
        if ($exit) exit;
    }

}