<?php

Language::set([
    'asset' => ['Asset', 'Asset', 'Assets'],
    'asset-count' => function(int $i) {
        return $i . ' Asset' . ($i === 1 ? "" : 's');
    }
]);