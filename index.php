<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', rtrim(__DIR__, DS));
define('GROUND', rtrim($_SERVER['DOCUMENT_ROOT'], DS));

define('CARGO', ROOT . DS . 'lot');
define('ASSET', CARGO . DS . 'assets');
define('CACHE', CARGO . DS . 'scraps');

define('I', '  '); // Default indent
define('N', "\n"); // Line break
define('R', "\r"); // Return
define('S',  ' '); // Space
define('T', "\t"); // Tab
define('V', "\v"); // Vertical space
define('X', "\x1A"); // Placeholder text

define('ES', '>');

define('CELL_BEGIN', ""); // Begin HTML output
define('CELL_END', N); // End HTML output

// Convert array to object
function a($o) {
    if (is_object($o) || is_array($o)) {
        $o = (array) $o;
        foreach ($o as &$oo) {
            $oo = a($oo);
        }
        unset($oo);
    }
    return $o;
}

// Convert object to array
function o($a, $safe = true) {
    if (is_array($a) || is_object($a)) {
        $a = (array) $a;
        $a = $safe && count($a) && array_keys($a) !== range(0, count($a) - 1) ? (object) $a : $a;
        foreach ($a as &$aa) {
            $aa = o($aa, $safe);
        }
        unset($aa);
    }
    return $a;
}

// Convert any data type to their string format
function s($x) {
    if (is_array($x) || is_object($x)) {
        foreach ($x as &$v) {
            $v = s($v);
        }
        unset($v);
        return $x;
    } else if ($x === true) {
        return 'true';
    } else if ($x === false) {
        return 'false';
    } else if ($x === null) {
        return 'null';
    }
    return (string) $x;
}

// Evaluate string format to their appropriate data type
function e($x) {
    if (is_string($x)) {
        if ($x === "") return $x;
        if (is_numeric($x)) {
            return strpos($x, '.') !== false ? (float) $x : (int) $x;
        } else if (Guardian::check($x)->is('json') && $v = json_decode($input, true)) {
            return is_array($v) ? e($v) : $v;
        } else if ($x[0] === '"' && substr($x, -1) === '"' || $x[0] === "'" && substr($x, -1) === "'") {
            return substr(substr($x, 1), 0, -1);
        }
        return Group::alter($x, array(
            'TRUE' => true,
            'FALSE' => false,
            'NULL' => null,
            'true' => true,
            'false' => false,
            'null' => null,
            'yes' => true,
            'no' => false,
            'on' => true,
            'off' => false
        ));
    } else if (is_array($x) || is_object($x)) {
        foreach ($x as &$v) {
            $v = e($v);
        }
        unset($v);
    }
    return $x;
}