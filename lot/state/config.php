<?php

$time = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

return [
    'zone' => 'Asia/Jakarta',
    'direction' => 'ltr',
    'language' => 'en-us',
    'charset' => 'utf-8',
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
        'author' => '@ta-tau-taufik',
        'link' => null,
        'content' => ""
    ],
    'shield' => 'document'
];