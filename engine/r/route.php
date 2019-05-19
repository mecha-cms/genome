<?php

function route(...$v) {
    return count($v) < 2 ? Route::get(...$v) : Route::set(...$v);
}

// Load all route(s)…
Hook::set('start', function() {
    !error_get_last() && Route::start();
}, 100);