<?php

class State extends Genome {

    public static function __callStatic($kin, $lot) {
        if (strpos($kin, 'set_') === 0) {
            $kin = substr($kin, strlen('set_'));
            $data = call_user_func_array('self::' . $kin, $lot);
            if ($state = File::exist(STATE . DS . $kin . '.php')) {
                Anemon::extend($data, isset($lot[0]) ? $lot[0] : []);
                File::export($data)->saveTo($state);
                return true;
            } elseif ($state = File::exist(STATE . DS . $kin . '.txt')) {
                Anemon::extend($data, isset($lot[0]) ? $lot[0] : []);
                File::serialize($data)->saveTo($state);
                return true;
            }
            return false;
        }
        if ($state = File::exist(STATE . DS . $kin . '.php')) {
            $a = include $state;
            return $a ? $a : (isset($lot[0]) ? $lot[0] : false);
        } elseif ($state = File::exist(STATE . DS . $kin . '.txt')) {
            $a = File::open($state)->unserialize();
            return $a ? $a : (isset($lot[0]) ? $lot[0] : false);
        }
        return parent::__callStatic($kin, $lot);
    }

}