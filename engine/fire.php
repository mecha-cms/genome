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
File::$config['extension'] = array_unique(explode(',', $x));

Session::ignite();
Config::ignite(STATE . DS . 'config.php');

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
$GLOBALS['URL'] = [
    'scheme' => $scheme,
    'protocol' => $protocol,
    'host' => $host,
    'directory' => $directory,
    'port' => (int) $_SERVER['SERVER_PORT'],
    'user' => $_SESSION['url']['user'] ?? null,
    'pass' => $_SESSION['url']['pass'] ?? null,
    'path' => $path,
    'i' => $i,
    '$' => $url,
    'clean' => $clean,
    'current' => trim($clean . '/' . $i, '/'),
    'query' => $query,
    'previous' => $_SESSION['url']['previous'] ?? null,
    'next' => $_SESSION['url']['next'] ?? null,
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
$message = new Message;

$seeds = [
    'config' => $config,
    'date' => $date,
    'language' => $language,
    'message' => $message,
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
        extract(Lot::get());
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