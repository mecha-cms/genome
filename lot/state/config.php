<?php

$time = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

return [
    'charset' => 'utf-8',
    'direction' => 'ltr',
    'directions' => ['ltr', 'rtl'],
    'language' => 'en-us',
    'languages' => ['en-us', 'id-id'],
    'title' => 'Site Title',
    'description' => 'Site description.',
    'slug' => 'index',
    'query' => [],
    'chunk' => 3,
    'sort' => [-1, 'time'],
    'page' => [
        'path' => "",
        'time' => $time,
        'update' => $time,
        'kind' => [0],
        'slug' => '--',
        'state' => 'page',
        'title' => "",
        'description' => "",
        'type' => "",
        'author' => '#ta-tau-taufik',
        'link' => null,
        'content' => ""
    ],
    'shield' => 'document',
    'shields' => ['document', 'journal'],
    'TZ' => 'Asia/Jakarta'
];