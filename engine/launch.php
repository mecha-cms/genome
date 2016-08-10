<?php

require __DIR__ . DS . 'function.php';

d(ENGINE . DS . 'kernel');

foreach (glob(ENGINE . DS . 'plug' . DS . '*.php') as $w) {
    $c = h(str_replace('\\', '.', pathinfo($w, PATHINFO_FILENAME)), '-', '.');
    if (!class_exists($c)) continue;
    require $w;
}

$config = Config::fire();
$speak = Config::speak();var_dump($config);

require SHIELD . DS . $config->shield . DS . 'function.php';