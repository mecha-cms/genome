<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 * © 2014 – 2019 · Taufik Nurrohman
 * -----------------------------------------
 */

!defined('DS') && define('DS', DIRECTORY_SEPARATOR); // Default directory separator
!defined('PS') && define('PS', PATH_SEPARATOR); // Default path separator
!defined('DENT') && define('DENT', '  '); // Default HTML indent
!defined('N') && define('N', "\n"); // Line break
!defined('P') && define('P', "\x1A"); // Placeholder text
!defined('T') && define('T', "\t"); // Tab

!defined('GROUND') && define('GROUND', rtrim(strtr($_SERVER['DOCUMENT_ROOT'], '/', DS), DS));
!defined('ROOT') && define('ROOT', __DIR__);
!defined('ENGINE') && define('ENGINE', ROOT . DS . 'engine');
!defined('LOT') && define('LOT', ROOT . DS . 'lot');

foreach (glob(LOT . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $lot) {
    $b = strtoupper(strtr(basename($lot), '-.', "_\\"));
    !defined($b) && define($b, $lot);
    $b = "LOT\\" . $b; // Alias
    !defined($b) && define($b, $lot);
}

!defined('SESSION') && define('SESSION', null); // Change to a folder path to define `session_save_path`
!defined('DEBUG') && define('DEBUG', true); // Change to `true` to enable debug mode

// Common HTML tag(s) allowed to be written in the form field
!defined('HTML_WISE_I') && define('HTML_WISE_I', 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var');
!defined('HTML_WISE_B') && define('HTML_WISE_B', 'address,blockquote,caption,dd,div,dl,dt,figcaption,figure,hr,h1,h2,h3,h4,h5,h6,li,ol,p,pre,table,tbody,tfoot,td,th,tr,ul');
!defined('HTML_WISE') && define('HTML_WISE', HTML_WISE_I . ',' . HTML_WISE_B);

// Common date format
!defined('DATE_FORMAT') && define('DATE_FORMAT', 'Y-m-d H:i:s');
!defined('DATE_LOCALE') && define('DATE_LOCALE', locale_get_default());
!defined('DATE_NOW') && define('DATE_NOW', $_SERVER['REQUEST_TIME'] ?? time());
!defined('DATE_ZONE') && define('DATE_ZONE', date_default_timezone_get());

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'fire.php';