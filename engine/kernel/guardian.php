<?php

final class Guardian extends Genome {

    const session = 'guardian';

    public static function abort(string $message) {
        throw new \Exception($message);
    }

    public static function check(string $token, $id = 0) {
        $previous = Session::get(self::session . '.token.' . $id);
        return $previous && $token && $previous === $token ? $token : false;
    }

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function kick(string $path = null) {
        $c = static::class;
        $current = $GLOBALS['URL']['current'];
        $path = $path ?? $current;
        Session::set(URL::session . '.previous', $current);
        $long = URL::long($path);
        $long = Hook::fire(c2f($c, '_', '/') . '.' . __FUNCTION__, [$long, $path], null, $c);
        header('Location: ' . str_replace('&amp;', '&', $long));
        exit;
    }

    public static function token($id = 0) {
        $token = self::hash($id);
        Session::set(self::session . '.token.' . $id, $token);
        return $token;
    }

}