<?php

// Alias for `State`
class_alias('State', 'Site');

// Alias for `$state`
$GLOBALS['site'] = $site = $state;

// Default title for the content
$GLOBALS['t'] = $t = new Anemon([$state->title], ' &#x00B7; ');

// Extend skin state(s) to the global state(s)
if (is_file($state = Content::$state['path'] . DS . 'state.php')) {
    State::over(require $state);
}