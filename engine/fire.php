<?php

// Enable/disable debug mode (default is `null`)
if (defined('DEBUG')) {
    ini_set('error_log', ENGINE . DS . 'log' . DS . 'error.log');
    if (DEBUG) {
        ini_set('max_execution_time', 300); // 5 minute(s)
        if (DEBUG === true) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
            ini_set('html_errors', 1);
        }
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

// Generate static URL data
call_user_func(function($v) {
    $port = (int) $v['SERVER_PORT'];
    $scheme = 'http' . (!empty($v['HTTPS']) && $v['HTTPS'] !== 'off' || $port === 443 ? 's' : "");
    $host = $v['HTTP_HOST'] ?? $v['SERVER_NAME'] ?? "";
    $a = explode('&', strtr($v['QUERY_STRING'], DS, '/'), 2);
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
    $directory = strtr(dirname($v['SCRIPT_NAME']), DS, '/');
    $directory = $directory !== '.' ? '/' . trim($directory, '/') : null;
    $path = $path !== "" ? '/' . $path : null;
    $query = $query !== "" ? '?' . $query : null;
    $u = $scheme . '://' . $host . $directory;
    $GLOBALS['URL'] = [
        'scheme' => $scheme,
        'host' => $host,
        'port' => $port,
        'directory' => $directory,
        'path' => $path,
        'i' => $i,
        'query' => $query,
        '$' => $u,
        'clean' => $u . $path,
        'current' => trim($u . $path . '/' . $i, '/') . $query,
        'hash' => $_COOKIE['hash'] ?? null
    ];
}, $_SERVER);

$f = ENGINE . DS;
d($f . 'kernel', function($c, $n) use($f) {
    $f .= 'plug' . DS . $n . '.php';
    if (is_file($f)) {
        require $f;
    }
});

$x = BINARY_X . ',' . FONT_X . ',' . IMAGE_X . ',' . TEXT_X;
File::$config['x'] = array_unique(explode(',', $x));

Session::start();

Config::load(STATE . DS . 'config.php');

$config = new Config;
$url = new URL;

// Set default date time zone
Date::zone($config->zone);

// Set default document status
HTTP::status(200); // “OK”
// Set default `X-Powered-By` value
HTTP::header('X-Powered-By', 'Mecha/' . Mecha::version);

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
foreach (glob(EXTEND . DS . '*' . DS . 'index.php', GLOB_NOSORT) as $v) {
    $b = basename($d = dirname($v));
    $extends[$v] = content($d . DS . $b) ?? $b;
}

// Sort by name
natsort($extends);
extract($seeds, EXTR_SKIP);
Config::set('extend[]', $extends = array_keys($extends));
$files = [];
foreach ($extends as $v) {
    $f = dirname($v) . DS;
    $ff = $f . 'lot' . DS . 'language' . DS;
    if ($ff = File::exist([
        $ff . $config->language . '.page',
        $ff . 'en-us.page'
    ])) {
        $files[] = $ff;
    }
    $f .= 'engine' . DS;
    d($f . 'kernel', function($c, $n) use($f, $seeds) {
        $f .= 'plug' . DS . $n . '.php';
        if (is_file($f)) {
            extract($seeds, EXTR_SKIP);
            require $f;
        }
    });
}

// Load extension(s)’ language…
Language::set(Cache::hit($files, function($files): array {
    $out = [];
    foreach ($files as $file) {
        $file = file_get_contents($file);
        $fn = 'From::' . Page::apart($file, 'type');
        $content = Page::apart($file, 'content');
        $out = extend($out, is_callable($fn) ? (array) call_user_func($fn, $content) : []);
    }
    return $out;
}) ?? []);

// Run main task if any
if (is_file($task = ROOT . DS . 'task.php')) {
    require $task;
}

// Load extension(s)…
foreach ($extends as $v) {
    call_user_func(function() use($v) {
        extract(Lot::get(), EXTR_SKIP);
        if (is_file($k = dirname($v) . DS . 'task.php')) {
            require $k;
        }
        require $v;
    });
}

// Fire!
Hook::fire('start');

// Load all route(s)…
Route::start();