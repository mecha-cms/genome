<?php

class Page extends Genome {

    protected static $meta_static = [];
    protected static $data_static = "";
    protected static $path_static = "";

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    // Escape ...
    protected static function x_static($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un-Escape ...
    protected static function v_static($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    protected static function open_static($path) {
        self::$path_static = $path;
        self::apart_static();
        return new static;
    }

    // Apart ...
    protected static function apart_static($input = null) {
        $input = n($input ?? file_get_contents(self::$path_static));
        $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
        $input = explode(self::$v[1] . N . N, $input, 2);
        // Do meta ...
        self::$meta_static = [];
        foreach (explode(self::$v[4], $input[0]) as $v) {
            $v = explode(self::$v[2], $v, 2);
            self::$meta_static[self::v_static($v[0])] = e(self::v_static($v[1] ?? false));
        }
        // Do data ...
        self::$data_static = trim($input[1] ?? "");
        return new static;
    }

    // Unite ...
    protected static function unite_static() {
        $meta = [];
        foreach (self::$meta_static as $k => $v) {
            $meta[] = self::x_static($k) . self::$v[2] . self::x_static(s($v));
        }
        return self::$v[0] . implode(N, $meta) . self::$v[1] . (self::$data_static ? N . N . self::$data_static : "");
    }

    // Create meta ...
    protected static function meta_static($a) {
        Anemon::extend(self::$meta_static, $a);
        foreach (self::$meta_static as $k => $v) {
            if ($v === false) unset(self::$meta_static[$k]);
        }
        return new static;
    }

    // Create data ...
    protected static function data_static($s) {
        self::$data_static = $s;
        return new static;
    }

    protected static function read_static($as = 'content', $output = [], $NS = 'page:', $lot = []) {
        $lot = array_merge($lot, self::$meta_static);
        // Pre-defined page meta ...
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load page meta ...
        return self::_meta(array_merge($output, $lot, [$as => self::$data_static]), $NS, $lot);
    }

    protected static function _meta($input, $NS, $lot) {
        $output = [];
        foreach ($input as $k => $v) {
            $v = Hook::NS($NS . '__' . $k, [$v, $lot]);
            $output['__' . $k] = $v; // private item
            $v = Hook::NS($NS . 'pattern.i', [$v, $lot]); // before pattern set-up
            $v = Hook::NS($NS . $k, [$v, $lot]); // public item
            $v = Hook::NS($NS . 'pattern.o', [$v, $lot]); // after pattern set-up
            $output[$k] = $v;
        }
        return $output;
    }

    protected static function saveTo_static($path, $consent = 0600) {
        File::open($path)->write(self::unite_static())->save($consent);
    }

    protected static function save_static($consent = 0600) {
        return self::saveTo_static(self::$path_static, $consent);
    }

}