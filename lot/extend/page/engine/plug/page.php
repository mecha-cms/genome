<?php

Page::plug('is', function($key = null, $all = false) {
    global $config;
    if (!isset($key)) return $config->type;
    return Is::these($config->type)->has($key, $all);
});