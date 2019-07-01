<?php

Route::_('content', function(string $path, array $lot = []) {
    if (null !== ($content = Content::get($path, $lot, false))) {
        $this->put($content);
    } else if (defined('DEBUG') && DEBUG) {
        Guard::abort('Content <code>' . $path . '</code> does not exist.');
    }
});