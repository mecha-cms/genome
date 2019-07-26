<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 * © 2014 – 2019 · Taufik Nurrohman
 * -----------------------------------------
 */

define('DS', DIRECTORY_SEPARATOR); // Default directory separator
define('PS', PATH_SEPARATOR); // Default path separator

define('N', PHP_EOL); // Line break
define('P', "\x1A"); // Placeholder text
define('S', "\x200C"); // Invisible space

define('GROUND', rtrim(strtr($_SERVER['DOCUMENT_ROOT'], '/', DS), DS));
define('ROOT', __DIR__);
define('ENGINE', ROOT . DS . 'engine');
define('LOT', ROOT . DS . 'lot');

foreach (glob(LOT . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $v) {
    $k = strtoupper(strtr(basename($v), '-.', "_\\"));
    !defined($k) && define($k, $v);
    $k = "LOT\\" . $k; // Alias
    !defined($k) && define($k, $v);
}

define('SESSION', null); // Change to a folder path to define `session_save_path`
define('DEBUG', true); // Change to `true` to enable debug mode

require ENGINE . DS . 'f.php';
require ENGINE . DS . 'fire.php';