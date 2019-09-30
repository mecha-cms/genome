<?php

Route::_('_content', function(string $v) {
    echo ($v = Hook::fire('content', [$v], $this)); // The response body
    exit;
});

Route::_('content', function(string $path, array $lot = []) {
    Hook::fire('set', [], $this); // Run just before response body
    if (null !== ($content = Content::get($path, $lot, false))) {
        $this->_content($content);
        Hook::fire('get', [$content], $this); // Run just after response body
        Hook::fire('let', [$content], $this); // Run after response body (to clear session, cookie, etc.)
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});