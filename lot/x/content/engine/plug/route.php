<?php

Route::_('_content', function(string $v) {
    Hook::fire('set', [], $this); // Run just before response body
    echo ($v = Hook::fire('content', [$v], $this)); // The response body
    Hook::fire('get', [$v], $this); // Run just after response body
    Hook::fire('let', [$v], $this); // Run after response body (to clear session, cookie, etc.)
    exit;
});

Route::_('content', function(string $path, array $lot = []) {
    if (null !== ($content = Content::get($path, $lot, false))) {
        $this->_content($content);
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});