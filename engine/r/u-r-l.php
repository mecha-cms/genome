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

// Detect if user put this CMS in a sub-folder by checking the `directory` value
$directory = trim(($_SERVER['CONTEXT_PREFIX'] ?? "") . strtr(ROOT, [
    GROUND => "",
    DS => '/'
]), '/');

$directory = $directory !== "" ? '/' . $directory : null;
$path = $path !== "" ? '/' . $path : null;
$query = $query !== "" ? '?' . $query : null;
$hash = !empty($_COOKIE['hash']) ? '#' . $_COOKIE['hash'] : null;
$u = $protocol . $host . $directory;

$GLOBALS['url'] = $url = new URL([
    'clean' => $u . $path,
    'current' => trim($u . $path . '/' . $i, '/') . $query . $hash,
    'directory' => $directory,
    'ground' => $protocol . $host,
    'hash' => $hash,
    'host' => $host,
    'i' => $i,
    'path' => $path,
    'port' => $port,
    'protocol' => $protocol,
    'query' => $query,
    'root' => $u,
    'scheme' => $scheme
]);