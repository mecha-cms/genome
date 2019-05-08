<?php

define('PLUGIN', __DIR__ . DS . 'lot');

require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'language.php';

call_user_func(function() {
    $plugins = [];
    foreach (glob(PLUGIN . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
        $b = basename($d = dirname($v));
        $plugins[$v] = content($d . DS . $b) ?? $b;
    }
    // Sort by name
    natsort($plugins);
    $GLOBALS['PLUGIN'] = $plugins = array_keys($plugins);
    // Load class(es)…
    foreach ($plugins as $v) {
        d(($f = dirname($v) . DS . 'engine' . DS) . 'kernel', function($v, $name) use($f) {
            $f .= 'plug' . DS . $name . '.php';
            if (is_file($f)) {
                extract($GLOBALS, EXTR_SKIP);
                require $f;
            }
        });
    }
    // Load plugin(s)…
    foreach ($plugins as $v) {
        call_user_func(function() use($v) {
            extract($GLOBALS, EXTR_SKIP);
            if (is_file($k = dirname($v) . DS . 'task.php')) {
                require $k;
            }
            require $v;
        });
    }
});