<?php

Route::_('content', function(string $v, $exit = true) {
    echo ($v = Hook::fire('content', [$v], $this)); // The response body
    $exit && exit;
});

Route::_('view', function(string $path, array $lot = []) {
    Hook::fire('set', [], $this); // Run just before response body
    if (null !== ($content = Layout::get($path, $lot))) {
        $this->content($content, false);
        Hook::fire('get', [$content], $this); // Run just after response body
        Hook::fire('let', [$content], $this); // Run after response body (to clear session, cookie, etc.)
        exit;
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Layout <code>' . $path . '</code> does not exist.');
    }
});