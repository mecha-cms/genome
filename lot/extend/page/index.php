<?php


/**
 * Detect Page Type
 * ----------------
 */

$path = $url->path;
$path_array = explode('/', $path);

$config->type = '404'; // default is `404`
$config->state = 'page'; // default is `page`
if ($path === "" || $path === $config->slug) {
    $config->type = "";
}
$n = DS . Path::B($path);
$folder = PAGE . DS . $path;
if ($file = File::exist([
    $folder . '.page',
    $folder . '.archive',
    $folder . $n . '.page',
    $folder . $n . '.archive'
])) {
    $config->type = 'page';
    $config->state = Path::X($file);
    if (!File::exist($folder . $n . '.page') && Get::pages($folder, 'page')) {
        $config->type = 'pages';
    }
}


/**
 * Universal Route Definition
 * --------------------------
 */

Route::set(['%*%/%i%', '%*%', ""], function($path = "", $step = 1) use($config, $language, $url) {
    $step = $step - 1; // 0-based index ...
    $path_alt = $path === "" ? $config->slug : $path;
    $folder = rtrim(PAGE . DS . To::path($path_alt), DS);
    $folder_shield = rtrim(SHIELD . DS . To::path($config->shield), DS);
    // Change vertical elevator into horizontal elevator
    $pager = [
        'direction' => [
            '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
            '-2' => [
                2 => ['rel' => null]
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
    Seed::set('page', new Page($page));
    Config::set('page.title', new Anemon([$config->title], ' &#x2013; '));
    $name = Path::B($folder);
    if ($file = File::exist([
        $folder . '.page', // `lot\page\page-slug.page`
        $folder . '.archive', // `lot\page\page-slug.archive`
        $folder . DS . $name . '.page', // `lot\page\page-slug\page-slug.page`
        $folder . DS . $name . '.archive' // `lot\page\page-slug\page-slug.archive`
    ])) { // File does exist, then ...
        // Load user functions from the current shield folder if any
        if ($fn = File::exist([$folder_shield . DS . 'index.php', $folder_shield . DS . 'index__.php'])) {
            include $fn;
        }
        // Load user functions from the current page folder if any
        if ($fn = File::exist([$folder . DS . 'index.php', $folder . DS . 'index__.php'])) {
            include $fn;
        }
        $page = new Page($file);
        $chunk = $page->chunk($config->chunk);
        $sort = $page->sort($config->sort);
        Seed::set('page', $page);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &#x2013; '));
        if (
            !File::exist($folder . DS . $name . '.page') &&
            ($files = Get::pages($folder, 'page', $sort[0], $sort[1], 'path'))
        ) {
            foreach (Anemon::eat($files)->chunk($chunk, $step) as $file) {
                $pages[] = new Page($file);
            }
            if (empty($pages)) {
                Shield::abort(['204/' . $path_alt, '404/' . $path_alt, '204', '404']);
            }
            Seed::set([
                'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path, $pager, 'pager'),
                'pages' => $pages
            ]);
            Shield::attach('pages/' . $path_alt);
        }
        Shield::attach('page/' . $path_alt);
    }
});