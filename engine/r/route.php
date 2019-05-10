<?php

Route::_('view', function(string $path, array $lot = []) {
    if (null !== ($view = Content::get($path, $lot, false))) {
        $this->content($view);
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});

function route(...$v) {
    return count($v) > 1 ? Route::set(...$v) : Route::get(...$v);
}

// Load all route(s)…
Hook::set('start', function() {
    !error_get_last() && Route::start();
}, 100);