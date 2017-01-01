<?php

class Shield extends Genome {

    public static $lot = [];

    public static function version($info, $v = null) {
        if (is_string($info)) {
            $info = self::info($info)->version;
        } else {
            $info = (object) $info;
            $info = isset($info->version) ? $info->version : '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    public static function cargo($key = null, $fail = false) {
        self::$lot = array_merge(self::$lot, Seed::get(null, []));
        if (isset($key)) {
            return Anemon::get(self::$lot, $key, $fail);
        }
        return self::$lot;
    }

    protected static function X($input) {
        $x = substr($input, -4) !== '.php' ? '.php' : "";
        return $input . $x;
    }

    public static function path($input, $fail = false) {
        global $config;
        // Full path, be quick!
        if (is_string($input) && strpos($input, ROOT) === 0) {
            return File::exist(self::X($input), $fail);
        }
        $input = Anemon::step($input, '/');
        foreach ($input as $k => $v) {
            $v = To::path($v);
            $input[$k] = self::X(SHIELD . DS . $config->shield . DS . trim($v, DS));
        }
        return File::exist($input, $fail);
    }

    public static function info($folder = null, $a = false) {
        global $config, $language;
        $folder = $folder ?: $config->shield;
        // Check whether the localized "about" file is available
        $f = SHIELD . DS . $folder . DS;
        if (!$info = File::exist($f . 'about.' . $config->language . '.txt')) {
            $info = $f . 'about.txt';
        }
        $info = Page::open($info)->read([
            'id' => Folder::exist(SHIELD . DS . $folder) ? $folder : null,
            'title' => To::title($folder),
            'author' => $language->anon,
            'version' => '0.0.0',
            'content' => $language->_message_avail($language->description)
        ], strtolower(static::class) . Anemon::NS);
        return $a ? $info : o($info);
    }

    public static function get($input, $fail = false, $buffer = true) {
        global $config, $date, $language, $url;
        if ($path__ = self::path($input, $fail)) {
            $G = ['source' => $input];
            $NS = strtolower(static::class) . Anemon::NS . 'get' . Anemon::NS;
            $lot__ = self::cargo();
            $path__ = Hook::NS($NS . 'path', $path__);
            $G['lot'] = $lot__;
            $G['path'] = $path__;
            $out = "";
            // Begin shield part
            Hook::fire($NS . 'lot' . Anemon::NS . 'before', [null, $G, $G]);
            extract(Hook::NS($NS . 'lot', $lot__));
            Hook::fire($NS . 'lot' . Anemon::NS . 'after', [null, $G, $G]);
            Hook::fire($NS . 'before', [null, $G, $G]);
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
            Hook::fire($NS . 'after', [null, $G, $G]);
        }
    }

    public static function attach($input, $fail = false, $buffer = true) {
        global $config, $date, $language, $url;
        $path__ = self::path($input, $fail);
        $G = ['source' => $input];
        $NS = strtolower(static::class) . Anemon::NS;
        $lot__ = self::cargo();
        $path__ = Hook::NS($NS . 'path', $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $out = "";
        // Begin shield
        Hook::fire($NS . 'lot' . Anemon::NS . 'before', [null, $G, $G]);
        extract(Hook::NS($NS . 'lot', $lot__));
        Hook::fire($NS . 'lot' . Anemon::NS . 'after', [null, $G, $G]);
        Hook::fire($NS . 'before', [null, $G, $G]);
        if ($path__) {
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
        }
        $G['content'] = $out;
        // Reset shield lot
        self::$lot = [];
        // End shield
        Hook::fire($NS . 'after', [null, $G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $path = self::path($code);
        $s = explode(Anemon::NS, $path);
        $s = array_pop($s);
        $s = is_numeric($s) ? $s : '404';
        HTTP::status((int) $s);
        self::attach($path, $fail, $buffer);
    }

    public static function exist($input, $fail = false) {
        return Folder::exist(SHIELD . DS . $input, $fail);
    }

}