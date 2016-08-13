<?php

Config::plug('url', function($key = 'url', $fail = false) {
    return Config::get($key !== true ? 'url.' . $key : 'url', $fail);
});