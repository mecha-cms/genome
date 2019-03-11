<?php namespace fn\message;

function reset() {
    \Message::reset();
}

// Clear all message(s) on exit…
\Hook::set('exit', __NAMESPACE__ . "\\reset", 20);