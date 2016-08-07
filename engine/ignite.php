<?php

require __DIR__ . DS . 'fn.php';

spl_autoload_register(function($worker) {
    $worker = h(str_replace('\\', '.', $worker), '-', '.');
    $worker = ENGINE . DS . 'kernel' . DS . $worker . '.php';
    if (file_exists($worker)) require $worker;
});

foreach (glob(ENGINE . DS . 'plug' . DS . '*.php') as $plug) {
    require $plug;
}

$config = Config::fire();
$speak = Config::speak();