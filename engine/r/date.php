<?php

// Set default date time zone and locale
Date::zone($config->zone);
Date::locale($config->locale);

$GLOBALS['date'] = $date = new Date($_SERVER['REQUEST_TIME'] ?? time());