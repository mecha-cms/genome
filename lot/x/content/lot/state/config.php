<?php

return [
    'zone' => date_default_timezone_get(),
    'locale' => extension_loaded('intl') ? locale_get_default() : null,
    'charset' => 'utf-8',
    'direction' => 'ltr',
    'language' => 'en',
    'name' => 'log'
];