<?php

/**
 * =================================================================
 *  Mecha -- Content Management System (CMS)
 *  Copyright (c) 2014-2016 Taufik Nurrohman <http://mecha-cms.com>
 * =================================================================
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));
define('GROUND', rtrim($_SERVER['DOCUMENT_ROOT'], DS));

define('SESSION', null);

define('ENGINE', ROOT . DS . 'engine');

define('LOT', ROOT . DS . 'lot');
define('EXTEND', LOT . DS . 'extend');
define('LANGUAGE', LOT . DS . 'language');
define('CHUNK', EXTEND . DS . 'chunk');
define('PLUGIN', EXTEND . DS . 'plugin');
define('ASSET', LOT . DS . 'asset');
define('CACHE', LOT . DS . 'cache');
define('SHIELD', LOT . DS . 'shield');
define('STATE', LOT . DS . 'state');
define('PAGE', LOT . DS . 'page');

define('I', '  '); // Default indent
define('N', "\n"); // Line break
define('R', "\r"); // Return
define('S', ' '); // Space
define('T', "\t"); // Tab
define('V', "\v"); // Vertical space
define('X', "\x1A"); // Placeholder text

define('CELL_V', '>'); // Stand-alone HTML element

define('CELL_O', ""); // Begin HTML output
define('CELL_C', N); // End HTML output

// Common HTML tag(s) allowed to be written in the form field
define('WISE_CELL_I', [
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

define('WISE_CELL_B', [
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

define('WISE_CELL', WISE_CELL_I + WISE_CELL_B);

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

require ENGINE . DS . 'set.php';
require ENGINE . DS . 'fire.php';