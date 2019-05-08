<?php

// Run main task if any
if (is_file($f = ROOT . DS . 'task.php')) {
    require $f;
}

// Fire!
Hook::fire('start');

// Load all route(s)…
Route::start();