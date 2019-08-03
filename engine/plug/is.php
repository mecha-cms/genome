<?php

foreach([
    'JSON' => "_\\json",
    'json' => "_\\json", // Alias
    'serial' => "_\\serial"
] as $k => $v) {
    Is::_($k, $v);
}