<?php

/**
 * Mecha · Content Management System (CMS)
 * =======================================
 *
 * © 2014 – 2017 Taufik Nurrohman
 *
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));

define('DENT', '  '); // Default HTML indent
define('N', "\n"); // Line break
define('S', "\xA0"); // Non break space
define('T', "\t"); // Tab
define('X', "\x1A"); // Placeholder text

define('SESSION', null);
define('DEBUG', false); // Change to `true` to enable debug mode

define('ENGINE', ROOT . DS . 'engine');
define('LOT', ROOT . DS . 'lot');

foreach (glob(LOT . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $lot) {
    $s = strtoupper(str_replace(['-', '.'], ['_', '__'], basename($lot)));
    if (!defined($s)) {
        define($s, $lot);
    }
}

// Common HTML tag(s) allowed to be written in the form field
define('HTML_WISE_I', 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var');
define('HTML_WISE_B', 'address,blockquote,caption,dd,div,dl,dt,figcaption,figure,hr,h1,h2,h3,h4,h5,h6,li,ol,p,pre,table,tbody,tfoot,td,th,tr,ul');
define('HTML_WISE', HTML_WISE_I . HTML_WISE_B);

// Common date format
define('DATE_WISE', 'Y-m-d H:i:s');

// Common file type(s) allowed to be uploaded by the file manager
define('FONT_X', 'eot,otf,svg,ttf,woff,woff2');
define('IMAGE_X', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
define('MEDIA_X', '3gp,avi,flv,mkv,mov,mp3,mp4,m4a,m4v,ogg,swf,wav,wma');
define('PACKAGE_X', 'gz,iso,rar,tar,zip,zipx');
define('SCRIPT_X', 'archive,cache,css,data,draft,html,js,json,log,page,php,stack,txt,xml');

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'fire.php';