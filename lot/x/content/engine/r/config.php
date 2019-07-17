<?php

// Set global state as default
Config::set($state = state('content'));

// Set global skin ID
if (isset($state['skin'])) {
    Content::$config['root'] = $f = CONTENT . DS . $state['skin'];
    // Override default state
    if (is_file($f .= DS . 'state' . DS . 'config.php')) {
        Config::set(require $f);
    }
}

// Alias for `Config`
class_alias('Config', 'Site');

// Alias for `$config`
$GLOBALS['site'] = $site = $config;

// Default title for the content
$GLOBALS['t'] = $t = new Anemon([$config->title], ' &#x00B7; ');