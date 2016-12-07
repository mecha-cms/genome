<?php

class Page extends Genome {

    protected static $meta_ = [];
    protected static $data_ = "";
    protected static $path_ = "";

    public static $v = ["---\n", "\n...", ': ', '- ', "\n"];
    public static $x = ['&#45;&#45;&#45;&#10;', '&#10;&#46;&#46;&#46;', '&#58;&#32;', '&#45;&#32;', '&#10;'];

    // Escape ...
    protected static function x_($s) {
        return str_replace(self::$v, self::$x, $s);
    }

    // Un-Escape ...
    protected static function v_($s) {
        return str_replace(self::$x, self::$v, $s);
    }

    protected static function open_($path) {
        self::$path_ = $path;
        self::apart_();
        return new static;
    }

    // Apart ...
    protected static function apart_($input = null) {
        $input = n($input ?? file_get_contents(self::$path_));
        $input = str_replace([X . self::$v[0], X], "", X . $input . N . N);
        $input = explode(self::$v[1] . N . N, $input, 2);
        // Do meta ...
        self::$meta_ = [];
        foreach (explode(self::$v[4], $input[0]) as $v) {
            $v = explode(self::$v[2], $v, 2);
            self::$meta_[self::v_($v[0])] = e(self::v_($v[1] ?? false));
        }
        // Do data ...
        self::$data_ = trim($input[1] ?? "");
        return new static;
    }

    // Unite ...
    protected static function unite_() {
        $meta = [];
        foreach (self::$meta_ as $k => $v) {
            $meta[] = self::x_($k) . self::$v[2] . self::x_(s($v));
        }
        return self::$v[0] . implode(N, $meta) . self::$v[1] . (self::$data_ ? N . N . self::$data_ : "");
    }

    // Create meta ...
    protected static function meta_($a) {
        Anemon::extend(self::$meta_, $a);
        foreach (self::$meta_ as $k => $v) {
            if ($v === false) unset(self::$meta_[$k]);
        }
        return new static;
    }

    // Create data ...
    protected static function data_($s) {
        self::$data_ = $s;
        return new static;
    }

    protected static function read_($as = 'content', $output = [], $NS = 'page:', $lot = []) {
        $lot = array_merge($lot, self::$meta_);
        // Pre-defined page meta ...
        if ($output) {
            foreach ($output as $k => $v) {
                if (strpos($k, '__') !== 0 && !array_key_exists('__' . $k, $output)) {
                    $output['__' . $k] = $v;
                }
            }
        }
        // Load page meta ...
        return self::_meta(array_merge($output, $lot, [$as => self::$data_]), $NS, $lot);
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

    protected static function saveTo_($path, $consent = 0600) {
        File::open($path)->write(self::unite_())->save($consent);
    }

    protected static function save_($consent = 0600) {
        return self::saveTo_(self::$path_, $consent);
    }

}