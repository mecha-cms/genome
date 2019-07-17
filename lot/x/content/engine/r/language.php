<?php

Language::set([
    'skin' => ['Skin', 'Skin', 'Skins'],
    'skin-count' => function(int $i) {
        return $i . ' Skin' . ($i === 1 ? "" : 's');
    }
]);