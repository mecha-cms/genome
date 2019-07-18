<?php

namespace _\lot\x\content {
    function alert($content) {
        if (\strpos($content, '</alert>') !== false) {
            return \preg_replace_callback('#(?:\s*<alert(?:\s[^>]+)?>[\s\S]*?<\/alert>\s*)+#', function($m) {
                return '<div class="alerts p">' . \str_replace([
                    '<alert type="',
                    '</alert>'
                ], [
                    '<p class="alert alert-',
                    '</p>'
                ], $m[0]) . '</div>';
            }, $content);
        }
        return $content;
    }
    function start() {
        // Prepare current skin state
        $GLOBALS['state'] = $state = new \Anemon;
        // Load current skin state if any
        $folder = \Content::$config['root'] . DS;
        if (\is_file($f = $folder . 'state' . DS . 'config.php')) {
            $GLOBALS['state'] = $state = new \Anemon(require $f);
        }
        // Run skin task if any
        if (\is_file($task = $folder . 'task.php')) {
            include $task;
        }
        // Load user function(s) from the current skin folder if any
        if (\is_file($fn = $folder . 'index.php')) {
            \call_user_func(function() use($fn) {
                extract($GLOBALS, \EXTR_SKIP);
                require $fn;
            });
        }
        // Detect relative asset path to the `.\lot\content\*` folder
        if (\state('asset') !== null && $assets = \Asset::get()) {
            foreach ($assets as $k => $v) {
                foreach ($v as $kk => $vv) {
                    // Full path, no change!
                    if (
                        strpos($kk, ROOT) === 0 ||
                        strpos($kk, '//') === 0 ||
                        strpos($kk, '://') !== false
                    ) {
                        continue;
                    }
                    // Relative to the `asset` folder of active skin
                    if ($path = \Asset::path($folder . 'asset' . DS . $kk)) {
                        \Asset::let($kk);
                        \Asset::set($path, $vv['stack']);
                    }
                }
            }
        }
    }
    \Hook::set('content', __NAMESPACE__, 0);
    \Hook::set('content', __NAMESPACE__ . "\\alert", 0);
    \Hook::set('start', __NAMESPACE__ . "\\start", 0);
}

namespace _\lot\x {
    // Generate HTML class(es)
    function content($content) {
        $root = 'html';
        if (\strpos($content, '<' . $root . ' ') !== false) {
            return \preg_replace_callback('#<' . \x($root) . '(?:\s[^>]*)?>#', function($m) {
                if (
                    \strpos($m[0], ' class="') !== false ||
                    \strpos($m[0], ' class ') !== false ||
                    \substr($m[0], -7) === ' class>'
                ) {
                    $root = new \HTML($m[0]);
                    $c = $root['class'] === true ? [] : \preg_split('/\s+/', $root['class'] ?? "");
                    $cc = (array) \Config::get('[class]', true); // Put your custom class(es) here
                    foreach (['has', 'is', 'not'] as $key) {
                        foreach (\array_filter((array) \Config::get($key, true)) as $k => $v) {
                            $c[] = $key . '-' . $k;
                        }
                    }
                    if ($x = \Config::get('is.error')) {
                        $c[] = 'error:' . $x;
                    }
                    $c = \array_merge($c, \array_keys(\array_filter($cc)));
                    $c = \array_unique($c);
                    \sort($c);
                    $root['class'] = \trim(\implode(' ', $c));
                    return $root;
                }
                return $m[0];
            }, $content);
        }
        return $content;
    }
}