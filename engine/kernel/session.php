<?php

class Session extends Genome {

    public static function ignite(...$lot) {
        $path = array_shift($lot);
        $path = $path ? $path : constant(u(static::class));
        if (isset($path)) {
            Folder::set($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

    public static function set($key, $value = null) {
        Anemon::set($_SESSION, $key, $value);
        return new static;
    }

    public static function get($key = null, $fail = null) {
        return isset($key) ? Anemon::get($_SESSION, $key, $fail) : ($_SESSION ?: $fail);
    }

    public static function reset($key = null) {
        if (!isset($key) || $key === true) {
            $_SESSION = [];
            if ($key === true) {
                session_destroy();
            }
        } else {
            Anemon::reset($_SESSION, $key);
        }
        return new static;
    }

}