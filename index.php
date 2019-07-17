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

define('DENT', '  '); // Default HTML indent
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

// Common HTML tag(s) allowed to be written in the form field
define('HTML_WISE_I', 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var');
define('HTML_WISE_B', 'address,blockquote,caption,dd,div,dl,dt,figcaption,figure,hr,h1,h2,h3,h4,h5,h6,li,ol,p,pre,table,tbody,tfoot,td,th,tr,ul');
define('HTML_WISE', HTML_WISE_I . ',' . HTML_WISE_B);

// Common date format
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DATE_LOCALE', locale_get_default());
define('DATE_NOW', $_SERVER['REQUEST_TIME'] ?? time());
define('DATE_ZONE', date_default_timezone_get());

require ENGINE . DS . 'f.php';
require ENGINE . DS . 'fire.php';