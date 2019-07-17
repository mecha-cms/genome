<?php

Route::_('_content', function(string $v) {
    Hook::fire('set', [], $this);
    echo Hook::fire('content', [$v], $this);
    Hook::fire('get', [], $this);
    exit;
});

Route::_('content', function(string $path, array $lot = []) {
    if (null !== ($content = Content::get($path, $lot, false))) {
        $this->_content($content);
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});