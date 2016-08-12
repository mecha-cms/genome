<?php

require __DIR__ . DS . 'kernel.php';

d(ENGINE . DS . 'kernel', function($w) {
    $f = ENGINE . DS . 'plug' . DS . $w . '.php';
    if (file_exists($f)) {
        require $f;
    }
});

File::$config['file_extension_allow'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Config::start();

$config = Genome\Config::_();
$i18n = Genome\I18N::_();