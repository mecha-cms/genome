<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    ini_set('error_log', ENGINE . DS . 'log' . DS . 'error.log');
    if (DEBUG === true) {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        ini_set('html_errors', 1);
    } else if (DEBUG === false) {
        error_reporting(0);
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
    }
}

// Normalize line-break
$vars = [
    &$_COOKIE,
    &$_GET,
    &$_POST,
    &$_REQUEST,
    &$_SESSION
];
array_walk_recursive($vars, function(&$v) {
    $v = strtr($v, ["\r\n" => "\n", "\r" => "\n"]);
});

$f = ENGINE . DS;
d($f . 'kernel', function($w, $n) use($f) {
    $f .= 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

$x = BINARY_X . ',' . FONT_X . ',' . IMAGE_X . ',' . TEXT_X;
File::$config['extension'] = array_unique(explode(',', $x));

Session::ignite();
Config::ignite(STATE . DS . 'config.php');

// Set global URL data
$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$directory = strtr(dirname($_SERVER['SCRIPT_NAME']), DS, '/');
$directory = $directory === '.' ? "" : trim($directory, '/');
$url = rtrim($protocol . $host  . '/' . $directory, '/');
// [1]. Remove query string(s) and hash from URL
// [2]. Remove possible XSS attack from URL
$path = strtr(preg_replace('#[?&\#].*$#', "", trim($_SERVER['QUERY_STRING'], '/')), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);
$path = trim(strtr($_SERVER['REQUEST_URI'], ['/?' => '?']), '/') === $directory . '?' . trim($_SERVER['QUERY_STRING'], '/') ? "" : $path;
if ($path !== "") {
    array_shift($_GET);
}
$query = http_build_query($_GET);
$a = explode('/', rtrim($path, '/'));
$i = null;
if (is_numeric(end($a))) {
    $i = (int) array_pop($a);
    $path = implode('/', $a);
}
$clean = rtrim($url . '/' . $path, '/');
$GLOBALS['URL'] = [
    'i' => $i,
    'scheme' => $scheme,
    'protocol' => $protocol,
    'host' => $host,
    'port' => (int) $_SERVER['SERVER_PORT'],
    'user' => $_SESSION['url']['user'] ?? null,
    'pass' => $_SESSION['url']['pass'] ?? null,
    'directory' => $directory,
    '$' => $url,
    'path' => $path,
    'query' => $query ? '?' . $query : "",
    'previous' => $_SESSION['url']['previous'] ?? null,
    'next' => $_SESSION['url']['next'] ?? null,
    'clean' => $clean,
    'current' => rtrim($clean . '/' . $i, '/'),
    'hash' => null // TODO
];

$config = new Config;
$url = new URL;

// Set default date time zone
Date::zone($config->zone);

// Set default document status
HTTP::status(200); // “OK”

// Must be set after date time zone set
Language::ignite();

$date = new Date;
$language = new Language;

$seeds = [
    'config' => $config,
    'date' => $date,
    'language' => $language,
    'message' => Message::get("", false),
    'site' => $config,
    'token' => Guardian::token(0),
    'url' => $url,
    'u_r_l' => $url // alias for `url`
];

// Plant…
Lot::set($seeds);

$extends = [];
foreach (g(EXTEND . DS . '*', 'index.php') as $v) {
    $extends[$v] = (float) File::open(Path::D($v) . DS . 'stack.data')->get(0, 10);
}

asort($extends);
extract($seeds);
Config::set('extend[]', $extends);
$c = [];
foreach ($extends as $k => $v) {
    $f = Path::D($k) . DS;
    $i18n = $f . 'lot' . DS . 'language' . DS;
    if ($l = File::exist([
        $i18n . $config->language . '.page',
        $i18n . 'en-us.page'
    ])) {
        $c[$l] = filemtime($l);
    }
    $f .= 'engine' . DS;
    d($f . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}

$id = array_sum($c);
$content = Cache::expire(EXTEND, $id) ? Cache::set(EXTEND, function() use($c) {
    $content = [];
    foreach ($c as $k => $v) {
        $i18n = new Page($k, [], ['*', 'language']);
        $fn = 'From::' . $i18n->type;
        $v = $i18n->content;
        $content = extend($content, is_callable($fn) ? call_user_func($fn, $v) : (array) $v);
    }
    return $content;
}, $id) : Cache::get(EXTEND, []);

// Load extension(s)’ language…
Language::set($content);

// Run main task if any
if ($task = File::exist(ROOT . DS . 'task.php')) {
    include $task;
}

// Load extension(s)…
foreach (array_keys($extends) as $v) {
    call_user_func(function() use($v) {
        extract(Lot::get(null, []));
        if ($k = File::exist(dirname($v) . DS . 'task.php')) {
            include $k;
        }
        require $v;
    });
}

// Load all route(s)…
Hook::set('on.ready', 'Route::fire', 20)->fire('on.ready');