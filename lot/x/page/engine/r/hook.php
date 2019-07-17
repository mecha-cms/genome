<?php namespace _\lot\x\page;

function u_r_l($url = "", array $lot = []) {
    if (!$path = $this->path) {
        return $url;
    }
    $path = \Path::R(\Path::F($path), PAGE, '/');
    return \trim($GLOBALS['URL']['$'] . '/' . $path, '/');
}

\Hook::set('page.url', __NAMESPACE__ . "\\u_r_l", 2);