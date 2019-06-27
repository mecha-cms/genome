<?php

Language::set([
    'shield' => ['Shield', 'Shield', 'Shields'],
    'shield-count' => function(int $i) {
        return $i . ' Shield' . ($i === 1 ? "" : 's');
    }
]);