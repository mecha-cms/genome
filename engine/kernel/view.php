<?php

class View extends Genome {

    protected static $lot = [];

    protected static function __($input) {
        $x = substr($input, -4) !== '.php' ? '.php' : "";
        return $input . $x;
    }

    public static function path($input, $fail = false) {
        $NS = __c2f__($c = static::class, '_') . '.' . __FUNCTION__;
        $output = [];
        if (is_string($input)) {
            if (strpos($input, ROOT) !== 0) {
                $output = Anemon::step(str_replace(DS, '/', $input), '/');
                array_unshift($output, str_replace('/', '.', $output[0]));
            }
        }
        foreach ($output ?: (array) $input as $k => $v) {
            $id = $v;
            $v = str_replace('/', DS, $v);
            $v = self::__(strpos($v, ROOT) === 0 ? $v : ROOT . DS . $v);
            if (!isset(self::$lot[$c][0][$id])) {
                $v = isset(self::$lot[$c][1][$id]) ? self::$lot[$c][1][$id] : $v;
            }
            $output[$k] = $v;
        }
        return File::exist(Hook::fire($NS, [$output, $input]), $fail);
    }

    public static function set($id, $path = null) {
        $c = static::class;
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                self::set($k, $v);
            }
        } else {
            if (!isset(self::$lot[$c][0][$id])) {
                $id = str_replace(DS, '/', $id);
                self::$lot[$c][1][$id] = $path;
            }
        }
        return new static;
    }

    public static function get($input, $fail = false, $print = true) {
        $NS = __c2f__(static::class, '_', '/');
        $output = "";
        Lot::set('lot', []);
        if (is_array($fail)) {
            Lot::set('lot', $fail);
            $fail = false;
        }
        if ($path = self::path($input, $fail)) {
            // Begin view
            Hook::fire($NS . '.enter', [$output, $input, $path]);
            ob_start();
            extract(Lot::get(null, []));
            require $path;
            $output = ob_get_clean();
            $output = Hook::fire($NS . '.' . __FUNCTION__, [$output, $input, $path]);
            // End view
            Hook::fire($NS . '.exit', [$output, $input, $path]);
        }
        if (!$print) {
            return $output;
        }
        echo $output;
    }

    public static function reset($id = null) {
        $c = static::class;
        if (!isset($id)) {
            self::$lot[$c] = [];
        } else if (is_array($id)) {
            foreach ($id as $v) {
                self::reset($v);
            }
        } else {
            $id = str_replace(DS, '/', $id);
            self::$lot[$c][0][$id] = 1;
            unset(self::$lot[$c][1][$id]);
        }
        return new static;
    }

    public static function fire($input, $fail = false) {
        if (!$output = self::get($input, $fail, false)) {
            $output = __replace__(Guardian::$config['message'], [
                'message' => '<code>' . __METHOD__ . '(' . v(json_encode($input)) . ')</code>'
            ]);
        }
        echo Hook::fire(__c2f__(static::class, '_', '/') . '.yield', [$output, $input]);
        exit;
    }

}