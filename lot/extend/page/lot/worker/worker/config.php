<?php

// Store page state to registry…
if ($state = Extend::state('page')) {
    // Prioritize current shield state
    if ($alt = File::open(SHIELD . DS . $config->shield . DS . 'state' . DS . 'config.php')->import()) {
        $state = extend($state, $alt);
    }
    // Prioritize default state
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
    $path = trim($path . '/' . $i, '/');
    $folder .= DS . $i;
    $GLOBALS['URL']['path'] = $path;
    $GLOBALS['URL']['clean'] .= '/' . $i;
    $GLOBALS['URL']['i'] = null;
}

$if_0 = $path === "" || $path === $p;

$if_1 = File::exist([
    // `lot\page\home-slug.{page,archive}`
    PAGE . DS . $p . '.page',
    PAGE . DS . $p . '.archive'
]);

$if_2 = File::exist([
    // `lot\page\page-slug\1.{page,archive}`
    $folder . DS . $i . '.page',
    $folder . DS . $i . '.archive',
    // `lot\page\page-slug\1\$.{page,archive}`
    $folder . DS . $i . DS . '$.page',
    $folder . DS . $i . DS . '$.archive',
    // `lot\page\page-slug\$.{page,archive}`
    $folder . DS . '$.page',
    $folder . DS . '$.archive'
]);

$if_3 = File::exist([
    // `lot\page\page-slug.{page,archive}`
    $folder . '.page',
    $folder . '.archive',
    $if_2
]);

$if_4 = glob(PAGE . DS . $p . DS . '*.page', GLOB_NOSORT);
$if_5 = glob($folder . DS . '*.page', GLOB_NOSORT);

$folder = Path::D($folder);
$if_6 = File::exist([
    // `lot\page\parent-slug.{page,archive}`
    $folder . '.page',
    $folder . '.archive',
    $folder . DS . '$.page',
    $folder . DS . '$.archive'
]);

Config::set('is', [
    '$' => $if_0 ? $if_1 : false,
    'error' => $path === "" && !$if_1 || $path !== "" && !$if_3 ? 404 : false,
    'home' => $if_0 ? $if_1 : false, // alias for `$`
    'page' => $path === "" && $if_1 || $path !== "" && $if_3 ? ($path === "" ? $if_1 : $if_3) : false,
    'pages' => $path === "" && $if_4 || $path !== "" && !$if_2 && $if_5 ? ($path === "" ? $if_4 : $if_5) : false,
    'search' => HTTP::is('get', Config::get('q'))
]);

$pages = $path === "" ? count($if_4) : count($if_5);

Config::set('has', [
    'next' => Config::is('pages') && $pages > ($i ?: 1) * Config::page('chunk', 5),
    'page' => Config::is('page') ? 1 : 0,
    'pages' => Config::is('pages') && $pages ? $pages : 0,
    'parent' => strpos($path, '/') !== false && $if_6 ? $if_6 : false,
    'previous' => Config::is('pages') && $i > 1,
    'step' => Config::is('pages') && $i !== null ? $i : false
]);

Config::set('not', []);