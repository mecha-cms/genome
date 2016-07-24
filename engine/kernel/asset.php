<?php

/**
 * Assets
 * ------
 *
 * ~~~ .php
 * echo Asset::shell('path/to/file.css');
 * echo Asset::shell('path/to/file.css', ['type' => 'text/css']);
 * echo Asset::shell(['path/to/file-1.css', 'path/to/file-2.css']);
 * ~~~
 *
 */

class Asset extends __ {

    public $assets = [];
    public $assets_x = [];

    // Get full version of private asset path
    public function path($path, $fail = false) {
        // External URL, nothing to check!
        if (strpos($path, '://') !== false || strpos($path, '//') === 0 || strpos($path, ':') !== false) {
            // Fix broken external URL
            $path = Path::url($path);
            // Check if URL very external ...
            if (strpos($path, URL::url()) !== 0) return $path;
        }
        // ... else, try parse it into private asset path
        $path = File::path($path);
        // Full path, be quick!
        if (strpos($path, ROOT) === 0) {
            return File::exist($path, $fail);
        }
        return File::exist(
            ASSET . DS . ltrim($path, DS),
            File::exist(ROOT . DS . ltrim($path, DS),
            $fail
        ));
    }

    // Get public asset URL
    public function url($input, $fail = false) {
        $input = Filter::dot('asset.input', $input);
        $path = Filter::dot('asset.path', $this->path($input), [$input]);
        $url = Path::url($path);
        if ($path && strpos($path, ROOT) === false) {
            return strpos($url, '://') !== false || strpos($url, '//') === 0 ? Filter::dot('asset.url', $url, [$input]) : $fail;
        }
        return $path && file_exists($path) ? Filter::dot('asset.url', $url, [$input]) : $fail;
    }

    // Common ...
    private function _create($input, $addon, $C, $F, $S) {
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        $I = I . I;
        $html = "";
        for ($i = 0, $count = count($input); $i < $count; ++$i) {
            $url = $this->url($input[$i]);
            if ($url !== false) {
                $this->assets[$input[$i]] = 1;
                if (is_array($addon)) {
                    if (!isset($addon[0])) {
                        $attr = Cell::bond($addon);
                    } else {
                        $attr = $addon[$i] ?? end($addon) ?? "";
                    }
                } else {
                    $attr = ' ' . ltrim($addon);
                }
                if (is_array($attr)) {
                    $attr = Cell::bond($attr);
                }
                $s = sprintf($C, $url, $attr) . N;
                $s = $S ? $I . $s : $s;
                $html .= !$this->ignored($input[$i]) ? Filter::apply('asset.' . $F, $s, [$input[$i], $url]) : "";
            } else {
                // File does not exist
                $s = '<!-- ' . $input[$i] . ' -->' . N;
                $html .= $S ? $I . $s : $s;
            }
        }
        return CELL_BEGIN . rtrim($S ? substr($html, strlen($I)) : $html, N) . CELL_END;
    }

    // Return the HTML stylesheet of asset
    public function shell($input, $addon = "") {
        return $this->_create($input, $addon, '<link href="%s" rel="stylesheet"%s' . ES, __FUNCTION__, true);
    }

    // Return the HTML javascript of asset
    public function sword($input, $addon = "") {
        return $this->_create($input, $addon, '<script src="%s"%s></script>', __FUNCTION__, true);
    }

    // Return the HTML image of asset
    public function trope($input, $addon = "") {
        return $this->_create($input, $addon, '<img src="%s"%s' . ES, __FUNCTION__, false);
    }

    // Merge multiple asset file(s) into a single file
    public static function merge($path, $name = null, $addon = "", $fn = null) {
        $cache = strpos($name, ROOT) === 0 ? File::path($name) : ASSET . DS . File::path($name);
        $log = CACHE . DS . 'asset.' . md5($cache) . '.log';
        $is_valid = true;
        if ( !file_exists($log)) {
            $is_valid = false;
        } else {
            $time = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($time) !== count($path)) {
                $is_valid = false;
            } else {
                foreach ($time as $i => $u) {
                    if ($p = Filter::colon('asset:path', self::path($path[$i], false))) {
                        if ( !file_exists($p) || (int) filemtime($p) !== (int) $u) {
                            $is_valid = false;
                            break;
                        }
                    }
                }
            }
        }
        $unix = "";
        $content = "";
        $e = File::E($name);
        if ( !$is_valid || !file_exists($cache)) {
            File::open($cache)->delete(); // delete cache ...
            if (Mecha::walk(array('gif', 'jpeg', 'jpg', 'png'))->has($e)) {
                $images = [];
                foreach ($path as $p) {
                    $p = Filter::colon('asset:source', $p);
                    if ( !self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        if ( !file_exists($p)) continue;
                        $unix .= filemtime($p) . "\n";
                        $images[] = $p;
                    }
                }
                if ( !empty($images)) {
                    File::write(substr($unix, 0, -1))->saveTo($log);
                    Image::take($images)->merge()->saveTo($cache);
                }
            } else {
                foreach ($path as $p) {
                    $p = Filter::colon('asset:source', $p);
                    if ( !self::ignored($p) && $p = Filter::colon('asset:path', self::path($p, false))) {
                        if ( !file_exists($p)) continue;
                        $unix .= filemtime($p) . "\n";
                        $c = Filter::apply('asset:input', file_get_contents($p) . "\n", $p);
                        if (strpos(File::B($p), '.min.') === false) {
                            if (substr($cache, -8) === '.min.css') {
                                $c = Converter::detractShell($c);
                            } else if (substr($cache, -7) === '.min.js') {
                                $c = Converter::detractSword($c);
                            } else {
                                $c = $c . "\n";
                            }
                            $content .= Filter::apply('asset:output', $c, $p);
                        } else {
                            $content .= Filter::apply('asset:output', $c . "\n", $p);
                        }
                    }
                }
                if ($content = trim($content)) {
                    File::write(substr($unix, 0, -1))->saveTo($log);
                    File::write($content)->saveTo($cache);
                }
            }
        }
        if (is_null($fn)) {
            $fn = Mecha::alter($e, array(
                'css' => 'stylesheet',
                'js' => 'javascript',
                'gif' => 'image',
                'jpeg' => 'image',
                'jpg' => 'image',
                'png' => 'image'
            ));
        }
        return call_user_func('self::' . $fn, $cache, $addon);
    }

    // Check for loaded asset(s)
    public static function loaded($path = null, $fail = false) {
        if ( !is_null($path)) {
            return isset(self::$assets[$path]) ? $path : $fail;
        }
        return !empty(self::$assets) ? self::$assets : $fail;
    }

    // alias for `Asset::loaded()`
    public static function exist($path = null, $fail = false) {
        return self::loaded($path, $fail);
    }

    // Do not let the `Asset` loads these file(s) ...
    public static function ignore($path) {
        if (is_array($path)) {
            foreach ($path as $p) {
                self::$assets_x[$p] = isset(self::$assets[$p]) ? self::$assets[$p] : 1;
            }
        } else {
            self::$assets_x[$path] = isset(self::$assets[$path]) ? self::$assets[$path] : 1;
        }
    }

    // Check for ignored asset(s)
    public static function ignored($path = null, $fail = false) {
        if ( !is_null($path)) {
            return isset(self::$assets_x[$path]) ? self::$assets_x[$path] : $fail;
        }
        return !empty(self::$assets_x) ? self::$assets_x : $fail;
    }

}