<?php

class State extends Genome {

    public static function __callStatic($kin, $lot) {
        if (strpos($kin, 'set_') === 0) {
            $kin = substr($kin, strlen('set_'));
            $data = call_user_func_array('self::' . $kin, $lot);
            if ($state = File::exist(STATE . DS . $kin . '.php')) {
                Anemon::extend($data, $lot[0] ?? []);
                File::export($data)->saveTo($state);
                return true;
            } elseif ($state = File::exist(STATE . DS . $kin . '.txt')) {
                Anemon::extend($data, $lot[0] ?? []);
                File::serialize($data)->saveTo($state);
                return true;
            }
            return false;
        }
        if ($state = File::exist(STATE . DS . $kin . '.php')) {
            $a = include $state;
            return $a ? $a : ($lot[0] ?? false);
        } elseif ($state = File::exist(STATE . DS . $kin . '.txt')) {
            $a = unserialize(file_get_contents($state));
            return $a ? $a : ($lot[0] ?? false);
        }
        return parent::__callStatic($kin, $lot);
    }

}