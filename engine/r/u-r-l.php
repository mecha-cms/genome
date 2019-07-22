<?php

$port = (int) $_SERVER['SERVER_PORT'];
$scheme = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $port === 443 ? 's' : "");
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$path = $_GET['//'] ?? "";
$query = explode('&', $_SERVER['QUERY_STRING'], 2)[1] ?? "";

unset($_GET['//']);

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
}

// Put this CMS in a sub-folder?
$directory = strtr(ROOT, [
    GROUND => "",
    DS => '/'
]);

$directory = $directory !== "" ? $directory : null;
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
    'clean' => $u . $path,
    'current' => trim($u . $path . '/' . $i, '/') . $query . $hash,
    'hash' => $hash,
    'root' => $u,
    'ground' => $protocol . $host
];

$GLOBALS['url'] = $url = new URL;