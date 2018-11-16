<?php

/**
 * =========================================
 *  MECHA · CONTENT MANAGEMENT SYSTEM (CMS)
 * =========================================
 * © 2014 – 2018 Taufik Nurrohman
 * -----------------------------------------
 */

!defined('DS') && define('DS', DIRECTORY_SEPARATOR); // Default directory separator
!defined('DENT') && define('DENT', '  '); // Default HTML indent
!defined('N') && define('N', "\n"); // Line break
!defined('T') && define('T', "\t"); // Tab
!defined('X') && define('X', "\x1A"); // Placeholder text

!defined('GROUND') && define('GROUND', rtrim(strtr($_SERVER['DOCUMENT_ROOT'], '/', DS), DS));
!defined('ROOT') && define('ROOT', __DIR__);
!defined('ENGINE') && define('ENGINE', ROOT . DS . 'engine');
!defined('LOT') && define('LOT', ROOT . DS . 'lot');

foreach (glob(ROOT . DS . 'lot' . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $lot) {
    $b = strtoupper(str_replace(['-', '.'], ['_', "\\"], basename($lot)));
    !defined($b) && define($b, $lot);
    $b = "LOT\\" . $b; // Alias
    !defined($b) && define($b, $lot);
}

!defined('SESSION') && define('SESSION', null); // Change to a folder path to define `session_save_path`
!defined('DEBUG') && define('DEBUG', false); // Change to `true` to enable debug mode

// Common HTML tag(s) allowed to be written in the form field
!defined('HTML_WISE_I') && define('HTML_WISE_I', 'a,abbr,b,br,cite,code,del,dfn,em,i,ins,kbd,mark,q,span,strong,sub,sup,time,u,var');
!defined('HTML_WISE_B') && define('HTML_WISE_B', 'address,blockquote,caption,dd,div,dl,dt,figcaption,figure,hr,h1,h2,h3,h4,h5,h6,li,ol,p,pre,table,tbody,tfoot,td,th,tr,ul');
!defined('HTML_WISE') && define('HTML_WISE', HTML_WISE_I . ',' . HTML_WISE_B);

// Common date format
!defined('DATE_WISE') && define('DATE_WISE', 'Y-m-d H:i:s');

// Common file type(s) allowed to be uploaded by the file manager
!defined('AUDIO_X') && define('AUDIO_X', 'aif,mid,mov,mpa,mp3,m3u,m4a,ogg,wav,wma');
!defined('FONT_X') && define('FONT_X', 'eot,fnt,fon,otf,svg,ttf,woff,woff2');
!defined('IMAGE_X') && define('IMAGE_X', 'bmp,cur,gif,ico,jpeg,jpg,png,svg');
!defined('PACKAGE_X') && define('PACKAGE_X', 'cbr,gz,iso,pkg,rar,rpm,tar,zip,zipx,7z');
!defined('TEXT_X') && define('TEXT_X', 'archive,cache,cfg,css,csv,data,draft,htaccess,html,js,json,log,page,php,srt,stack,tex,trash,txt,xml,yaml,yml');
!defined('VIDEO_X') && define('VIDEO_X', 'avi,flv,mkv,mov,mpg,mp4,m4a,m4v,ogv,rm,swf,vob,webm,wmv,3gp,3g2');
!defined('BINARY_X') && define('BINARY_X', AUDIO_X . ',' . PACKAGE_X . ',' . VIDEO_X . ',doc,docx,odt,pdf,ppt,pptx,rtf,xlr,xls,xlsx');

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'fire.php';