<?php

class Session extends DNA {

    public function set($id, $lot = "") {
        Anemon::set($_SESSION, $id, $lot);
    }

    public function get($id = null, $fail = "") {
        if ($id === null) return $_SESSION;
        return Anemon::get($_SESSION, $id, $fail);
    }

    public function reset($id = null) {
        if ($id === null) {
            $_SESSION = [];
            session_destroy();
        } else {
            Anemon::reset($_SESSION, $id);
        }
    }

    public function start($path = SESSION) {
        if ($path !== null) {
            Folder::create($path, 0600);
            session_save_path($path);
        }
        return !session_id() ? session_start() : true;
    }

}