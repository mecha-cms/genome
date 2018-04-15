<?php

Hook::set([
    '*.time',
    '*.update'
], function($v) {
    return new Date($v);
}, 0);