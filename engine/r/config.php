<?php

Config::load(STATE . DS . 'config.php');

function config(...$v) {
    return $GLOBALS['config'](...$v);
}

$GLOBALS['config'] = $GLOBALS['c'] = new Config;