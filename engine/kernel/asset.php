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

class Asset extends Socket {

    public static $log = [];

    // Get full version of private asset path
    public static function path($input, $fail = false) {
        $url = Config::get('url');
        // External URL, nothing to check!
        if (strpos($input, '://') !== false || strpos($input, '//') === 0 || strpos($input, ':') !== false) {
            // Fix broken external URL
            $input = To::url($input);
            // Check if URL very external ...
            if (strpos($input, $url) !== 0) return $input;
        }
        // ... else, try parse it into private asset path
        $input = To::path($input);
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
        $input = Hook::NS('asset:input', [], $input);
        $path = Hook::NS('asset:path', [$input], self::path($input));
        $url = To::url($path);
        if (!$path) return $fail;
        if (strpos($path, ROOT) === false) {
            return strpos($url, '://') !== false || strpos($url, '//') === 0 ? Hook::NS('asset:url', [$input], $url) : $fail;
        }
        return file_exists($path) ? Hook::NS('asset:url', [$input], $url) : $fail;
    }

    // Generate skeleton ...
    public static function genome($input, $addon, $unite, $C, $F, $T) {
        if ($unite !== false && strlen($unite)) {
            return self::unite($input, $unite, $addon, $F);
        }
        $I = I . I;
        $cell = "";
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        for ($i = 0, $count = count($input); $i < $count; ++$i) {
            $url = self::url($input[$i]);
            if ($url !== false) {
                self::$log[1][$input[$i]] = $input[$i];
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
                $cell .= !self::blocked($input[$i]) ? Hook::NS('asset:' . $F, [$input[$i], $url], $s) : "";
            } else {
                // File does not exist
                $s = '<!-- ' . $input[$i] . ' -->' . N;
                $cell .= $T ? $I . $s : $s;
            }
        }
        return CELL_BEGIN . rtrim($T ? substr($cell, strlen($I)) : $cell, N) . CELL_END;
    }

    // Return the HTML stylesheet of asset
    public static function shell($input, $addon = "", $unite = false) {
        return self::genome($input, $addon, '<link href="%s" rel="stylesheet"%s' . ES, $unite, __METHOD__, true);
    }

    // Return the HTML javascript of asset
    public static function sword($input, $addon = "", $unite = false) {
        return self::genome($input, $addon, '<script src="%s"%s></script>', $unite, __METHOD__, true);
    }

    // Return the HTML image of asset
    public static function trope($input, $addon = "", $unite = false) {
        return self::genome($input, $addon, '<img src="%s"%s' . ES, $unite, __METHOD__, false);
    }

    // Group multiple asset file(s) into a single file
    public static function unite($input, $as = null, $addon = "", $fn = null) {
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        $as = To::path($as);
        $cache = strpos($as, ROOT) === 0 ? $as : ASSET . DS . $as;
        $cache_log = SCRAP . DS . 'asset.' . md5($cache) . '.log';
        $ok = file_exists($cache) && file_exists($cache_log);
        $logs = $ok ? file($cache_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (count($logs) === count($input)) {
            foreach ($logs as $k => $v) {
                if ($i = Hook::NS('asset:path', [], self::path($input[$k]))) {
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
            if (Is::these(['gif', 'jpeg', 'jpg', 'png'])->has($x)) {
                $tropes = [];
                foreach ($input as $i) {
                    $i = Hook::NS('asset:input', [], $i);
                    if (!self::blocked($i) && $i = Hook::NS('asset:path', [$i], self::path($i))) {
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
                    $i = Hook::NS('asset:input', [], $i);
                    if (!self::blocked($i) && $i = Hook::NS('asset:path', [$i], self::path($i))) {
                        if (!file_exists($i)) continue;
                        $unix .= filemtime($i) . N;
                        $c = Hook::NS('asset:source.input', [$i], trim(file_get_contents($i)) . N);
                        $content .= Hook::NS(['asset:source.output', 'asset:source'], [$i], $c);
                    }
                }
                if ($content = trim($content)) {
                    File::write(substr($unix, 0, -1))->saveTo($cache_log);
                    File::write($content)->saveTo($cache);
                }
            }
        }
        if ($fn === null) {
            $fn = Anemon::alter($x, [
                'css' => 'shell',
                'gif' => 'trope',
                'jpeg' => 'trope',
                'jpg' => 'trope',
                'js' => 'sword',
                'png' => 'trope'
            ]);
        }
        return call_user_func([$this, $fn], $cache, $addon);
    }

    // Check for loaded asset(s)
    public static function loaded($input = null, $fail = false) {
        if ($input !== null) {
            return self::$log[1][$input] ?? $fail;
        }
        return !empty(self::$log[1]) ? self::$log[1] : $fail;
    }

    // Check if asset does exist
    public static function exist($input, $fail = false) {
        return self::url($input, null) ?? $fail;
    }

    // Do not let the `Asset` loads these file(s) ...
    public static function block($input) {
        foreach ((array) $input as $i) {
            self::$log[0][$i] = self::$log[1][$i] ?? 1;
        }
    }

    // Check for blocked asset(s)
    public static function blocked($input, $fail = false) {
        if ($input !== null) {
            return self::$log[0][$input] ?? $fail;
        }
        return !empty(self::$log[0]) ? self::$log[0] : $fail;
    }

}