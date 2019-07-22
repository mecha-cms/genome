<?php namespace _\lot\x\page;

function u_r_l($u_r_l) {
    $path = $this->path;
    if (!$path || \strpos($path, PAGE . DS) !== 0) {
        return $u_r_l;
    }
    global $url;
    $path = \Path::R(\Path::F($path), PAGE, '/');
    return \trim($url . '/' . $path, '/');
}

\Hook::set('page.url', __NAMESPACE__ . "\\u_r_l", 2);