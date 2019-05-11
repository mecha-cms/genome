<?php

$port = (int) $_SERVER['SERVER_PORT'];
$scheme = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $port === 443 ? 's' : "");
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$a = explode('&', strtr($_SERVER['QUERY_STRING'], DS, '/'), 2);
$path = array_shift($a) ?? "";
$query = array_shift($a) ?? "";

// Prevent XSS attack where possible
$path = strtr(trim($path, '/'), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);

$a = explode('/', $path);
if (is_numeric(end($a))) {
    $i = (int) array_pop($a);
    $path = implode('/', $a);
} else {
    $i = null;
    if ($path !== "") {
        array_shift($_GET); // Remove path data from native URL query
    }
}

$directory = strtr(dirname($_SERVER['SCRIPT_NAME']), DS, '/');
$directory = $directory !== '.' ? '/' . trim($directory, '/') : null;
$path = $path !== "" ? '/' . $path : null;
$query = $query !== "" ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;
$u = $protocol . $host . $directory;

$GLOBALS['URL'] = [
    'scheme' => $scheme,
    'protocol' => $protocol,
    'host' => $host,
    'port' => $port,
    'directory' => $directory,
    'path' => $path,
    'i' => $i,
    'query' => $query,
    '$' => $u,
    'clean' => $u . $path,
    'current' => trim($u . $path . '/' . $i, '/') . $query . $hash,
    'hash' => $hash
];

$GLOBALS['url'] = $url = new URL;