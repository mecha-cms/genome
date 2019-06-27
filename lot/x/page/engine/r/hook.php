<?php namespace _\page;

function url($url = "", array $lot = []) {
    if (!$path = $this->path) {
        return $url;
    }
    $path = \Path::R(\Path::F($path), PAGE, '/');
    return \trim($GLOBALS['URL']['$'] . '/' . $path, '/');
}

\Hook::set('page.url', __NAMESPACE__ . "\\url", 2);