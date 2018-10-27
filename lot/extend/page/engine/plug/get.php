<?php namespace fn\get;

function _page($v, $key) {
    return (new \Page($v, [], false))->get([
        'id' => null,
        'path' => $v,
        'slug' => \Path::N($v),
        'state' => \Path::X($v),
        'time' => null,
        'update' => null,
        $key => null
    ]);
}

function pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $pages = \Anemon::eat(\g($folder, $state))->not(function($v) {
        return pathinfo($v, PATHINFO_FILENAME) === '$';
    })->map(function($v) use($key, $sort) {
        return _page($v, is_array($sort) && isset($sort[1]) ? $sort[1] : $key);
    })->sort($sort);
    if ($key) {
        return $pages->pluck($key);
    }
    return $pages;
}

\Get::_('pages', __NAMESPACE__ . "\\pages");