<?php namespace _\lot\x\page;

function pages(string $folder = PAGE, string $x = 'page', string $hook = "\\Pages") {
    $pages = [];
    foreach (\g($folder, $x) as $v) {
        if (\pathinfo($v, \PATHINFO_FILENAME) === "") {
            continue;
        }
        $pages[] = $v;
    }
    return new $hook($pages);
}

\Get::_('pages', __NAMESPACE__ . "\\pages");