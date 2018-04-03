<?php

class Shield extends Genome {

    protected static $shield = [];

    protected static function __($input) {
        $x = substr($input, -4) !== '.php' ? '.php' : "";
        return $input . $x;
    }

    public static function path($input, $fail = false) {
        // Full path, be quick!
        if (is_string($input) && strpos($input, ROOT) === 0) {
            return File::exist(self::__($input), $fail);
        } else if (is_string($input)) {
            $input = str_replace(DS, '/', $input);
        }
        $input = Anemon::step($input, '/');
        array_unshift($input, str_replace('/', '.', $input[0]));
        foreach ($input as &$v) {
            // Full path, skip!
            if (strpos($v = To::path($v), ROOT) === 0) continue;
            $v = self::__($v);
        }
        return File::exist(Hook::fire(__c2f__(static::class, '_') . '.' . __FUNCTION__, [$input, $input]), $fail);
    }

    public static function get($input, $fail = false, $print = true) {
        $NS = __c2f__(static::class, '_') . '.' . __FUNCTION__ . '.';
        $out = "";
        Lot::set('lot', []);
        if (is_array($fail)) {
            Lot::set('lot', $fail);
            $fail = false;
        }
        if ($path = self::path($input, $fail)) {
            $G = [
                'path' => $path,
                'source' => $input
            ];
            // Begin shield part
            extract(Lot::get(null, []));
            Hook::fire($NS . 'enter', [$out, $G]);
            if (function_exists('ob_start')) {
                ob_start();
                require $path;
                $out = ob_get_clean();
            } else {
                $out = require $path;
            }
            $out = Hook::fire($NS . 'yield', [$out, $G]);
            // End shield part
            Hook::fire($NS . 'exit', [$out, $G]);
        }
        if (!$print) {
            return $out;
        }
        echo $out;
    }

    public static function read($input, $fail = false, $print = true) {
        $NS = __c2f__(static::class, '_') . '.';
        $out = "";
        Lot::set('lot', []);
        if (is_array($fail)) {
            Lot::set('lot', $fail);
            $fail = false;
        }
        if ($path = self::path($input, $fail)) {
            $G = [
                'path' => $path,
                'source' => $input
            ];
            // Begin shield
            extract(Lot::get(null, []));
            Hook::fire($NS . 'enter', [$out, $G]);
            if (function_exists('ob_start')) {
                ob_start();
                require $path;
                $out = ob_get_clean();
            } else {
                $out = require $path;
            }
            $out = Hook::fire($NS . 'yield', [$out, $G]);
            // End shield
            Hook::fire($NS . 'exit', [$out, $G]);
        } else {
            $out = '<code>' . __METHOD__ . '(' . v(json_encode($input)) . ')</code>';
        }
        if (!$print) {
            return $out;
        }
        echo $out;
    }

    public static function attach($input, $fail = false) {
        self::read($input, $fail, true);
        exit;
    }

    public static function abort($code = 404, $fail = false) {
        $path = self::path((string) $code);
        $s = explode('/', $path);
        $s = array_shift($s);
        $s = is_numeric($s) ? $s : '404';
        HTTP::status((int) $s);
        self::read($code, $fail, true);
        exit;
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(SHIELD . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = basename(array_shift($lot));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: SHIELD;
        $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
        $id = str_replace('.', '\\', $id);
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $c = __c2f__(static::class, '_');
        $state = isset(self::$shield[$c][$id]) ? self::$shield[$c][$id] : include $state;
        $state = Hook::fire($c . '.state.' . $id, [$state]);
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}