<?php

$time = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();

return [
    'zone' => 'Asia/Jakarta',
    'direction' => 'ltr',
    'language' => 'en-us',
    'charset' => 'utf-8',
    'title' => 'Site Title',
    'description' => 'Site description.',
    'type' => "", // default page type is ``
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
        'author' => "",
        'type' => 'HTML',
        'link' => null,
        'content' => ""
    ],
    'shield' => 'document'
];