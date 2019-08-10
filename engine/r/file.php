<?php

function move(string $from, string $to) {
    return open($from)->move($to);
}

function open(string $from) {
    if (is_file($from)) {
        return new File($from);
    }
    if (is_dir($from)) {
        return new Folder($from);
    }
    return false;
}

function put($in, string $to, $seal = null) {
    $file = new File($to);
    $file->set($in);
    $file->save($seal);
    return $file;
}

function take(string $from) {
    if (is_file($from)) {
        $open = new File($from);
    }
    if (is_dir($from)) {
        $open = new Folder($from);
    }
    return $open->let();
}