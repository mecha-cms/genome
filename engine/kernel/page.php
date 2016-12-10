<?php

class Page extends Genome {

    protected static $meta = [];
    protected static $data = "";
    protected static $path = "";

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    // Escape ...
    public static function x($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un-Escape ...
    public static function v($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    public static function open($path) {
        self::$path = $path;
        self::apart();
        return new static;
    }

    // Apart ...
    public static function apart($input = null) {
        $input = n($input ?? file_get_contents(self::$path));
        $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
        $input = explode(self::$v[1] . N . N, $input, 2);
        // Do meta ...
        self::$meta = [];
        foreach (explode(self::$v[4], $input[0]) as $v) {
            $v = explode(self::$v[2], $v, 2);
            self::$meta[self::v($v[0])] = e(self::v($v[1] ?? false));
        }
        // Do data ...
        self::$data = trim($input[1] ?? "");
        return new static;
    }

    // Unite ...
    public static function unite() {
        $meta = [];
        foreach (self::$meta as $k => $v) {
            $meta[] = self::x($k) . self::$v[2] . self::x(s($v));
        }
        return self::$v[0] . implode(N, $meta) . self::$v[1] . (self::$data ? N . N . self::$data : "");
    }

    // Create meta ...
    public static function meta($a) {
        Anemon::extend(self::$meta, $a);
        foreach (self::$meta as $k => $v) {
            if ($v === false) unset(self::$meta[$k]);
        }
        return new static;
    }

    // Create data ...
    public static function data($s) {
        self::$data = $s;
        return new static;
    }

    public static function read($as = 'content', $output = [], $NS = 'page:', $lot = []) {
        $lot = array_merge($lot, self::$meta);
        // Pre-defined page meta ...
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load page meta ...
        return self::_meta(array_merge($output, $lot, [$as => self::$data]), $NS, $lot);
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

    public static function saveTo($path, $consent = 0600) {
        File::open($path)->write(self::unite())->save($consent);
    }

    public static function save($consent = 0600) {
        return self::saveTo(self::$path, $consent);
    }

}