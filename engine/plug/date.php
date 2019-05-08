<?php

Date::_('en_us', '%A, %B %d, %Y');
Date::_('id_id', '%A, %d %B %Y');

Hook::set('start', function() {
    $key = strtr(Config::get('language') ?? "", '-', '_');
    // Fix for missing language key → default to `en_us`
    if (!Date::_($key)) {
        Date::_($key, Date::_('en_us'));
    }
}, 20);