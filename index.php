<?php

/**
 * =================================================================
 *  Mecha -- Content Management System (CMS)
 *  Copyright (c) 2014-2016 Taufik Nurrohman
 * =================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));

define('I', '  '); // default indent
define('N', "\n"); // line break
define('R', "\r"); // return
define('S', "\x00a0"); // non break space
define('T', "\t"); // tab
define('V', "\v"); // vertical space
define('X', "\x1A"); // placeholder text

define('SESSION', null);

define('ENGINE', ROOT . DS . 'engine');
define('LOT', ROOT . DS . 'lot');

foreach (glob(LOT . DS . '*', GLOB_NOSORT | GLOB_ONLYDIR) as $lot) {
    define(strtoupper(str_replace(['-', '.'], ['_', '__'], basename($lot))), $lot);
}

define('HTML_WISE_I', [
    'a',
    'abbr',
    'b',
    'br',
    'cite',
    'code',
    'del',
    'dfn',
    'em',
    'i',
    'ins',
    'kbd',
    'mark',
    'q',
    'span',
    'strong',
    'sub',
    'sup',
    'time',
    'u',
    'var'
]);

define('HTML_WISE_B', [
    'address',
    'blockquote',
    'caption',
    'dd',
    'div',
    'dl',
    'dt',
    'figcaption',
    'figure',
    'hr',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'li',
    'ol',
    'p',
    'pre',
    'table',
    'tbody',
    'tfoot',
    'td',
    'th',
    'tr',
    'ul'
]);

// common HTML tag(s) allowed to be written in the form field
define('HTML_WISE', array_unique(array_merge(HTML_WISE_I, HTML_WISE_B)));

define('FONT_X', [
    'eot',
    'otf',
    'svg',
    'ttf',
    'woff',
    'woff2'
]);

define('IMAGE_X', [
    'bmp',
    'cur',
    'gif',
    'ico',
    'jpeg',
    'jpg',
    'png',
    'svg'
]);

define('MEDIA_X', [
    '3gp',
    'avi',
    'flv',
    'mkv',
    'mov',
    'mp3',
    'mp4',
    'm4a',
    'm4v',
    'ogg',
    'swf',
    'wav',
    'wma'
]);

define('PACKAGE_X', [
    'gz',
    'iso',
    'rar',
    'tar',
    'zip',
    'zipx'
]);

define('SCRIPT_X', [
    'archive',
    'cache',
    'css',
    'draft',
    'htaccess',
    'hold',
    'htm',
    'html',
    'js',
    'json',
    'jsonp',
    'log',
    'php',
    'txt',
    'xml'
]);

require ENGINE . DS . 'ignite.php';
require ENGINE . DS . 'fire.php';