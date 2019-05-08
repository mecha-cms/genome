<?php

Language::set([
    'plugin' => ['Plugin', 'Plugin', 'Plugins'],
    'plugin-count' => function(int $i) {
        return $i . ' Plugin' . ($i === 1 ? "" : 's');
    }
]);