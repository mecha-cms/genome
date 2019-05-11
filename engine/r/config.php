<?php

Config::load(STATE . DS . 'config.php');

function config(...$v) {
    return $GLOBALS['config'](...$v);
}

$GLOBALS['config'] = $config = new Config;