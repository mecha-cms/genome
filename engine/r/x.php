<?php

function state(string $query) {
    $a = explode(':', $query, 2);
    if (isset($GLOBALS['X'][2][$query])) {
        return $GLOBALS['X'][2][$query];
    }
    if (is_file($f = X . DS . $a[0] . DS . 'index.php')) {
        $out = [];
        if (is_file($f = dirname($f) . DS . 'lot' . DS . 'state' . DS . ($a[1] ?? 'config') . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            $out = require $f;
        }
        return ($GLOBALS['X'][2][$query] = $out);
    }
    return null;
}

$uses = [];
$uses_x = $GLOBALS['X'][0] ?? [];
foreach (glob(X . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
    if (empty($uses_x[$v])) {
        $n = basename($r = dirname($v));
        $uses[$v] = content($r . DS . $n) ?? $n;
    }
}

// Sort by name
natsort($uses);
$GLOBALS['X'][1] = $uses = array_keys($uses);

// Load class(es)…
foreach ($uses as $v) {
    d(($f = dirname($v) . DS . 'engine' . DS) . 'kernel', function($v, $n) use($f) {
        $f .= 'plug' . DS . $n . '.php';
        if (is_file($f)) {
            extract($GLOBALS, EXTR_SKIP);
            require $f;
        }
    });
}

// Load extension(s)…
foreach ($uses as $v) {
    call_user_func(function() use($v) {
        extract($GLOBALS, EXTR_SKIP);
        if (is_file($k = dirname($v) . DS . 'task.php')) {
            require $k;
        }
        require $v;
    });
}