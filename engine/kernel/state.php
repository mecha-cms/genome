<?php

class State extends Genome {

    public static function __callStatic($kin, $lot) {
        if ($state = File::exist(STATE . DS . $kin . '.log')) {
            return unserialize(file_get_contents($state));
        }
        return parent::__callStatic($kin, $lot);
    }

}