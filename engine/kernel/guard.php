<?php

final class Guard extends Genome {

    public static function abort(string $message) {
        throw new \Exception($message);
    }

    public static function check(string $token, $id = 0) {
        $previous = $_SESSION['token'][$id];
        return $previous && $token && $previous === $token ? $token : false;
    }

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function kick(string $path = null) {
        $path = $path ?? $GLOBALS['URL']['current'];
        header('Location: ' . str_replace('&amp;', '&', URL::long($path)));
        exit;
    }

    public static function token($id = 0) {
        return ($_SESSION['token'][$id] = self::hash($id));
    }

}