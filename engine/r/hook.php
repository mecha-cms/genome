<?php

function hook(...$v) {
    return count($v) < 2 ? Hook::get(...$v) : Hook::set(...$v);
}

register_shutdown_function(function() {
    // Load extension(s)…
    require __DIR__ . DS . 'x.php';
    // Run main task if any…
    if (is_file($f = ROOT . DS . 'task.php')) {
        require $f;
    }
    // Fire!
    Hook::fire('start');
});