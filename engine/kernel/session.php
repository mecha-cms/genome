<?php

class Session extends Genome {

    protected static function set_($id, $lot = "") {
        Anemon::set($_SESSION, $id, $lot);
    }

    protected static function get_($id = null, $fail = "") {
        if ($id === null) return $_SESSION;
        return Anemon::get($_SESSION, $id, $fail);
    }

    protected static function reset_($id = null) {
        if ($id === null || $id === true) {
            $_SESSION = [];
            if ($id === true) session_destroy();
        } else {
            Anemon::reset($_SESSION, $id);
        }
    }

    protected static function ignite_(...$lot) {
        $path = array_shift($lot) ?? SESSION;
        if ($path !== null) {
            Folder::create($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

}