<?php

Config::plug('fire', function() {
    $config = State::config();
    if (!$lang = File::exist(LANGUAGE . DS . $config->language . DS . 'speak.txt')) {
        $lang = File::exist(LANGUAGE . DS . 'en-us' . DS . 'speak.txt');
    }
    Config::set('__speak', From::yaml(File::open($lang)->read("")));
    return Config::get();
});

Config::plug('speak', function($key = null, $lot = []) {
    if ($key === null) return Config::get('__speak', []);
});

Config::plug('url', function($key = 'url', $fail = false) {
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
    $protocol = $scheme . '://';
    $host = $_SERVER['HTTP_HOST'];
    $sub = str_replace(DS, '/', dirname($_SERVER['SCRIPT_NAME']));
    $sub = $sub === '.' ? "" : trim($sub, '/');
    $url = rtrim($protocol . $host  . '/' . $sub, '/');
    $s = preg_replace('#[<>"]|[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/')); // Remove HTML tag(s) and query string(s) from URL
    $path = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $sub . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $s;
    $current = rtrim($url . '/' . $path, '/');
    $output = [
        'scheme' => $scheme,
        'protocol' => $protocol,
        'host' => $host,
        'port' => (int) $_SERVER['SERVER_PORT'],
        'user' => null,
        'pass' => null,
        'sub' => $sub,
        'url' => $url,
        'path' => $path,
        'query' => null,
        'current' => $current,
        'origin' => Session::get('url.origin', null),
        'hash' => null
    ];
    return $key !== true ? ($output[$key] ?? $fail) : o($output);
});