<?php

Language::set([
    'site' => ['Site', 'Site', 'Sites'],
    'skin' => ['Skin', 'Skin', 'Skins'],
    'skin-count' => function(int $i) {
        return $i . ' Skin' . ($i === 1 ? "" : 's');
    }
]);