<?php

define('PLUGIN', dirname(__DIR__));

d(PLUGIN . DS . '*', '{index.php,index__.php,__index.php}', function($f) {
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            require $f;
        }
    });
});