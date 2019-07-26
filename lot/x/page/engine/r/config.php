<?php

// Store page state to registry…
$state = state('page');
if (!empty($state['page'])) {
    // Prioritize default state
    Config::over($state);
}

$path = trim($url->path ?? "", '/');
$i = $url->i ?? "";
$p = $state['/'];
$folder = PAGE . DS . $path;

// Set proper `i` value in `$url` if we have some page with numeric file/folder name
if ($i !== "" && File::exist([
    $folder . DS . $i . '.page',
    $folder . DS . $i . '.archive'
])) {
    $path = $path . '/' . $i;
    $folder .= DS . $i;
    $url->clean .= '/' . $i;
    $url->i = null;
    $url->path = '/' . $path;
    $i = "";
}

$if_0 = $i === "" && ($path === "" || $path === $p);

$if_1 = File::exist([
    // `.\lot\page\home-slug.{page,archive}`
    PAGE . DS . $p . '.page',
    PAGE . DS . $p . '.archive'
]);

$if_2 = File::exist([
    // `.\lot\page\page-slug\.{page,archive}`
    $folder . DS . '.page',
    $folder . DS . '.archive'
]);

$if_3 = File::exist([
    // `.\lot\page\page-slug.{page,archive}`
    $folder . '.page',
    $folder . '.archive'
]);

$if_4 = glob(PAGE . DS . $p . DS . '*.page', GLOB_NOSORT);
$if_5 = glob($folder . DS . '*.page', GLOB_NOSORT);

$folder = dirname($folder);
$if_6 = File::exist([
    // `.\lot\page\parent-slug.{page,archive}`
    $folder . '.page',
    $folder . '.archive',
    $folder . DS . '.page',
    $folder . DS . '.archive'
]);

Config::set('is', [
    'error' => $path === "" && !$if_1 || $path !== "" && !$if_3 ? 404 : false,
    'home' => $if_0,
    'page' => $path === "" && $if_1 || $path !== "" && ($if_2 || $if_3 && !$if_5),
    'pages' => $i !== "" || $path === "" && $if_4 || $path !== "" && !$if_2 && $if_5
]);

$count = count($path === "" ? $if_4 : $if_5);

Config::set('has', [
    'next' => Config::is('pages') && ($count > (($i ?: 1) * ($state['chunk'] ?? 5))),
    'page' => $path === "" && $if_1 || $if_3,
    'pages' => $count > 0,
    'parent' => strpos($path, '/') !== false, // `foo/bar`
    'prev' => Config::is('pages') && $i > 1,
    'i' => Config::is('pages') && $i !== ""
]);

Config::set('not', []);