<?php

foreach([
    'anemon' => "fn\\is\\anemon",
    'anemon_0' => "fn\\is\\anemon_0",
    'anemon_a' => "fn\\is\\anemon_a",
    'JSON' => "fn\\is\\json",
    'json' => "fn\\is\\json", // alias
    'serial' => "fn\\is\\serial"
] as $k => $v) {
    Is::_($k, $v);
}