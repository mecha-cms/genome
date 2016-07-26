<?php

// Check for valid JSON string
function __such_json__($x) {
    if (!is_string($x) || !trim($x)) return false;
    return (
        // Maybe an empty string, array or object
        $x === '""' ||
        $x === '[]' ||
        $x === '{}' ||
        // Maybe an encoded JSON string
        $x[0] === '"' ||
        // Maybe a flat array
        $x[0] === '[' ||
        // Maybe an associative array
        strpos($x, '{"') === 0
    ) && json_decode($x) !== null && json_last_error() !== JSON_ERROR_NONE;
}

// Check for valid serialize string
function __such_serialize__($x) {
    if(!is_string($x) || !trim($x)) return false;
    return $x === 'N;' || strpos($x, 'a:') === 0 || strpos($x, 'b:') === 0 || strpos($x, 'd:') === 0 || strpos($x, 'i:') === 0 || strpos($x, 's:') === 0 || strpos($x, 'O:') === 0;
}

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
    } elseif ($x === true) {
        return 'true';
    } elseif ($x === false) {
        return 'false';
    } elseif ($x === null) {
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
        } elseif (__such_json__($x) && $v = json_decode($input, true)) {
            return is_array($v) ? e($v) : $v;
        } elseif ($x[0] === '"' && substr($x, -1) === '"' || $x[0] === "'" && substr($x, -1) === "'") {
            return substr(substr($x, 1), 0, -1);
        }
        $xx = [
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
        ];
        return $xx[$x] ?? $x;
    } elseif (is_array($x) || is_object($x)) {
        foreach ($x as &$v) {
            $v = e($v);
        }
        unset($v);
    }
    return $x;
}