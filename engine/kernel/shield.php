<?php

class Shield extends Genome {

    public static $lot = [];

    protected static function X($input) {
        $x = substr($input, -4) !== '.php' ? '.php' : "";
        return $input . $x;
    }

    public static function path($input, $fail = false) {
        global $config;
        // Full path, be quick!
        if (is_string($input) && strpos($input, ROOT) === 0) {
            return File::exist(self::X($input), $fail);
        } else if (is_string($input)) {
            $input = str_replace(DS, '/', $input);
        }
        $input = Anemon::step($input, '/');
        array_unshift($input, str_replace('/', '.', $input[0]));
        foreach ($input as $k => $v) {
            // Full path, skip!
            if (strpos($v, ROOT) === 0) continue;
            $v = To::path($v);
            $input[$k] = self::X(SHIELD . DS . $config->shield . DS . trim($v, DS));
        }
        return File::exist($input, $fail);
    }

    public static function get($input, $fail = false, $buffer = true) {
        $NS = __c2f__(static::class) . '.get.';
        if ($path__ = Hook::NS($NS . 'path', [self::path($input, $fail)])) {
            global $config;
            $G = ['source' => $input];
            $lot__ = Lot::get(null, []);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield part
            Hook::NS($NS . 'lot.before', [null, $G, $G]);
            $state = o(self::state($config->shield, []));
            extract(Hook::NS($NS . 'lot', $lot__));
            Hook::NS($NS . 'lot.after', [null, $G, $G]);
            Hook::NS($NS . 'before', [null, $G, $G]);
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $path__]);
                    $out = Hook::NS($NS . 'output', [$content, $path__]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['content'] = $out;
            // Reset shield part lot
            self::$lot = [];
            // End shield part
            Hook::NS($NS . 'after', [null, $G, $G]);
        }
    }

    public static function attach($input, $fail = false, $buffer = true) {
        if ($path__ = self::path($input, $fail)) {
            global $config;
            $G = ['source' => $input];
            $NS = __c2f__(static::class) . '.';
            $lot__ = Lot::get(null, []);
            $path__ = Hook::NS($NS . 'path', [$path__]);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield
            Hook::NS($NS . 'lot.before', [null, $G, $G]);
            $state = o(self::state($config->shield, []));
            extract(Hook::NS($NS . 'lot', [$lot__]));
            Hook::NS($NS . 'lot.after', [null, $G, $G]);
            Hook::NS($NS . 'before', [null, $G, $G]);
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $path__]);
                    $out = Hook::NS($NS . 'output', [$content, $path__]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['content'] = $out;
            // Reset shield lot
            self::$lot = [];
            // End shield
            Hook::NS($NS . 'after', [null, $G, $G]);
            exit;
        } else {
            Guardian::abort('<code>' . __METHOD__ . '(' . json_encode($input) . ')');
        }
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $path = self::path($code);
        $s = explode('.', $path);
        $s = array_pop($s);
        $s = is_numeric($s) ? $s : '404';
        HTTP::status((int) $s);
        self::attach($path, $fail, $buffer);
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
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $state = include $state;
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}