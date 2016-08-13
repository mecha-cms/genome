<?php

class Session extends Socket {

    public static function set($id, $lot = "") {
        Anemon::set($_SESSION, $id, $lot);
    }

    public static function get($id = null, $fail = "") {
        if ($id === null) return $_SESSION;
        return Anemon::get($_SESSION, $id, $fail);
    }

    public static function reset($id = null) {
        if ($id === null || $id === true) {
            $_SESSION = [];
            if ($id === true) session_destroy();
        } else {
            Anemon::reset($_SESSION, $id);
        }
    }

    public static function start(...$lot) {
        $path = array_shift($lot) ?? SESSION;
        if ($path !== null) {
            Folder::create($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

}