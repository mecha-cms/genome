<?php

Route::_('content', function(string $v, $exit = true) {
    echo ($v = Hook::fire('content', [$v], $this)); // The response body
    $exit && exit;
});

Route::_('lot', function(...$v) {
    return count($v) > 1 || is_array($v[0]) ? Lot::set(...$v) : Lot::get(...$v);
});

Route::_('status', function(...$v) {
    return Lot::status(...$v);
});

Route::_('type', function(...$v) {
    return Lot::type(...$v);
});

Route::_('view', function(string $path, array $lot = []) {
    if (null !== ($content = Layout::get($path, $lot))) {
        $this->content($content, false);
        exit;
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Layout <code>' . $path . '</code> does not exist.');
    }
});