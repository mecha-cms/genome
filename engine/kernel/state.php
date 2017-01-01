<?php

class State extends Genome {

    public static function __callStatic($kin, $lot) {
        $s = STATE . DS . $kin . '.php';
        if ($state = File::open($s)->import()) {
            $state_alt = array_merge(['shield' => ""], isset($lot[0]) ? (array) $lot[0] : []);
            $state = Anemon::extend($state_alt, $state);
            $s = SHIELD . DS . $state['shield'] . DS . 'state' . DS . $kin . '.php';
            if ($state_alt = File::open($s)->import()) {
                Anemon::extend($state, $state_alt);
            }
            return $state;
        }
        return parent::__callStatic($kin, $lot);
    }

}