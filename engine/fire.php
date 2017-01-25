<?php

// enable/disable debug mode (default is `null`)
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

// normalize line–break
$vars = [&$_GET, &$_POST, &$_REQUEST, &$_COOKIE];
array_walk_recursive($vars, function(&$v) {
    $v = str_replace(["\r\n", "\r"], "\n", $v);
});

d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['extensions'] = array_unique(explode(',', FONT_X . ',' . IMAGE_X . ',' . MEDIA_X . ',' . PACKAGE_X . ',' . SCRIPT_X));

Session::ignite();
Config::ignite();

$config = new Config;
$url = new URL;

// set default date time zone
Date::zone($config->zone);

// must be set after date time zone set
Language::ignite();

$date = new Date;
$language = new Language;

$seeds = [
    'config' => $config,
    'date' => $date,
    'language' => $language,
    'site' => $config,
    'url' => $url,
    'u_r_l' => $url
];

// plant and extract …
extract(Lot::set($seeds)->get(null, []));

$extends = [];
foreach (g(EXTEND . DS . '*', '{index__,index}.php') as $v) {
    $extends[str_replace(EXTEND . DS, "", $v)] = (float) File::open(Path::D($v) . DS . 'index.stack')->get(0, 10);
}

asort($extends);

$extends = array_keys($extends);

foreach ($extends as $extend) {
    $f = EXTEND . DS . Path::D($extend);
    $i18n = $f . DS . 'lot' . DS . 'language';
    if (!$l = File::exist($i18n . DS . $config->language . '.page')) {
        $l = $i18n . DS . 'en-us.page';
    }
    if (file_exists($l)) {
        $i18n = new Page($l, [], 'language');
        $fn = 'From::' . l($i18n->type);
        Language::set(is_callable($fn) ? call_user_func($fn, $i18n->content) : $i18n->content);
    }
    $f = $f . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}

r(EXTEND, $extends, null, $seeds);

r(SHIELD . DS . $config->shield, '{index__,index}.php', function($f) use($seeds) {
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}, $seeds);

function do_fire() {
    Route::fire();
    Shield::abort();
}

Hook::set('fire', 'do_fire')->fire('fire');