<?php

class Shield extends Genome {

    public static $lot = [];

    protected static function version_static($info, $v = null) {
        if (is_string($info)) {
            $info = self::info_static($info)->version;
        } else {
            $info = (object) $info;
            $info = $info->version ?? '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    protected static function cargo_static($key = null, $fail = false) {
        self::$lot = array_merge(self::$lot, Seed::get(null, []));
        foreach (glob(PAGE . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
            $v = Path::B($v);
            if (!isset(self::$lot['lot'][$v])) {
                self::$lot['lot'][$v] = [];
            }
            if (!isset(self::$lot['lot'][$v . 's'])) {
                self::$lot['lot'][$v . 's'] = [];
            }
        }
        if ($key !== null) {
            return Anemon::get(self::$lot, $key, $fail);
        }
        return self::$lot;
    }

    protected static function path_static($input, $fail = false) {
        $x = Path::X($input, "") !== 'php' ? '.php' : "";
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

    protected static function info_static($folder = null, $a = false) {
        $folder = $folder ?? Config::get('shield');
        $i18n = new Language;
        // Check whether the localized "about" file is available
        $f = SHIELD . DS . $folder . DS;
        if (!$info = File::exist($f . 'about.' . Config::get('language') . '.txt')) {
            $info = $f . 'about.txt';
        }
        $info = Page::open($info)->read('content', [
            'id' => Folder::exist(SHIELD . DS . $folder) ? $folder : null,
            'title' => To::title($folder),
            'author' => $i18n->anon,
            'link' => '#',
            'version' => '0.0.0',
            'content' => $i18n->_message_avail($i18n->description)
        ], strtolower(static::class) . ':');
        return $a ? $info : o($info);
    }

    protected static function attach_static($input, $fail = false, $buffer = true) {
        $path__ = To::path($input);
        $s = explode('-', Path::N($input), 2);
        $G = ['name' => $input, 'name.base' => $s[0]];
        $NS = strtolower(static::class) . ':';
        $i18n = new Language;
        if (strpos($path__, ROOT) === 0 && is_file($path__)) {
            // do nothing ...
        } else {
            if ($_path = File::exist(self::path_static($path__, $fail))) {
                $path__ = $_path;
            } elseif ($_path = File::exist(self::path_static($s[0], $fail))) {
                $path__ = $_path;
            } else {
                exit($i18n->_message_error_file_exist('<code>' . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo_static();
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

    protected static function abort_static($code = '404', $fail = false, $buffer = true) {
        $s = explode('-', $code, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page.type', $s);
        HTTP::status((int) $s);
        self::attach_static($code, $fail, $buffer);
    }

    protected static function exist_static($name, $fail = false) {
        return Folder::exist(SHIELD . DS . $name, $fail);
    }

}