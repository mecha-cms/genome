<?php

foreach([
    'anemon' => '__is_anemon__',
    'anemon_0' => '__is_anemon_0__',
    'anemon_a' => '__is_anemon_a__',
    'json' => '__is_json__',
    'JSON' => '__is_json__' , // alias
    'serial' => '__is_serial__'
] as $k => $v) {
    Is::_($k, $v);
}