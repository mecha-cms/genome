<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    ini_set('error_log', ENGINE . DS . 'log' . DS . 'error.log');
    if (DEBUG) {
        ini_set('max_execution_time', 300); // 5 minute(s)
        if (DEBUG === true) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
            ini_set('html_errors', 1);
        }
    } else if (DEBUG === false) {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
    }
}

// Normalize line-break
$vars = [&$_COOKIE, &$_GET, &$_POST, &$_REQUEST, &$_SESSION];
array_walk_recursive($vars, function(&$v) {
    $v = strtr($v, ["\r\n" => "\n", "\r" => "\n"]);
});

// Load class(es)…
d(($f = ENGINE . DS) . 'kernel', function($v, $name) use($f) {
    $f .= 'plug' . DS . $name . '.php';
    if (is_file($f)) {
        extract($GLOBALS, EXTR_SKIP);
        require $f;
    }
});

// Boot…
require __DIR__ . DS . 'r' . DS . 'anemon.php';
require __DIR__ . DS . 'r' . DS . 'blob.php';
require __DIR__ . DS . 'r' . DS . 'cache.php';
require __DIR__ . DS . 'r' . DS . 'config.php';
require __DIR__ . DS . 'r' . DS . 'cookie.php';
require __DIR__ . DS . 'r' . DS . 'date.php';
require __DIR__ . DS . 'r' . DS . 'file.php';
require __DIR__ . DS . 'r' . DS . 'guard.php';
require __DIR__ . DS . 'r' . DS . 'hook.php';
require __DIR__ . DS . 'r' . DS . 'h-t-t-p.php';
require __DIR__ . DS . 'r' . DS . 'language.php';
require __DIR__ . DS . 'r' . DS . 'mecha.php';
require __DIR__ . DS . 'r' . DS . 'route.php';
require __DIR__ . DS . 'r' . DS . 'session.php';
require __DIR__ . DS . 'r' . DS . 'u-r-l.php';

// Load extension(s)…
require __DIR__ . DS . 'r' . DS . 'extend.php';

// Run main task if any…
if (is_file($f = ROOT . DS . 'task.php')) {
    require $f;
}

// Fire!
Hook::fire('start');