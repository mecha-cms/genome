<?php

class Session extends Genome {

    public static function ignite(...$lot) {
        $path = array_shift($lot);
        $path = $path ? $path : SESSION;
        if (isset($path)) {
            Folder::create($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

    public static function set($id, $lot = "") {
        Anemon::set($_SESSION, $id, $lot);
    }

    public static function get($id = null, $fail = "") {
        if (!isset($id)) return $_SESSION;
        return Anemon::get($_SESSION, $id, $fail);
    }

    public static function reset($id = null) {
        if (!isset($id) || $id === true) {
            $_SESSION = [];
            if ($id === true) session_destroy();
        } else {
            Anemon::reset($_SESSION, $id);
        }
    }

}