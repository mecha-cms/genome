<?php

d(ENGINE . DS . 'kernel', function($w, $n) {
    $f = ENGINE . DS . 'plug' . DS . $n . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

// override–able function name
class fn extends Genome {}

File::$config['extensions'] = array_unique(explode(',', FONT_X . ',' . IMAGE_X . ',' . MEDIA_X . ',' . PACKAGE_X . ',' . SCRIPT_X));

Session::ignite();
Config::ignite();

$seeds = [
    'config' => new Config,
    'date' => new Date,
    'language' => new Language,
    'url' => new URL
];

// set default date time zone
Date::TZ($seeds['config']->TZ);

// plant and extract ...
extract(Seed::set($seeds)->get(null, []));

$extends = [];
foreach (g(EXTEND . DS . '*', '{index__,index,__index}.php') as $v) {
    $extends[str_replace(EXTEND . DS, "", $v)] = (float) File::open(Path::D($v) . DS . 'index.stack')->get(0, 10);
}

asort($extends);

r(EXTEND, array_keys($extends), function($f) use($seeds) {
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

r(SHIELD . DS . $config->shield, '{index__,index,__index}.php', function($f) use($seeds) {
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