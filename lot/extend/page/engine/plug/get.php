<?php namespace fn\get;

function pages($folder = PAGE, $state = 'page', $sort = [-1, 'time'], $key = null) {
    $pages = \Anemon::eat(\g($folder, $state))->not(function($v) {
        return pathinfo($v, PATHINFO_FILENAME) === '$';
    })->map(function($v) use($key, $sort) {
        $key = is_array($sort) && isset($sort[1]) ? $sort[1] : $key ?? X;
        return (new \Page($v, [], false))->get(['path' => $v, $key => null]);
    })->sort($sort);
    return $key ? $pages->pluck($key) : $pages;
}

\Get::_('pages', __NAMESPACE__ . "\\pages");