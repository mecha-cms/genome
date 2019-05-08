<?php

// Set default date time zone
Date::zone(Config::get('zone'));
Date::locale(Config::get('locale'));

$GLOBALS['date'] = $GLOBALS['d'] = new Date($_SERVER['REQUEST_TIME'] ?? time());