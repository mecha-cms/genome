<?php

function hook(...$v) {
    return count($v) < 2 ? Hook::get(...$v) : Hook::set(...$v);
}

register_shutdown_function(function() {
    if (!error_get_last()) {
        // Run task(s) if any…
        if (is_file($f = ROOT . DS . 'task.php')) {
            (function($f) {
                extract($GLOBALS, EXTR_SKIP);
                require $f;
            })($f);
        }
        // Run extra task(s) if any…
        if (is_dir($f = ROOT . DS . 'task')) {
            foreach (glob($f . DS . '*.php', GLOB_NOSORT) as $f) {
                is_file($f) && (function($f) {
                    extract($GLOBALS, EXTR_SKIP);
                    require $f;
                })($f);
            }
        }
        // Fire!
        Hook::fire('start');
    }
});