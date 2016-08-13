<?php

class Shield extends Genome {

    public static $lot = [];

    public static function version($info, $v = null) {
        if (is_string($info)) {
            $info = self::info($info)->version;
        } else {
            $info = (object) $info;
            $info = $info->version ?? '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    public static function cargo() {
        self::$lot['config'] = new Genome\Config;
        self::$lot['language'] = new Genome\Language;
        self::$lot['url'] = new Genome\URL;
        foreach (glob(SHEET . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
            $v = Path::B($v);
            if (!isset(self::$lot['lot'][$v])) {
                self::$lot['lot'][$v] = [];
            }
            if (!isset(self::$lot['lot'][$v . 's'])) {
                self::$lot['lot'][$v . 's'] = [];
            }
        }
        return self::$lot;
    }

    public static function path($input, $fail = false) {
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

    public static function lot($key = null, $fail = false) {
        if ($key === null) return self::$lot;
        if (!_is_anemon_($key)) {
            return self::$lot[$key] ?? $fail;
        }
        self::$lot = array_merge(self::$lot, (array) $key);
        return new static;
    }

    public static function apart($data) {
        foreach ((array) $data as $v) {
            unset(self::$lot[$v]);
        }
        return new static;
    }

    public static function info($folder = null, $a = false) {
        $folder = $folder ?? Config::get('shield');
        $i18n = new Genome\Language;
        // Check whether the localized "about" file is available
        if (!$info = File::exist(SHIELD . DS . $folder . DS . 'about.' . Config::get('language') . '.log')) {
            $info = SHIELD . DS . $folder . DS . 'about.log';
        }
        $info = Genome\Sheet::open($info)->read('content', [
            'id' => Folder::exist($folder),
            'title' => t($folder),
            'author' => $i18n->anon,
            'link' => '#',
            'version' => '0.0.0',
            'content' => $i18n->notify_not_avail($i18n->description)
        ], strtolower(static::class) . ':');
        return $a ? $info : o($info);
    }

    public static function attach($input, $fail = false, $buffer = true) {
        $path__ = To::path($input);
        $s = explode('-', Path::N($input), 2);
        $G = ['name' => $input, 'name.base' => $s[0]];
        $NS = strtolower(static::class) . ':';
        $i18n = new Genome\Language;
        if (strpos($path__, ROOT) === 0 && is_file($path__)) {
            // do nothing ...
        } else {
            if ($_path = File::exist(self::path($path__, $fail))) {
                $path__ = $_path;
            } elseif ($_path = File::exist(self::path($s[0], $fail))) {
                $path__ = $_path;
            } else {
                exit($i18n->notify_not_exist_file('<code>' . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo();
        $path__ = Hook::NS($NS . 'path', [], $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $G['path.base'] = $s[0];
        $out = "";
        // Begin shield
        Hook::fire($NS . 'lot.before', [$G, $G]);
        extract(Hook::NS($NS . 'lot', [], $lot__));
        Hook::fire($NS . 'lot.after', [$G, $G]);
        Hook::fire($NS . 'before', [$G, $G]);
        if ($path__) {
            if ($buffer) {
                ob_start(function($content) use($path__, $NS, &$out) {
                    $content = Hook::NS($NS . 'input', [$path__], $content);
                    $out = Hook::NS($NS . 'output', [$path__], $content);
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
        Hook::fire($NS . 'after', [$G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $s = explode('-', $code, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page_type', $s);
        HTTP::status((int) $s);
        self::attach($code, $fail, $buffer);
    }

    public static function exist($name, $fail = false) {
        return Folder::exist(SHIELD . DS . $name, $fail);
    }

}