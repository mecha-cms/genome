<?php namespace _\get;

function pages(string $folder = PAGE, string $x = 'page', $sort = [-1, 'time'], string $key = null): \Anemon {
    $k = \is_array($sort) && isset($sort[1]) ? $sort[1] : 'path';
    $key = $key ?? $k;
    $pages = [];
    foreach (\g($folder, $x) as $v) {
        if (\pathinfo($v, \PATHINFO_FILENAME) === '$') {
            continue;
        }
        $page = new \Page($v);
        $pages[] = [
            'path' => $v,
            $k => $page[$k],
            $key => $page[$key]
        ];
    }
    return \Anemon::eat($pages)->sort($sort)->pluck($key);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");