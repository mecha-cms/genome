<?php

class State extends Socket {

    public static function __callStatic($kin, $lot = []) {
        if ($state = File::exist(STATE . DS . To::slug($kin) . '.txt')) {
            return o(unserialize(file_get_contents($state)));
        }
        return parent::__callStatic($kin, $lot);
    }

}