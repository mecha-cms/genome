<?php

class Shield extends Socket {

    protected static $lot = [];

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
        $config = Config::get();
        $token = Guardian::token();
        $output = [
            'config' => $config,
            'speak' => $config->__speak,
            'token' => $tok,
            'lot' => []
        ];
        foreach (glob(PAGE . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
            $v = Path::B($v);
            $output['lot'][$v . 's'] = $config->__lot->{$v . 's'} ?? [];
            $output['lot'][$v] = $config->__lot->{$v} ?? [];
        }
        Session::set(Guardian::$token, $token);
        unset($config, $token);
        self::$lot = array_merge(self::$lot, $output);
        return self::$lot;
    }

    public static function path($input, $fail = false) {
        $x = Path::X($input, "") !== 'php' ? '.php' : "";
        $input = To::path($input) . $x;
        // Full path, be quick!
        if (strpos($input, ROOT) === 0) {
            return File::exist($input, $fail);
        }
        if ($path = File::exist(SHIELD . DS . Config::shield() . DS . ltrim($input, DS))) {
            return $path;
        } elseif ($path = File::exist(ROOT . DS . ltrim($input, DS))) {
            return $path;
        }
        return $fail;
    }

    public static function lot($key = null, $fail = false) {
        if ($key === null) return self::$lot;
        if (!Is::anemon($key)) {
            return self::$lot[$key] ?? $fail;
        }
        self::$lot = array_merge(self::$lot, a($key));
        return new static;
    }

    public static function apart($data) {
        $data = (array) $data;
        foreach ($data as $d) {
            unset(self::$lot[$d]);
        }
        return new static;
    }

    public static function info($folder = null, $a = false) {
        $folder = $folder ?? Config::get('shield');
        $speak = Speak::get();
        // Check whether the localized "about" file is available
        if (!$info = File::exist(SHIELD . DS . $folder . DS . 'about.' . Config::get('language') . '.txt')) {
            $info = SHIELD . DS . $folder . DS . 'about.txt';
        }
        $info = (new Sheet)->open($info, 'content')->read([
            'id' => Folder::exist($folder),
            'title' => To::case_tt($folder),
            'author' => $speak->anonymous,
            'url' => '#',
            'version' => '0.0.0',
            'content' => Speak::get('notify_not_available', $speak->description)
        ], 'shield:');
        return $a ? $info : o($info);
    }

    public static function attach($input, $fail = false, $buffer = true) {
        $path__ = To::path($input);
        $s = explode('-', Path::N($input), 2);
        $G = ['name' => $input, 'name.base' => $s[0]];
        if (strpos($path__, ROOT) === 0 && is_file($path__)) {
            // do nothing ...
        } else {
            if ($_path = File::exist(self::path($path__, $fail))) {
                $path__ = $_path;
            } elseif ($_path = File::exist(self::path($s[0], $fail))) {
                $path__ = $_path;
            } else {
                exit(Speak::get('notify_file_not_exist', '<code>' . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo();
        $path__ = Hook::NS('shield:path', [], $path__);
        $G['lot'] = $lot__;
        $G['path'] = $path__;
        $G['path.base'] = $s[0];
        $out = "";
        // Begin shield
        Hook::fire('shield:lot.before', [$G, $G]);
        extract(Hook::NS('shield:lot', [], $lot__));
        Hook::fire('shield:lot.after', [$G, $G]);
        Hook::fire('shield:before', [$G, $G]);
        if ($path__) {
            if ($buffer) {
                ob_start(function($content) use($path__, &$out) {
                    $content = Hook::NS('shield:input', [$path__], $content);
                    $out = Hook::NS('shield:output', [$path__], $content);
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
        Hook::fire('shield:after', [$G, $G]);
        exit;
    }

    public static function abort($code = '404', $fail = false, $buffer = true) {
        $s = explode('-', $code, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page_type', $s);
        HTTP::status((int) $s);
        self::attach($code, $fail, $buffer);
    }

    public static function chunk($input, $fail = false, $buffer = true) {
        $path__ = To::path($input);
        $G = ['name' => $input];
        if (Is::anemon($fail)) {
            self::$lot = array_merge(self::$lot, $fail);
            $fail = false;
        }
        $path__ = Hook::NS('chunk:path', [], self::path($path__, $fail));
        $G['lot'] = self::$lot;
        $G['path'] = $path__;
        $out = "";
        if ($path__) {
            // Begin chunk
            Hook::fire('chunk:lot.before', [$G, $G]);
            extract(Hook::fire('chunk:lot', [], self::$lot));
            Hook::fire('chunk:lot.after', [$G, $G]);
            Hook::fire('chunk:before', [$G, $G]);
            if ($buffer) {
                ob_start(function($content) use($path__, &$out) {
                    $content = Hook::NS('chunk:input', [$path__], $content);
                    $out = Hook::NS('chunk:output', [$path__], $content);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['content'] = $out;
            // End chunk
            Hook::fire('chunk:after', [$G, $G]);
        }
    }

    public static function exist($name, $fail = false) {
        return Folder::exist(SHIELD . DS . $name, $fail);
    }

}