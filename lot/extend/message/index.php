<?php namespace _\message;

require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'language.php';

$GLOBALS['message'] = $GLOBALS['m'] = new \Message;

function let() {
    \Message::let();
}

// Clear all message(s) on exit…
\Hook::set('exit', __NAMESPACE__ . "\\let", 20);