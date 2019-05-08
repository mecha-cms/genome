<?php

$extends = [];
foreach (glob(EXTEND . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
    $n = basename($dir = dirname($v));
    $extends[$v] = content($dir . DS . $n) ?? $n;
}

// Sort by name
natsort($extends);
$GLOBALS['EXTEND'] = $extends = array_keys($extends);

// Load class(es)…
foreach ($extends as $v) {
    d(($f = dirname($v) . DS . 'engine' . DS) . 'kernel', function($v, $name) use($f) {
        $f .= 'plug' . DS . $name . '.php';
        if (is_file($f)) {
            extract($GLOBALS, EXTR_SKIP);
            require $f;
        }
    });
}

// Load extension(s)…
foreach ($extends as $v) {
    call_user_func(function() use($v) {
        extract($GLOBALS, EXTR_SKIP);
        if (is_file($k = dirname($v) . DS . 'task.php')) {
            require $k;
        }
        require $v;
    });
}