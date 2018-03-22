<?php

// Store page state to registry…
if ($state = Extend::state('page')) {
    Config::alt($state);
}

$path = $url->path;
$p = Config::get('path');
$i = $url->i;
$folder = PAGE . DS . $path;

// Set proper `i` value in `$url` if we have some page with numeric file name
if ($i && File::exist([
    $folder . DS . $i . '.page',
    $folder . DS . $i . '.archive'
])) {
    $GLOBALS['URL']['path'] .= '/' . $i;
    $GLOBALS['URL']['clean'] .= '/' . $i;
    $GLOBALS['URL']['i'] = null;
}

$if_0 = $path === "" || $path === $p;

$if_1 = File::exist([
    // `lot\page\home.{page,archive}`
    PAGE . DS . $p . '.page',
    PAGE . DS . $p . '.archive'
]);

$if_2 = File::exist([
    // `lot\page\foo\bar\1.{page,archive}`
    $folder . DS . $i . '.page',
    $folder . DS . $i . '.archive',
    // `lot\page\foo\bar\1\$.{page,archive}`
    $folder . DS . $i . DS . '$.page',
    $folder . DS . $i . DS . '$.archive',
    // `lot\page\foo\bar\$.{page,archive}`
    $folder . DS . '$.page',
    $folder . DS . '$.archive'
]);

$if_3 = File::exist([
    // `lot\page\foo\bar.{page,archive}`
    $folder . '.page',
    $folder . '.archive',
    $if_2
]);

$if_4 = File::explore([PAGE . DS . $p, 'page,archive']);
$if_5 = File::explore([$folder, 'page,archive']);

Config::set('is', [
    '$' => $if_0,
    'error' => $path === "" && !$if_1 || $path !== "" && !$if_3 ? 404 : false,
    'home' => $if_0, // alias for `$`
    'page' => $path === "" && $if_1 || $path !== "" && $if_3,
    'pages' => $path === "" && $if_4 || $path !== "" && !$if_2 && $if_5,
    'search' => Request::is('get', Config::get('q'))
]);

$pages = $path === "" ? count($if_4) : count($if_5);
Config::set('has', [
    'next' => Config::is('pages') && $pages > ($i ?: 1) * Config::page('chunk', 5),
    'page' => Config::is('page') ? 1 : 0,
    'pages' => Config::is('pages') && $pages ? $pages : 0,
    'parent' => strpos($path, '/') !== false && !Config::is('error'),
    'previous' => Config::is('pages') && $i > 1,
    'step' => Config::is('pages') && $i !== null ? $i : false
]);