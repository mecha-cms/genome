<?php namespace _\get;

function pages(string $folder = PAGE, string $x = 'page', $sort = [1, 'path'], string $out = null): \Anemon {
    $k = \is_array($sort) && isset($sort[1]) ? $sort[1] : 'path';
    $out = $out ?? $k;
    $pages = [];
    foreach (\g($folder, $x) as $v) {
        if (\pathinfo($v, \PATHINFO_FILENAME) === '$') {
            continue;
        }
        $page = new \Page($v);
        $pages[] = [
            'path' => $v,
            $k => $page[$k],
            $out => $page[$out]
        ];
    }
    return \Anemon::from($pages)->sort($sort)->pluck($out);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");