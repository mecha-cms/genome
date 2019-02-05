<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    ini_set('error_log', ENGINE . DS . 'log' . DS . 'error.log');
    if (DEBUG) {
        ini_set('max_execution_time', 300); // 5 minute(s)
    }
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
$vars = [&$_COOKIE, &$_GET, &$_POST, &$_REQUEST, &$_SESSION];
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
File::$config['x'] = array_unique(explode(',', $x));

Session::start();

Config::load(STATE . DS . 'config.php');

// Generate static URL data
$scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] === 443 ? 'https' : 'http';
$protocol = $scheme . '://';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
$directory = strtr(dirname($_SERVER['SCRIPT_NAME']), DS, '/');
$directory = $directory === '.' ? "" : trim($directory, '/');
$url = trim($protocol . $host . '/' . $directory, '/');
$parts = explode('&', strtr($_SERVER['QUERY_STRING'], DS, '/'), 2);
$path = array_shift($parts);
$query = array_shift($parts);
// Prevent XSS attack where possible
$path = strtr(trim($path ?? "", '/'), [
    '<' => '%3C',
    '>' => '%3E',
    '&' => '%26',
    '"' => '%22'
]);
$query = $query ? '?' . $query : "";
$parts = explode('/', $path);
if (is_numeric(end($parts))) {
    $i = (int) array_pop($parts);
    $path = implode('/', $parts);
} else {
    if ($path !== "") {
        array_shift($_GET); // Remove path data from native URL query
    }
    $i = null;
}
$clean = trim($url . '/' . $path, '/');
$u = Session::get(URL::session, []);
$GLOBALS['URL'] = [
    'scheme' => $scheme,
    'protocol' => $protocol,
    'host' => $host,
    'directory' => $directory,
    'port' => (int) $_SERVER['SERVER_PORT'],
    'user' => $u['user'] ?? null,
    'pass' => $u['pass'] ?? null,
    'path' => $path,
    'i' => $i,
    '$' => $url,
    'clean' => $clean,
    'current' => trim($clean . '/' . $i, '/'),
    'query' => $query,
    'previous' => $u['previous'] ?? null,
    'next' => $u['next'] ?? null,
    'hash' => null // TODO
];

$config = new Config;
$url = new URL;

// Set default date time zone
Date::zone($config->zone);

// Set default document status
HTTP::status(200); // “OK”

// Set current language
Language::$config['id'] = $config->language ?? 'en-us';

$date = new Date;
$language = new Language;

$seeds = [
    'config' => $config,
    'date' => $date,
    'language' => $language,
    'url' => $url,
    'u_r_l' => $url // Alias for `$url`
];

// Plant…
Lot::set($seeds);

$extends = [];
foreach (g(EXTEND . DS . '*', 'index.php') as $v) {
    $extends[$v] = (float) File::open(Path::D($v) . DS . 'stack.data')->get(0, 10);
}

asort($extends);
extract($seeds, EXTR_SKIP);
Config::set('extend[]', $extends);
$files = [];
foreach ($extends as $k => $v) {
    $f = dirname($k) . DS;
    $ff = $f . 'lot' . DS . 'language' . DS;
    if ($ff = File::exist([
        $ff . $config->language . '.page',
        $ff . 'en-us.page'
    ])) {
        $files[] = $ff;
    }
    $f .= 'engine' . DS;
    d($f . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds, EXTR_SKIP);
            require $f;
        }
    }, $seeds);
}

// Load extension(s)’ language…
Language::set(Cache::hit($files, function($files): array {
    $out = [];
    foreach ($files as $file) {
        $fn = 'From::' . Page::apart($file, 'type', "");
        $content = Page::apart($file, 'content', "");
        $out = extend($out, is_callable($fn) ? (array) call_user_func($fn, $content) : []);
    }
    return $out;
}) ?? []);

// Run main task if any
if ($task = File::exist(ROOT . DS . 'task.php')) {
    include $task;
}

// Load extension(s)…
foreach (array_keys($extends) as $v) {
    call_user_func(function() use($v) {
        extract(Lot::get(), EXTR_SKIP);
        if ($k = File::exist(dirname($v) . DS . 'task.php')) {
            include $k;
        }
        require $v;
    });
}

// Document is ready
function ready() {
    // Load all route(s)…
    Route::fire();
    // Clear message(s)…
    Message::reset();
}

// Fire!
Hook::set('on.ready', 'ready', 20)->fire('on.ready');