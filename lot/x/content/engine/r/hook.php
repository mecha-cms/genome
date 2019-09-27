<?php

namespace _\lot\x\content {
    function alert($content) {
        if (\strpos($content, '</alert>') !== false) {
            return \preg_replace_callback('#(?:\s*<alert(?:\s[^>]+)?>[\s\S]*?<\/alert>\s*)+#', function($m) {
                return '<div class="alert p">' . \str_replace([
                    '<alert type="',
                    '</alert>'
                ], [
                    '<p class="',
                    '</p>'
                ], $m[0]) . '</div>';
            }, $content);
        }
        return $content;
    }
    function are() {
        foreach ((array) \State::get('are', true) as $k => $v) {
            \State::set('[content].are:' . $k, $v);
        }
    }
    function has() {
        foreach ((array) \State::get('has', true) as $k => $v) {
            \State::set('[content].has:' . $k, $v);
        }
    }
    function is() {
        foreach ((array) \State::get('is', true) as $k => $v) {
            \State::set('[content].is:' . $k, $v);
        }
        if ($x = \State::get('is.error')) {
            \State::set('[content].error:' . $x, true);
        }
    }
    function not() {
        foreach ((array) \State::get('not', true) as $k => $v) {
            \State::set('[content].not:' . $k, $v);
        }
    }
    function start() {
        $folder = \Content::$state['root'] . \DS;
        // Run content task if any
        if (\is_file($task = $folder . 'task.php')) {
            (function($task) {
                extract($GLOBALS, \EXTR_SKIP);
                require $task;
            })($task);
        }
        // Load user function(s) from the current content folder if any
        if (\is_file($fn = $folder . 'index.php')) {
            (function($fn) {
                extract($GLOBALS, \EXTR_SKIP);
                require $fn;
            })($fn);
        }
        // Detect relative asset path to the `.\lot\content\*` folder
        if (\State::get('x.asset') !== null && $assets = \Asset::get()) {
            foreach ($assets as $k => $v) {
                foreach ($v as $kk => $vv) {
                    // Full path, no change!
                    if (
                        strpos($kk, \ROOT) === 0 ||
                        strpos($kk, '//') === 0 ||
                        strpos($kk, '://') !== false
                    ) {
                        continue;
                    }
                    // Relative to the `asset` folder of active content
                    if ($path = \Asset::path($folder . 'asset' . \DS . $kk)) {
                        \Asset::let($kk);
                        \Asset::set($path, $vv['stack']);
                    }
                }
            }
        }
    }
    \Hook::set('content', __NAMESPACE__, 20);
    \Hook::set('content', __NAMESPACE__ . "\\alert", 0);
    \Hook::set('content', __NAMESPACE__ . "\\are", 0);
    \Hook::set('content', __NAMESPACE__ . "\\has", 0);
    \Hook::set('content', __NAMESPACE__ . "\\is", 0);
    \Hook::set('content', __NAMESPACE__ . "\\not", 0);
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
                    $c = \array_unique(\array_merge($c, \array_keys(\array_filter((array) \State::get('[content]', true)))));
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