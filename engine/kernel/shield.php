<?php

class Shield extends Genome {

    public static $shield = [];

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
        $NS = __c2f__(static::class, '_') . '.get.';
        $lot_a__ = ['lot' => []];
        if (is_array($fail)) {
            $lot_a__['lot'] = $fail;
            $fail = false;
        }
        if ($path__ = Hook::NS($NS . 'path', [self::path($input, $fail), $input])) {
            global $config;
            $G = ['source' => $input];
            $lot__ = Lot::set('state', new State(self::state($config->shield, [])))->get(null, []);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield part
            Hook::NS($NS . 'lot.enter', [$out, $G]);
            extract(Hook::NS($NS . 'lot', [$lot__, $G]));
            extract($lot_a__);
            Hook::NS($NS . 'lot.exit', [$out, $G]);
            Hook::NS($NS . 'enter', [$out, $G]);
            if ($buffer) {
                ob_start(function($content) use($G, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $G]);
                    $out = Hook::NS($NS . 'output', [$content, $G]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            // End shield part
            Hook::NS($NS . 'exit', [$out, $G]);
        }
    }

    public static function attach($input, $fail = false, $buffer = true) {
        $NS = __c2f__(static::class, '_') . '.';
        $lot_a__ = ['lot' => []];
        if (is_array($fail)) {
            $lot_a__['lot'] = $fail;
            $fail = false;
        }
        if ($path__ = Hook::NS($NS . 'path', [self::path($input, $fail), $input])) {
            global $config;
            $G = ['source' => $input];
            $lot__ = Lot::set('state', new State(self::state($config->shield, [])))->get(null, []);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield
            Hook::NS($NS . 'lot.enter', [$out, $G]);
            extract(Hook::NS($NS . 'lot', [$lot__, $G]));
            extract($lot_a__);
            if (is_array($fail)) {
                extract($fail);
            }
            Hook::NS($NS . 'lot.exit', [$out, $G]);
            Hook::NS($NS . 'enter', [$out, $G]);
            if ($buffer) {
                ob_start(function($content) use($G, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$content, $G]);
                    $out = Hook::NS($NS . 'output', [$content, $G]);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            // End shield
            Hook::NS($NS . 'exit', [$out, $G]);
            exit;
        } else {
            Guardian::abort('<code>' . __METHOD__ . '(' . json_encode($input) . ')');
        }
    }

    public static function abort($code = 404, $fail = false, $buffer = true) {
        $path = self::path((string) $code);
        $s = explode('/', $path);
        $s = array_shift($s);
        $s = is_numeric($s) ? $s : '404';
        HTTP::status((int) $s);
        self::attach($code, $fail, $buffer);
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(SHIELD . DS . $input, $fail);
    }

    public static function state(...$lot) {
        $id = str_replace('.', '\\', basename(array_shift($lot)));
        $key = array_shift($lot);
        $fail = array_shift($lot) ?: false;
        $folder = (is_array($key) ? $fail : array_shift($lot)) ?: SHIELD;
        $state = $folder . DS . $id . DS . 'state' . DS . 'config.php';
        if (!file_exists($state)) {
            return is_array($key) ? $key : $fail;
        }
        $c = __c2f__(static::class, '_');
        $state = isset(self::$shield[$c][$id]) ? self::$shield[$c][$id] : include $state;
        $state = Hook::NS($c . '.state.' . $id, [$state]);
        if (is_array($key)) {
            return array_replace_recursive($key, $state);
        }
        return isset($key) ? (array_key_exists($key, $state) ? $state[$key] : $fail) : $state;
    }

}