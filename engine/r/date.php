<?php

// Set default date time zone and locale
Date::zone($state->zone);
Date::locale($state->locale);

$GLOBALS['date'] = $date = new Date($_SERVER['REQUEST_TIME'] ?? time());