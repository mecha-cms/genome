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
        foreach (g(PAGE, '*/') as $v) {
            $v = Path::B($v);
            if (!isset(self::$lot['lot'][$v])) {
                self::$lot['lot'][$v] = [];
            }
            if (!isset(self::$lot['lot'][$v . 's'])) {
                self::$lot['lot'][$v . 's'] = [];
            }
        }
        if (isset($key)) {
            return Anemon::get(self::$lot, $key, $fail);
        }
        return self::$lot;
    }

    public static function path($input, $fail = false) {
        $x = Path::X($input) !== 'php' ? '.php' : "";
        $input = To::path($input) . $x;
        // Full path, be quick!
        if (strpos($input, ROOT) === 0) {
            return File::exist($input, $fail);
        }
        if ($path = File::exist(SHIELD . DS . Config::get('shield') . DS . ltrim($input, DS))) {
            return $path;
        } elseif ($path = File::exist(ROOT . DS . ltrim($input, DS))) {
            return $path;
        }
        return $fail;
    }

    public static function info($folder = null, $a = false) {
        $folder = isset($folder) ? $folder : Config::get('shield');
        $i18n = new Language;
        // Check whether the localized "about" file is available
        $f = SHIELD . DS . $folder . DS;
        if (!$info = File::exist($f . 'about.' . Config::get('language') . '.txt')) {
            $info = $f . 'about.txt';
        }
        $info = Page::open($info)->read([
            'id' => Folder::exist(SHIELD . DS . $folder) ? $folder : null,
            'title' => To::title($folder),
            'author' => $i18n->anon,
            'version' => '0.0.0',
            'content' => $i18n->_message_avail($i18n->description)
        ], strtolower(static::class) . '.');
        return $a ? $info : o($info);
    }

    public static function attach($input, $fail = false, $buffer = true) {
        $path__ = To::path($input);
        if (substr($path__, -4) !== '.php') {
            $path__ .= '.php';
        }
        $s = explode('-', Path::N($input), 2);
        $G = ['name' => $input, 'name.base' => $s[0]];
        $NS = strtolower(static::class) . '.';
        $i18n = new Language;
        if (strpos($path__, ROOT) === 0 && is_file($path__)) {
            // do nothing ...
        } else {
            $r = SHIELD . DS . Config::get('shield') . DS;
            if ($_path = File::exist(self::path($r . $path__, $fail))) {
                $path__ = $_path;
            } elseif ($_path = File::exist(self::path($r . $s[0], $fail))) {
                $path__ = $_path;
            } else {
                Guardian::abort($i18n->_message_error_file_exist('<code>' . $r . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo();
        $path__ = Hook::NS($NS . 'path', $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $G['path.base'] = $s[0];
        $out = "";
        // Begin shield
        Hook::fire($NS . 'lot.b', [null, $G, $G]);
        extract(Hook::NS($NS . 'lot', $lot__));
        Hook::fire($NS . 'lot.e', [null, $G, $G]);
        Hook::fire($NS . 'b', [null, $G, $G]);
        if ($path__) {
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'i', [$content, $path__]);
                    $out = Hook::NS($NS . 'o', [$content, $path__]);
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
        Hook::fire($NS . 'e', [null, $G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $s = explode('-', $code, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page.type', $s);
        HTTP::status((int) $s);
        self::attach($code, $fail, $buffer);
    }

    public static function exist($name, $fail = false) {
        return Folder::exist(SHIELD . DS . $name, $fail);
    }

}