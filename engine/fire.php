<?php

d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['extensions'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Session::ignite();
Config::ignite();

$seeds = [
    'config' => new Config,
    'date' => new Date,
    'language' => new Language,
    'url' => new URL
];

// plant and extract ...
extract(Seed::set($seeds)->get(null, []));

r(EXTEND . DS . '*', '{index.php,index__.php,__index.php}', function($f) use($seeds) {
    extract($seeds);
    $i18n = Path::D($f) . DS . 'lot' . DS . 'language';
    if (!$l = File::exist($i18n . DS . $config->language . '.txt')) {
        $l = $i18n . DS . 'en-us.txt';
    }
    Language::set(From::yaml($l));
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}, $seeds);

r(SHIELD . DS . $config->shield, '{index.php,index__.php,__index.php}', function($f) use($seeds) {
    $f = Path::D($f) . DS . 'engine';
    d($f . DS . 'kernel', function($w, $n) use($f, $seeds) {
        $f .= DS . 'plug' . DS . $n . '.php';
        if (file_exists($f)) {
            extract($seeds);
            require $f;
        }
    }, $seeds);
}, $seeds);

function do_start() {
    Route::fire() and Shield::abort();
}

Hook::set('start', 'do_start')->fire('start');