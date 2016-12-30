<?php

Route::set(['%%/%i%', '%%', ""], function($path = "", $step = 1) use($config, $language, $url) {
    $step = $step - 1; // 0-based index ...
    $path_alt = $path === "" ? 'index' : $path;
    $folder = rtrim(PAGE . DS . To::path($path_alt), DS);
    // Change vertical elevator into horizontal elevator
    $elevator = [
        'direction' => [
            '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
            '-2' => [
                2 => [
                    'class' => 'a',
                    'rel' => null
                ]
            ],
            '-1' => [
                1 => '&#x25C0;',
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => '&#x25B6;',
                2 => ['rel' => 'next']
            ]
        ]
    ];
    $pages = $page = [];
    Config::set('page.title', new Anemon([$config->title], ' &#x2013; '));
    if ($file = File::exist([$folder . '.page', $folder . '.archive'])) { // File does exist
        $page = new Page($file);
        Seed::set('page', $page);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &#x2013; '));
        if ($files = Get::pages($folder, [], 1, 'time', 'path')) {
            foreach (Anemon::eat($files)->chunk($config->chunk, $step) as $file) {
                $pages[] = new Page($file);
            }
            if (empty($pages)) {
                Shield::abort(['204/' . $path_alt, '404/' . $path_alt, '204', '404']);
            }
            Seed::set([
                'pager' => new Elevator($files, $config->chunk, $step, $url . '/' . $path, $elevator, 'pager'),
                'pages' => $pages
            ]);
            Shield::attach('pages/' . $path_alt);
        }
        Shield::attach('page/' . $path_alt);
    }
});