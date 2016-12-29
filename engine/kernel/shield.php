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

    protected static function _NS($input) {
        if (is_string($input) && strpos($input, '-') !== false) {
            $input = explode('-', $input);
            $a = array_shift($input);
            $path = [$a];
            while ($b = array_shift($input)) {
                $path[] = $a . '-' . $b;
            }
            return $path;
        }
        return $input;
    }

    public static function path($input, $fail = false) {
        global $config;
        if (is_array($input)) {
            foreach ($input as $k => $v) {
                $x = substr($v, -4) !== '.php' ? '.php' : "";
                $input[$k] = SHIELD . DS . $config->shield . DS . ltrim($v, DS) . $x;
            }
        } else {
            $x = substr($input, -4) !== '.php' ? '.php' : "";
            // Full path, be quick!
            if (strpos($input, ROOT) === 0) {
                return File::exist($input . $x, $fail);
            }
            $input = SHIELD . DS . $config->shield . DS . ltrim($input, DS) . $x;
        }
        return File::exist($input, $fail);
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
        $input = self::_NS($input);
        $path__ = self::path($input, $fail);
        $G = ['name' => $input];
        $NS = strtolower(static::class) . '.';
        $i18n = new Language;
        $lot__ = self::cargo();
        $path__ = Hook::NS($NS . 'path', $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $out = "";
        // Begin shield
        Hook::fire($NS . 'lot.before', [null, $G, $G]);
        extract(Hook::NS($NS . 'lot', $lot__));
        Hook::fire($NS . 'lot.after', [null, $G, $G]);
        Hook::fire($NS . 'before', [null, $G, $G]);
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
        Hook::fire($NS . 'after', [null, $G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $path = self::path($code);
        $s = explode('-', $path, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page.type', $s);
        HTTP::status((int) $s);
        self::attach($code, $fail, $buffer);
    }

    public static function exist($name, $fail = false) {
        return Folder::exist(SHIELD . DS . $name, $fail);
    }

}