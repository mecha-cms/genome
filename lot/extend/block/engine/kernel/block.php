<?php

class Block extends Genome {

    protected static $lot = [];

    public static function set($id, $fn) {
        self::$lot[$id] = $fn;
        return true;
    }

    public static function get($id = null, $fail = false) {
        if (isset($id)) {
            return array_key_exists($id, self::$lot) ? self::$lot[$id] : $fail;
        }
        return !empty(self::$lot) ? self::$lot : $fail;
    }

    public static function reset($id = null) {
        if (isset($id)) {
            unset(self::$lot[$id]);
        } else {
            self::$lot = [];
        }
        return true;
    }

    public static function replace($id, $fn, $content, $strict = false) {
        $state = Extend::state(Path::D(__DIR__, 2));
        $d = '#';
        $u = $state['union'][1];
        $ueo = $u[0][0];
        $uec = $u[0][1];
        $ues = $u[0][2];
        $uas = $u[1][3];
        $ueo_x = x($u[0][0], $d);
        $uec_x = x($u[0][1], $d);
        $ues_x = x($u[0][2], $d);
        $uas_x = x($u[1][3], $d);
        $a_x = x(Anemon::NS, $d);
        // no `[[` character(s) found, skip anyway …
        if (strpos($content, $ueo) === false) {
            return $content;
        }
        // no `[[id]]` and `[[id/]]` character(s) found, skip …
        if (strpos($content, $ueo . $id . $uec) === false && strpos($content, $ueo . $id . $ues . $uec) === false) {
            return $content;
        }
        // `[[id]]content[[/id]]`
        $id_end = explode(Anemon::NS, $id)[0];
        $id_end = $strict ? $id_end : $id_end . '(?:' . $a_x . '[^' . $a_x . $uas_x . ']+)*';
        $block = $ueo_x . $id . '(' . $uas_x . '.*?)?' . $uec_x . '[\s\S]*?' . $ueo_x . $ues_x . $id_end . $uec_x;
        // `[[id]]` or `[[id/]]`
        $void = $ueo_x . $id . '(' . $uas_x . '.*?)?' . $ues_x . $uec_x;
        return preg_replace_callback($d . $block . '|' . $void . $d, function($m) use($state, $fn) {
            $data = (new Union($state['union']))->apart($m[0]);
            array_shift($data); // remove “node name” data
            return call_user_func_array($fn, $data);
        }, $content);
    }

}