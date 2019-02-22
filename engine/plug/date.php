<?php

Date::_('en_us', '%~D%, %~M% %D%, %Y%');
Date::_('id_id', '%~D%, %D% %~M% %Y%');

Hook::set('on.ready', function() {
    $key = strtr(Config::get('language') ?? "", '-', '_');
    // Fix for missing language key → default to `en_us`
    if (!Date::_($key)) {
        Date::_($key, Date::_('en_us'));
    }
}, 20);