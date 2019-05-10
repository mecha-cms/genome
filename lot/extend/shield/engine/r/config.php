<?php

// Alias for `Config`
class_alias('Config', 'Site');

// Set global shield ID
if ($id = Config::get('shield')) {
    Content::$config['folder'] = SHIELD . DS . $id;
}

// Alias for `$config`
$GLOBALS['site'] = $site = $config;