<?php

return [
    'path' => '/index',
    'zone' => date_default_timezone_get(),
    'locale' => extension_loaded('intl') ? locale_get_default() : null,
    'charset' => 'utf-8',
    'direction' => 'ltr',
    'title' => 'Site Title',
    'description' => 'Site description.',
    'language' => 'en',
    'name' => 'log'
];