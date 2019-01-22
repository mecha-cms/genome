<?php namespace fn\get;

function pages(string $folder = PAGE, string $state = 'page', $sort = [-1, 'time'], string $key = null): \Anemon {
    $k = \is_array($sort) && isset($sort[1]) ? $sort[1] : 'path';
    $key = $key ?? $k;
    $pages = \Anemon::eat(\g($folder, $state))->not(function($v) {
        return \pathinfo($v, \PATHINFO_FILENAME) === '$';
    })->map(function($v) use($k, $key, $sort) {
        return (new \Page($v, [], false))->get([
            'path' => $v,
            $k => null,
            $key => null
        ]);
    })->sort($sort);
    return $pages->pluck($key);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");