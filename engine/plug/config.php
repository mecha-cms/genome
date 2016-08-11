<?php

Config::plug('start', function() {
    $config = State::config();
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
    $protocol = $scheme . '://';
    $host = $_SERVER['HTTP_HOST'];
    $sub = str_replace(DS, '/', dirname($_SERVER['SCRIPT_NAME']));
    $sub = $sub === '.' ? "" : trim($sub, '/');
    $url = rtrim($protocol . $host  . '/' . $sub, '/');
    $s = preg_replace('#[<>"]|[?&].*$#', "", trim($_SERVER['QUERY_STRING'], '/')); // Remove HTML tag(s) and query string(s) from URL
    $path = trim(str_replace('/?', '?', $_SERVER['REQUEST_URI']), '/') === $sub . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $s;
    $current = rtrim($url . '/' . $path, '/');
    $config['url'] = [
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
    if (!$lang = File::exist(LANGUAGE . DS . $config['language'] . DS . 'speak.txt')) {
        $lang = File::exist(LANGUAGE . DS . 'en-us' . DS . 'speak.txt');
    }
    $config['__i18n'] = From::yaml(File::open($lang)->read(""));
    Config::set($config);
});

Config::plug('url', function($key = 'url', $fail = false) {
    return Config::get($key !== true ? 'url.' . $key : 'url', $fail);
});