<?php namespace fn\get;

function pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $k = is_array($sort) && isset($sort[1]) ? $sort[1] : 'path';
    $key = $key ?? $k;
    $pages = \Anemon::eat(\g($folder, $state))->not(function($v) {
        return pathinfo($v, PATHINFO_FILENAME) === '$';
    })->map(function($v) use($k, $key, $sort) {
        return (new \Page($v, [], false))->get(['path' => $v, $k => null]);
    })->sort($sort);
    return $pages->pluck($key);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");