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
    $step = $step - 1; // 0-based index …
    $path_alt = $path === "" ? $config->slug : $path;
    $folder = rtrim(PAGE . DS . To::path($path_alt), DS);
    $folder_shield = rtrim(SHIELD . DS . To::path($config->shield), DS);
    $name = Path::B($folder);
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
    Lot::set([
        'pager' => new Elevator([]),
        'page' => new Page([])
    ]);
    Config::set('page.title', new Anemon([$config->title], ' &#x00B7; '));
    if ($file = File::exist([
        $folder . '.page', // `lot\page\page-slug.page`
        $folder . '.archive', // `lot\page\page-slug.archive`
        $folder . DS . $name . '.page', // `lot\page\page-slug\page-slug.page`
        $folder . DS . $name . '.archive' // `lot\page\page-slug\page-slug.archive`
    ])) { // File does exist, then …
        // Load user function(s) from the current shield folder if any
        if ($fn = File::exist([$folder_shield . DS . 'index.php', $folder_shield . DS . 'index__.php'])) {
            include $fn;
        }
        // Load user function(s) from the current page folder if any, stacked from the parent page(s)
        $s = PAGE;
        foreach (explode('/', '/' . $path) as $ss) {
            $s .= $ss ? DS . $ss : "";
            if ($fn = File::exist([$s . DS . 'index.php', $s . DS . 'index__.php'])) {
                include $fn;
            }
        }
        $page = new Page($file);
        $sort = $page->sort($config->sort);
        $chunk = $page->chunk($config->chunk);
        // Create elevator for single page mode
        $folder_parent = Path::D($folder);
        $path_parent = Path::D($path);
        $name_parent = Path::B($folder_parent);
        if ($file_parent = File::exist([
            $folder_parent . '.page',
            $folder_parent . '.archive',
            $folder_parent . DS . $name_parent . '.page',
            $folder_parent . DS . $name_parent . '.archive'
        ])) {
            $sort_parent = Page::open($file_parent)->get('sort', $config->sort);
            $files_parent = Get::pages($folder_parent, 'page', $sort_parent[0], $sort_parent[1], 'slug');
        } else {
            $files_parent = [];
        }
        Lot::set([
            'pager' => new Elevator($files_parent, null, $page->slug, $url . '/' . $path_parent, $pager, 'pager'),
            'page' => $page
        ]);
        Config::set('page.title', new Anemon([$page->title, $config->title], ' &#x00B7; '));
        if (!File::exist($folder . DS . $name . '.' . $page->state)) {
            if ($files = Get::pages($folder, 'page', $sort[0], $sort[1], 'path')) {
                foreach (Anemon::eat($files)->chunk($chunk, $step) as $file) {
                    $pages[] = new Page($file);
                }
                if (empty($pages)) {
                    Shield::abort(['204/' . $path_alt, '404/' . $path_alt, '204', '404']);
                }
                Lot::set([
                    'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path, $pager, 'pager'),
                    'pages' => $pages
                ]);
                Shield::attach('pages/' . $path_alt);
            } else if ($name === $name_parent && File::exist($folder . '.' . $page->state)) {
                Guardian::kick($path_parent);  // Redirect to parent page if user tries to access the placeholder page …
            }
        }
        Shield::attach('page/' . $path_alt);
    }
}, 20);