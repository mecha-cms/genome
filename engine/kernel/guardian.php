<?php

class Guardian extends Genome {

    public static $config = [
        'session' => ['token' => 'mecha.guardian.token']
    ];

    public static function hash($salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function kick($path = "") {
        $url = URL::long($path, false);
        $G = ['source' => $path, 'url' => $url];
        Session::set('url.previous', __url__('current'));
        Hook::fire(strtolower(static::class) . '.kick.before', [null, $G, $G]);
        header('Location: ' . $url);
        exit;
    }

    public static function abort($message, $exit = true) {
        echo '<p>' . $message . '</p>';
        if ($exit) exit;
    }

}