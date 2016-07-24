<?php

define('I', '  '); // Default indent
define('N', "\n"); // Line break
define('R', "\r"); // Return
define('S',  ' '); // Space
define('T', "\t"); // Tab
define('X', "\x1A"); // Placeholder text

define('ES', '>');

define('CELL_BEGIN', ""); // Begin HTML output
define('CELL_END', N); // End HTML output

function a($o) {
    return (array) $o;
}

function o($a) {
    return (object) $a;
}