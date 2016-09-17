<?php

/**
 * =================================================================
 *  Mecha -- Content Management System (CMS)
 *  Copyright (c) 2014-2016 Taufik Nurrohman
 * =================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));

define('I', '  '); // Default indent
define('N', "\n"); // Line break
define('R', "\r"); // Return
define('S', "\x00a0"); // Non break space
define('T', "\t"); // Tab
define('V', "\v"); // Vertical space
define('X', "\x1A"); // Placeholder text

define('SESSION', null);

define('ENGINE', ROOT . DS . 'engine');
define('LOT', ROOT . DS . 'lot');

foreach (['asset', 'cache', 'extend', 'language', 'page', 'shield', 'state'] as $lot) {
    define(strtoupper($lot), LOT . DS . $lot);
}

define('HTML_BEGIN', ""); // Begin HTML output
define('HTML_END', N); // End HTML output

// Common HTML tag(s) allowed to be written in the form field
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

define('HTML_WISE', HTML_WISE_I + HTML_WISE_B);

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

require ENGINE . DS . 'fire.php';