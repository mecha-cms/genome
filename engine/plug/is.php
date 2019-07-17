<?php

foreach([
    'anemon' => "_\\anemon",
    'anemon_0' => "_\\anemon_0",
    'anemon_a' => "_\\anemon_a",
    'JSON' => "_\\json",
    'json' => "_\\json", // alias
    'serial' => "_\\serial"
] as $k => $v) {
    Is::_($k, $v);
}