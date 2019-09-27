<?php

Date::_('en', '%A, %B %d, %Y');

Hook::set('start', function() {
    $key = strtr(State::get('language') ?? "", '-', '_');
    // Fix for missing language key → default to `en`
    if (!Date::_($key)) {
        Date::_($key, Date::_('en'));
    }
}, 20);