<?php

Date::_('en', '%A, %B %d, %Y');

Hook::set('start', function() {
    $key = strtr(Config::get('language') ?? "", '-', '_');
    // Fix for missing language key → default to `en_us`
    if (!Date::_($key)) {
        Date::_($key, Date::_('en'));
    }
}, 20);