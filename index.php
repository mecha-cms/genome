<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 * © 2014 – 2020 · Taufik Nurrohman
 * -----------------------------------------
 */

define('VERSION', '2.2.0'); // Current version
define('DS', DIRECTORY_SEPARATOR); // Default directory separator
define('PS', PATH_SEPARATOR); // Default path separator

define('N', PHP_EOL); // Line break
define('P', "\u{001A}"); // Placeholder character
define('S', "\u{200C}"); // Invisible character

define('GROUND', rtrim(strtr($_SERVER['CONTEXT_DOCUMENT_ROOT'] ?? $_SERVER['DOCUMENT_ROOT'], '/', DS), DS));
define('ROOT', __DIR__);
define('ENGINE', ROOT . DS . 'engine');
define('LOT', ROOT . DS . 'lot');

define('SESSION', null); // Change to a folder path to define `session_save_path`
define('DEBUG', true); // Change to `true` to enable debug mode

require ENGINE . DS . 'f.php';
require ENGINE . DS . 'fire.php';
