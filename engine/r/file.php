<?php

function move(string $path, string $to) {
    return open($path)->moveTo($to);
}

function open(string $path) {
    if (is_file($path)) {
        return new File($path);
    }
    if (is_dir($path)) {
        return new Folder($path);
    }
    return false;
}

function put($in, string $path, $seal = null) {
    $file = new File($path);
    $file->set($in);
    $file->save($seal);
    return $file;
}

function take(string $path) {
    if (is_file($path)) {
        $open = new File($path);
    }
    if (is_dir($path)) {
        $open = new Folder($path);
    }
    return $open->let();
}