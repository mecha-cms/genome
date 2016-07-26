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

class Asset extends DNA {

    public $log = [];

    // Get full version of private asset path
    public function path($input, $fail = false) {
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
    public function url($input, $fail = false) {
        $input = Filter::NS('asset:input', $input);
        $path = Filter::NS('asset:path', $this->path($input), [$input]);
        $url = Path::url($path);
        if (!$path) return $fail;
        if (strpos($path, ROOT) === false) {
            return strpos($url, '://') !== false || strpos($url, '//') === 0 ? Filter::NS('asset:url', $url, [$input]) : $fail;
        }
        return file_exists($path) ? Filter::NS('asset:url', $url, [$input]) : $fail;
    }

    // Generate skeleton ...
    public function genome($input, $addon, $unite, $C, $F, $T) {
        if ($unite !== false && strlen($unite)) {
            return $this->unite($input, $unite, $addon, $F);
        }
        $I = I . I;
        $cell = "";
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        for ($i = 0, $count = count($input); $i < $count; ++$i) {
            $url = $this->url($input[$i]);
            if ($url !== false) {
                $this->log[1][$input[$i]] = $input[$i];
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
                $cell .= !$this->blocked($input[$i]) ? Filter::NS('asset:' . $F, $s, [$input[$i], $url]) : "";
            } else {
                // File does not exist
                $s = '<!-- ' . $input[$i] . ' -->' . N;
                $cell .= $T ? $I . $s : $s;
            }
        }
        return CELL_BEGIN . rtrim($T ? substr($cell, strlen($I)) : $cell, N) . CELL_END;
    }

    // Return the HTML stylesheet of asset
    public function shell($input, $addon = "", $unite = false) {
        return $this->genome($input, $addon, '<link href="%s" rel="stylesheet"%s' . ES, $unite, __METHOD__, true);
    }

    // Return the HTML javascript of asset
    public function sword($input, $addon = "", $unite = false) {
        return $this->genome($input, $addon, '<script src="%s"%s></script>', $unite, __METHOD__, true);
    }

    // Return the HTML image of asset
    public function trope($input, $addon = "", $unite = false) {
        return $this->genome($input, $addon, '<img src="%s"%s' . ES, $unite, __METHOD__, false);
    }

    // Group multiple asset file(s) into a single file
    public function unite($input, $as = null, $addon = "", $fn = null) {
        $input = is_string($input) ? explode(' ', $input) : (array) $input;
        $as = URL::path($as);
        $cache = strpos($as, ROOT) === 0 ? $as : ASSET . DS . $as;
        $cache_log = SCRAP . DS . 'asset.' . md5($cache) . '.log';
        $ok = file_exists($cache) && file_exists($cache_log);
        $logs = $ok ? file($cache_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (count($logs) === count($input)) {
            foreach ($logs as $k => $v) {
                if ($i = Filter::NS('asset:path', $this->path($input[$k]))) {
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
            if (Anemon::walk(['gif', 'jpeg', 'jpg', 'png'])->has($x)) {
                $tropes = [];
                foreach ($input as $i) {
                    $i = Filter::NS('asset:input', $i);
                    if (!$this->blocked($i) && $i = Filter::NS('asset:path', $this->path($i), [$i])) {
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
                    if (!$this->blocked($i) && $i = Filter::NS('asset:path', $this->path($i), [$i])) {
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
    public function loaded($input = null, $fail = false) {
        if ($input !== null) {
            return $this->log[1][$input] ?? $fail;
        }
        return !empty($this->log[1]) ? $this->log[1] : $fail;
    }

    // Check if asset does exist
    public function exist($input, $fail = false) {
        return $this->url($input, null) ?? $fail;
    }

    // Do not let the `Asset` loads these file(s) ...
    public function block($input) {
        foreach ((array) $input as $i) {
            $this->log[0][$i] = $this->log[1][$i] ?? 1;
        }
    }

    // Check for blocked asset(s)
    public function blocked($input, $fail = false) {
        if ($input !== null) {
            return $this->log[0][$input] ?? $fail;
        }
        return !empty($this->log[0]) ? $this->log[0] : $fail;
    }

}