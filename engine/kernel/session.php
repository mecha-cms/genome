<?php

final class Session extends Genome {

    public static function get($key = null) {
        return isset($key) ? Anemon::get($_SESSION, $key) : ($_SESSION ?? []);
    }

    public static function reset($key = null) {
        if (is_array($key)) {
            foreach ($key as $v) {
                self::reset($v);
            }
        } else if (isset($key) && $key !== true) {
            Anemon::reset($_SESSION, $key);
        } else {
            $_SESSION = [];
            if ($key === true) {
                session_destroy();
            }
        }
    }

    public static function set(string $key, $value) {
        Anemon::set($_SESSION, $key, $value);
    }

    public static function start(...$lot) {
        $path = array_shift($lot);
        $path = $path ? $path : constant(u(static::class));
        if (isset($path)) {
            Folder::create($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

}