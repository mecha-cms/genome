<?php

function extend(string $query) {
    $a = explode(':', $query, 2);
    if (isset($GLOBALS['EXTEND'][1][$query])) {
        return $GLOBALS['EXTEND'][1][$query];
    }
    if (is_file($f = EXTEND . DS . $a[0] . DS . 'index.php')) {
        $out = [];
        if (is_file($f = dirname($f) . DS . 'lot' . DS . 'state' . DS . ($a[1] ?? 'config') . '.php')) {
            extract($GLOBALS, EXTR_SKIP);
            $out = require $f;
        }
        $out = Hook::fire('extend.state.' . strtr($query, '.', '/'), [$out]);
        return ($GLOBALS['EXTEND'][1][$query] = $out);
    }
    return null;
}

$extends = [];
foreach (glob(EXTEND . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
    $n = basename($dir = dirname($v));
    $extends[$v] = content($dir . DS . $n) ?? $n;
}

// Sort by name
natsort($extends);
$GLOBALS['EXTEND'][0] = $extends = array_keys($extends);

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