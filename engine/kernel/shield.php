<?php

class Shield extends DNA {

    protected static $lot = [];

    // Compare with current version
    public function version($info, $v = null) {
        if (is_string($info)) {
            $info = self::info($info)->version;
        } else {
            $info = (object) $info;
            $info = isset($info->version) ? $info->version : '0.0.0';
        }
        return Mecha::version($v, $info);
    }

    /**
     * Default Shortcut Variable(s)
     * ----------------------------
     */

    public function cargo() {
        $config = Config::get();
        $token = Guardian::token();
        $results = array(
            'config' => $config,
            'speak' => $config->speak,
            'pager' => $config->pagination,
            'manager' => Guardian::happy(),
            'token' => $token,
            'messages' => Notify::read(false),
            'message' => Notify::read(false)
        );
        foreach (glob(POST . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
            $v = Path::B($v);
            $results[$v . 's'] = isset($config->{$v . 's'}) ? $config->{$v . 's'} : false;
            $results[$v] = isset($config->{$v}) ? $config->{$v} : false;
        }
        Session::set(Guardian::$token, $token);
        unset($config, $token);
        $this->lot = array_merge($this->lot, $results);
        return $this->lot;
    }

    /**
     * ==========================================================
     *  GET SHIELD PATH BY ITS NAME
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    echo Shield::path('article');
     *
     * ----------------------------------------------------------
     *
     */

    public function path($name, $fail = false) {
        $e = File::E($name, "") !== 'php' ? '.php' : "";
        $name = URL::path($name) . $e;
        // Full path, be quick!
        if (strpos($name, ROOT) === 0) {
            return File::exist($name, $fail);
        }
        if ($path = File::exist(SHIELD . DS . Config::get('shield') . DS . ltrim($name, DS))) {
            return $path;
        } elseif ($path = File::exist(CHUNK . DS . ltrim($name, DS))) {
            return $path;
        } elseif ($path = File::exist(ROOT . DS . ltrim($name, DS))) {
            return $path;
        }
        return $fail;
    }

    /**
     * ==========================================================
     *  DEFINE/GET SHORTCUT VARIABLE(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::lot(array(
     *        'foo' => 'bar',
     *        'baz' => 'qux'
     *    ))->attach('page');
     *
     * ----------------------------------------------------------
     *
     *    $foo = Shield::lot('foo');
     *
     * ----------------------------------------------------------
     *
     */

    public function lot($key = null, $fail = false) {
        if (is_null($key)) return $this->lot;
        if ( !is_array($key)) {
            return isset($this->lot[$key]) ? $this->lot[$key] : $fail;
        }
        $this->lot = array_merge($this->lot, $key);
        return $this;
    }

    /**
     * ==========================================================
     *  UNDEFINE SHORTCUT VARIABLE(S)
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::lot($data)->apart('foo')->attach('page');
     *
     * ----------------------------------------------------------
     *
     *    Shield::lot($data)
     *          ->apart(array('foo', 'bar'))
     *          ->attach('page');
     *
     * ----------------------------------------------------------
     *
     */

    public function apart($data) {
        $data = (array) $data;
        foreach ($data as $d) {
            unset($this->lot[$d]);
        }
        return $this;
    }

    /**
     * ==========================================================
     *  GET SHIELD INFO
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    var_dump(Shield::info('normal'));
     *
     * ----------------------------------------------------------
     *
     */

    public function info($folder = null, $array = false) {
        $config = Config::get();
        $speak = Config::speak();
        if (is_null($folder)) {
            $folder = $config->shield;
        }
        // Check whether the localized "about" file is available
        if ( !$info = File::exist(SHIELD . DS . $folder . DS . 'about.' . $config->language . '.txt')) {
            $info = SHIELD . DS . $folder . DS . 'about.txt';
        }
        $info = Page::text(File::open($info)->read(), 'content', 'shield:', array(
            'id' => self::exist($folder) ? $folder : false,
            'title' => Text::parse($folder, '->title'),
            'author' => $speak->anon,
            'url' => '#',
            'version' => '0.0.0',
            'content' => Config::speak('notify_not_available', $speak->description)
        ));
        return $array ? $info : Mecha::O($info);
    }

    /**
     * ==========================================================
     *  RENDER A PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::attach('article');
     *
     * ----------------------------------------------------------
     *
     */

    public function attach($name, $fail = false, $buffer = true) {
        $path__ = URL::path($name);
        $s = explode('-', Path::N($name), 2);
        $G = array('data' => array('name' => $name, 'name_base' => $s[0]));
        if (strpos($path__, ROOT) === 0 && file_exists($path__) && is_file($path__)) {
            // do nothing ...
        } else {
            if ($_path = File::exist(self::path($path__, $fail))) {
                $path__ = $_path;
            } elseif ($_path = File::exist(self::path($s[0], $fail))) {
                $path__ = $_path;
            } else {
                Guardian::abort(Config::speak('notify_file_not_exist', '<code>' . $path__ . '</code>'));
            }
        }
        $lot__ = self::cargo();
        $path__ = Filter::apply('shield:path', $path__);
        $G['data']['lot'] = $lot__;
        $G['data']['path'] = $path__;
        $G['data']['path_base'] = $s[0];
        $out = "";
        // Begin shield
        Weapon::fire('shield_lot_before', array($G, $G));
        extract(Filter::apply('shield:lot', $lot__));
        Weapon::fire('shield_lot_after', array($G, $G));
        Weapon::fire('shield_before', array($G, $G));
        if ($buffer) {
            ob_start(function($content) use($path__, &$out) {
                $content = Filter::apply('shield:input', $content, $path__);
                $out = Filter::apply('shield:output', $content, $path__);
                return $out;
            });
            require $path__;
            ob_end_flush();
        } else {
            require $path__;
        }
        $G['data']['content'] = $out;
        // Reset shield lot
        $this->lot = [];
        // End shield
        Weapon::fire('shield_after', array($G, $G));
        exit;
    }

    /**
     * ==========================================================
     *  RENDER A 404 PAGE
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::abort();
     *
     * ----------------------------------------------------------
     *
     *    Shield::abort('404-custom');
     *
     * ----------------------------------------------------------
     *
     */

    public function abort($name = '404', $fail = false, $buffer = true) {
        $s = explode('-', $name, 2);
        $s = is_numeric($s[0]) ? $s[0] : '404';
        Config::set('page_type', $s);
        HTTP::status((int) $s);
        self::attach($name, $fail, $buffer);
    }

    /**
     * ==========================================================
     *  RENDER A SHIELD CHUNK
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    Shield::chunk('header');
     *
     * ----------------------------------------------------------
     *
     *    Shield::chunk('header', array('title' => 'Yo!'));
     *
     * ----------------------------------------------------------
     *
     */

    public function chunk($name, $fail = false, $buffer = true) {
        $path__ = URL::path($name);
        $G = array('data' => array('name' => $name));
        if (is_array($fail)) {
            $this->lot = array_merge($this->lot, $fail);
            $fail = false;
        }
        $path__ = Filter::apply('chunk:path', self::path($path__, $fail));
        $G['data']['lot'] = $this->lot;
        $G['data']['path'] = $path__;
        $out = "";
        if ($path__) {
            // Begin chunk
            Weapon::fire('chunk_lot_before', array($G, $G));
            extract(Filter::apply('chunk:lot', $this->lot));
            Weapon::fire('chunk_lot_after', array($G, $G));
            Weapon::fire('chunk_before', array($G, $G));
            if ($buffer) {
                ob_start(function($content) use($path__, &$out) {
                    $content = Filter::apply('chunk:input', $content, $path__);
                    $out = Filter::apply('chunk:output', $content, $path__);
                    return $out;
                });
                require $path__;
                ob_end_flush();
            } else {
                require $path__;
            }
            $G['data']['content'] = $out;
            // End chunk
            Weapon::fire('chunk_after', array($G, $G));
        }
    }

    /**
     * ==========================================================
     *  CHECK IF SHIELD ALREADY EXIST
     * ==========================================================
     *
     * -- CODE: -------------------------------------------------
     *
     *    if ($path = Shield::exist('normal')) { ... }
     *
     * ----------------------------------------------------------
     *
     */

    public function exist($name, $fail = false) {
        $name = SHIELD . DS . $name;
        return file_exists($name) && is_dir($name) ? $name : $fail;
    }

}