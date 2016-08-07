<?php

class State extends Socket {

    public static function __callStatic($kin, $lot = []) {
        if ($state = File::exist(STATE . DS . To::slug($kin) . '.txt')) {
            return File::open($state)->unserialize();
        }
        return parent::__callStatic($kin, $lot);
    }

}