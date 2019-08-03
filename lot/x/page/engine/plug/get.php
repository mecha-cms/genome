<?php namespace _\lot\x\page;

function pages(string $folder = PAGE, string $x = 'page', $deep = 0, string $hook = "\\Pages") {
    $pages = [];
    foreach (\g($folder, $x, $deep) as $k => $v) {
        if (\pathinfo($k, \PATHINFO_FILENAME) === "") {
            continue;
        }
        $pages[] = $k;
    }
    return new $hook($pages);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");