<?php

require __DIR__ . DS . 'kernel.php';

d(ENGINE . DS . 'kernel');

foreach (glob(ENGINE . DS . 'plug' . DS . '*.php') as $v) {
    if (class_exists(c(h(str_replace('\\', '.', pathinfo($v, PATHINFO_FILENAME)), '-', '.')))) {
        require $v;
    }
}

File::$config['file_extension_allow'] = array_unique(array_merge(FONT_X, IMAGE_X, MEDIA_X, PACKAGE_X, SCRIPT_X));

Config::start();

$config = Gene\Config::_();
$i18n = Gene\I18N::_();