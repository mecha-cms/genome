<?php

function move(string $from, string $to) {
    return open($from)->moveTo($to);
}

function open(string $path) {
    if (is_file($path)) {
        return File::from($path);
    }
    if (is_dir($path)) {
        return Folder::from($path);
    }
    return false;
}

function put(string $in, string $path, $consent = null) {
    return File::put($in)->saveTo($path, $consent);
}

function take(string $in) {
    return File::from($in)->let();
}

$x = BINARY_X . ',' . FONT_X . ',' . IMAGE_X . ',' . TEXT_X;
File::$config['x'] = array_unique(explode(',', $x));