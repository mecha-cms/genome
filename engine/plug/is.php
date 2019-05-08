<?php

foreach([
    'anemon' => "_\\is\\anemon",
    'anemon_0' => "_\\is\\anemon_0",
    'anemon_a' => "_\\is\\anemon_a",
    'JSON' => "_\\is\\json",
    'json' => "_\\is\\json", // alias
    'serial' => "_\\is\\serial"
] as $k => $v) {
    Is::_($k, $v);
}