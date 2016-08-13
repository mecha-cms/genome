<?php

Route::add(':all', function($path) {
    if ($sheet = File::exist(PAGE . DS . To::path($path) . '.log')) {
        _debug_(Genome\Sheet::open($sheet)->read());
        exit;
    }
});