<?php

Route::_('view', function(string $path, array $lot = []) {
    if (null !== ($view = Content::get($path, $lot, false))) {
        $this->content($view);
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});