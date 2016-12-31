<?php

class State extends Genome {

    public static function __callStatic($kin, $lot) {
        $s = STATE . DS . $kin . '.php';
        if ($state = File::open($s)->import()) {
            $state_alt = File::open(Path::D($s) . DS . Path::N($s) . '.txt')->unserialize();
            Anemon::extend($state, $state_alt, isset($lot[0]) ? (array) $lot[0] : []);
            $s = SHIELD . DS . $state['shield'] . DS . 'state' . DS . $kin . '.php';
            if ($state_alt = File::open($s)->import()) {
                $state_alt_alt = File::open(Path::D($s) . DS . Path::N($s) . '.txt')->unserialize();
                Anemon::extend($state, $state_alt, $state_alt_alt);
            }
            return $state;
        }
        return parent::__callStatic($kin, $lot);
    }

}