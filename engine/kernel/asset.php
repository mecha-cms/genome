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

    public static $assets = [];
    public static $assets_x = [];

    // Get full version of private asset path
    public static function path($input, $fail = false) {
        // External URL, nothing to check!
        if (strpos($input, '://') !== false || strpos($input, '//') === 0 || strpos($input, ':') !== false) {
            // Fix broken external URL
            $input = Path::url($input);
            // Check if URL very external ...
            if (strpos($input, URL::url()) !== 0) return $input;
        }
        // ... else, try parse it into private asset path
        $input = URL::path($input);
        // Full path, be quick!
        if (strpos($input, ROOT) === 0) {
            return File::exist($input, $fail);
        }
        return File::exist(
            ASSET . DS . ltrim($input, DS),
            File::exist(ROOT . DS . ltrim($input, DS),
            $fail
        ));
    }

    // Get public asset URL
    public static function url($input, $fail = false) {
        $input = Filter::NS('asset:input', $input);
        $path = Filter::NS('asset:path', self::path($input), [$input]);
        $url = Path::url($path);
        if (!$path) return $fail;
        if (strpos($path, ROOT) === false) {
            return strpos($url, '://') !== false || strpos($url, '//') === 0 ? Filter::NS('asset:url', $url, [$input]) : $fail;
        }
        return file_exists($path) ? Filter::NS('asset:url', $url, [$input]) : $fail;
    }

    // Generate skeleton ...
    protected static function _skeleton($input, $addon, $unite, $C, $F, $T) {
        if ($unite !== false && strlen($unite)) {
            return self::unite($input, $unite, $addon, $F);
        }
        $I = I . I;
        $html = "";
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        for ($i = 0, $count = count($input); $i < $count; ++$i) {
            $url = self::url($input[$i]);
            if ($url !== false) {
                self::$assets[$input[$i]] = $input[$i];
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
                $s = $T ? $I . $s : $s;
                $html .= !self::ignored($input[$i]) ? Filter::apply('asset:' . $F, $s, [$input[$i], $url]) : "";
            } else {
                // File does not exist
                $s = '<!-- ' . $input[$i] . ' -->' . N;
                $html .= $T ? $I . $s : $s;
            }
        }
        return CELL_BEGIN . rtrim($T ? substr($html, strlen($I)) : $html, N) . CELL_END;
    }

    // Return the HTML stylesheet of asset
    public static function shell($input, $addon = "", $unite = false) {
        return self::_skeleton($input, $addon, '<link href="%s" rel="stylesheet"%s' . ES, $unite, __FUNCTION__, true);
    }

    // Return the HTML javascript of asset
    public static function sword($input, $addon = "", $unite = false) {
        return self::_skeleton($input, $addon, '<script src="%s"%s></script>', $unite, __FUNCTION__, true);
    }

    // Return the HTML image of asset
    public static function trope($input, $addon = "", $unite = false) {
        return self::_skeleton($input, $addon, '<img src="%s"%s' . ES, $unite, __FUNCTION__, false);
    }

    // Group multiple asset file(s) into a single file
    public static function unite($input, $as = null, $addon = "", $fn = null) {
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        $as = URL::path($as);
        $cache = strpos($as, ROOT) === 0 ? $as : ASSET . DS . $as;
        $cache_log = CACHE . DS . 'asset.' . md5($cache) . '.log';
        $ok = file_exists($cache) && file_exists($cache_log);
        $logs = $ok ? file($cache_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (count($logs) === count($input)) {
            foreach ($logs as $k => $v) {
                if ($i = Filter::NS('asset:path', self::path($input[$k]))) {
                    if (!file_exists($i) || (int) filemtime($i) !== (int) $v) {
                        $ok = false;
                        break;
                    }
                }
            }
        } else {
            $ok = false;
        }
        $unix = "";
        $content = "";
        $x = Path::X($as);
        if (!$ok) {
            if (Group::walk(['gif', 'jpeg', 'jpg', 'png'])->contain($x)) {
                $tropes = [];
                foreach ($input as $i) {
                    $i = Filter::NS('asset:input', $i);
                    if (!self::ignored($i) && $i = Filter::NS('asset:path', self::path($i), [$i])) {
                        if (!file_exists($i)) continue;
                        $unix .= filemtime($i) . N;
                        $tropes[] = $i;
                    }
                }
                if (!empty($tropes)) {
                    File::write(substr($unix, 0, -1))->saveTo($cache_log);
                    Image::take($tropes)->merge()->saveTo($cache);
                }
            } else {
                foreach ($input as $i) {
                    $i = Filter::NS('asset:input', $i);
                    if (!self::ignored($i) && $i = Filter::NS('asset:path', self::path($i), [$i])) {
                        if (!file_exists($i)) continue;
                        $unix .= filemtime($i) . N;
                        $c = Filter::NS('asset:source.input', file_get_contents($i) . N, [$i]);
                        $content .= Filter::NS(['asset:source.output', 'asset:source'], $c, [$i]);
                    }
                }
                if ($content = trim($content)) {
                    File::write(substr($unix, 0, -1))->saveTo($cache_log);
                    File::write($content)->saveTo($cache);
                }
            }
        }
        if ($fn === null) {
            $fn = Group::alter($x, [
                'css' => 'shell',
                'gif' => 'trope',
                'jpeg' => 'trope',
                'jpg' => 'trope',
                'js' => 'sword',
                'png' => 'trope'
            ]);
        }
        return call_user_func('self::' . $fn, $cache, $addon);
    }

    // Check for loaded asset(s)
    public static function loaded($input = null, $fail = false) {
        if ($input !== null) {
            return self::$assets[$input] ?? $fail;
        }
        return !empty(self::$assets) ? self::$assets : $fail;
    }

    // Check if asset does exist
    public static function exist($input, $fail = false) {
        return self::url($input, null) ?? $fail;
    }

    // Do not let the `Asset` loads these file(s) ...
    public static function ignore($input) {
        foreach ((array) $input as $i) {
            self::$assets_x[$i] = self::$assets[$i] ?? 1;
        }
    }

    // Check for ignored asset(s)
    public static function ignored($input, $fail = false) {
        if ($input !== null) {
            return self::$assets_x[$input] ?? $fail;
        }
        return !empty(self::$assets_x) ? self::$assets_x : $fail;
    }

}